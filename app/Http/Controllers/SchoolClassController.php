<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Employee;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $query = SchoolClass::with(['academicYear', 'major', 'homeroomTeacher', 'students'])
            ->where('school_id', $schoolId)
            ->orderBy('name');

        if ($request->filled('major_id')) {
            $query->where('major_id', $request->integer('major_id'));
        }
        if ($request->filled('academic_year_id')) {
            $query->where('academic_year_id', $request->integer('academic_year_id'));
        }

        $classes = $query->get();
        $academicYears = AcademicYear::where('school_id', $schoolId)->orderByDesc('year_label')->get();
        $majors = Major::where('school_id', $schoolId)->orderBy('code')->get();
        $teachers = Employee::where('school_id', $schoolId)->orderBy('full_name')->get();

        return view('classes.index', compact('classes', 'academicYears', 'majors', 'teachers'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'name' => ['required', 'string', 'max:30'],
            'homeroom_teacher_id' => ['nullable', 'exists:employees,id'],
        ]);

        $data['school_id'] = $schoolId;
        $data['major_id'] = $data['major_id'] ?: null;
        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;

        SchoolClass::create($data);

        return back()->with('toast', 'Kelas "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'name' => ['required', 'string', 'max:30'],
            'homeroom_teacher_id' => ['nullable', 'exists:employees,id'],
        ]);

        $data['major_id'] = $data['major_id'] ?: null;
        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;

        $class->update($data);

        return back()->with('toast', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return back()->with('toast', 'Kelas berhasil dihapus.');
    }

    public function storeYear(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'year_label' => ['required', 'string', 'max:20'],
        ]);

        $exists = AcademicYear::where('school_id', $schoolId)->where('year_label', $data['year_label'])->exists();
        if ($exists) {
            return back()->with('toast', 'Tahun ajaran tersebut sudah ada.');
        }

        AcademicYear::create([
            'school_id' => $schoolId,
            'year_label' => $data['year_label'],
            'is_active' => false,
        ]);

        return back()->with('toast', 'Tahun ajaran ' . $data['year_label'] . ' berhasil ditambahkan.');
    }

    public function activateYear(AcademicYear $year)
    {
        $schoolId = auth()->user()->school_id;

        AcademicYear::where('school_id', $schoolId)->where('is_active', true)->update(['is_active' => false]);
        $year->update(['is_active' => true]);

        return back()->with('toast', 'Tahun ajaran aktif diubah ke ' . $year->year_label . '.');
    }

    public function template(SpreadsheetService $service)
    {
        $headers = [
            'NAMA_KELAS',
            'KODE_JURUSAN',
            'TAHUN_AJARAN',
            'NIP_WALI_KELAS',
        ];

        $sampleRows = [
            ['X RPL 1', 'RPL', '2026/2027', '198505122010011003'],
            ['X RPL 2', 'RPL', '2026/2027', ''],
            ['X TKJ 1', 'TKJ', '2026/2027', '197506102005011002'],
            ['XI AKL 1', 'AKL', '2026/2027', ''],
        ];

        return $service->downloadTemplate('template_kelas.csv', $headers, $sampleRows);
    }

    public function import(Request $request, SpreadsheetService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $rows = $service->parseFile($request->file('file'));
        if (count($rows) < 2) {
            return back()->with('toast', 'File spreadsheet kosong atau tidak memiliki baris data.');
        }

        $schoolId = auth()->user()->school_id;
        $defaultYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first()
            ?? AcademicYear::firstOrCreate(['school_id' => $schoolId, 'year_label' => '2026/2027'], ['is_active' => true]);

        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            $className = trim($row[0] ?? '');
            $majorCode = strtoupper(trim($row[1] ?? ''));
            $yearLabel = trim($row[2] ?? '');
            $nipWali = trim($row[3] ?? '');

            if (empty($className)) {
                continue;
            }

            // 1. Tahun Ajaran
            $yearId = $defaultYear->id;
            if (!empty($yearLabel)) {
                $ay = AcademicYear::firstOrCreate(
                    ['school_id' => $schoolId, 'year_label' => $yearLabel],
                    ['is_active' => false]
                );
                $yearId = $ay->id;
            }

            // 2. Jurusan
            $majorId = null;
            if (!empty($majorCode)) {
                $maj = Major::firstOrCreate(
                    ['school_id' => $schoolId, 'code' => $majorCode],
                    ['name' => 'Program Keahlian ' . $majorCode]
                );
                $majorId = $maj->id;
            }

            // 3. Wali Kelas
            $teacherId = null;
            if (!empty($nipWali)) {
                $teacher = Employee::where('school_id', $schoolId)
                    ->where(fn ($q) => $q->where('nip', $nipWali)->orWhere('full_name', 'like', "%{$nipWali}%"))
                    ->first();
                if ($teacher) {
                    $teacherId = $teacher->id;
                }
            }

            // 4. Update or Create Class
            SchoolClass::updateOrCreate(
                ['school_id' => $schoolId, 'name' => $className],
                [
                    'academic_year_id' => $yearId,
                    'major_id' => $majorId,
                    'homeroom_teacher_id' => $teacherId,
                ]
            );

            $imported++;
        }

        return back()->with('toast', "Berhasil mengimpor {$imported} rombel kelas!");
    }
}
