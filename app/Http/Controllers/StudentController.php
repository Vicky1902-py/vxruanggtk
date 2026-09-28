<?php

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\Guardian;
use App\Models\Major;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['schoolClass', 'major', 'guardian'])->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }
        if ($request->filled('major_id')) {
            $query->where('major_id', $request->integer('major_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(fn ($w) => $w
                ->where('full_name', 'like', "%{$q}%")
                ->orWhere('nis', 'like', "%{$q}%")
                ->orWhere('nisn', 'like', "%{$q}%"));
        }

        $students = $query->get();
        $classes = SchoolClass::orderBy('name')->get();
        $majors = Major::orderBy('code')->get();
        $guardians = Guardian::orderBy('full_name')->get();

        return view('students.index', compact('students', 'classes', 'majors', 'guardians'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'class_id' => ['nullable', 'exists:classes,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'nis' => ['nullable', 'string', 'max:30'],
            'nisn' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,lulus,pindah,keluar'],
        ]);

        $data['school_id'] = $schoolId;
        $data['class_id'] = $data['class_id'] ?: null;
        $data['major_id'] = $data['major_id'] ?: null;
        $data['guardian_id'] = $data['guardian_id'] ?: null;
        $data['birth_date'] = $data['birth_date'] ?: null;
        $data['nis'] = $data['nis'] ?: null;
        $data['nisn'] = $data['nisn'] ?: null;

        // Auto-assign major from class if class has major
        if (!empty($data['class_id']) && empty($data['major_id'])) {
            $selectedClass = SchoolClass::find($data['class_id']);
            if ($selectedClass && $selectedClass->major_id) {
                $data['major_id'] = $selectedClass->major_id;
            }
        }

        Student::create($data);

        return back()->with('toast', 'Siswa "' . $data['full_name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'class_id' => ['nullable', 'exists:classes,id'],
            'major_id' => ['nullable', 'exists:majors,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'nis' => ['nullable', 'string', 'max:30'],
            'nisn' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,lulus,pindah,keluar'],
        ]);

        $data['class_id'] = $data['class_id'] ?: null;
        $data['major_id'] = $data['major_id'] ?: null;
        $data['guardian_id'] = $data['guardian_id'] ?: null;
        $data['birth_date'] = $data['birth_date'] ?: null;
        $data['nis'] = $data['nis'] ?: null;
        $data['nisn'] = $data['nisn'] ?: null;

        if (!empty($data['class_id']) && empty($data['major_id'])) {
            $selectedClass = SchoolClass::find($data['class_id']);
            if ($selectedClass && $selectedClass->major_id) {
                $data['major_id'] = $selectedClass->major_id;
            }
        }

        $student->update($data);

        return back()->with('toast', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return back()->with('toast', 'Siswa berhasil dihapus.');
    }

    public function template(SpreadsheetService $service)
    {
        $headers = [
            'NIS',
            'NISN',
            'NAMA_LENGKAP',
            'JENIS_KELAMIN',
            'KELAS',
            'JURUSAN',
            'TANGGAL_LAHIR',
            'STATUS',
            'NAMA_WALI',
            'NO_HP_WALI',
            'HUBUNGAN_WALI',
        ];

        $sampleRows = [
            ['2601', '0091234567', 'Aisyah Putri Pratama', 'P', 'X RPL 1', 'RPL', '2010-04-12', 'aktif', 'Ahmad Fauzi', '081234567890', 'Ayah'],
            ['2602', '0091234568', 'Bagas Aditya Prakoso', 'L', 'X RPL 1', 'RPL', '2010-08-03', 'aktif', 'Sutrisno', '081298765432', 'Ayah'],
            ['2603', '0091234569', 'Citra Dewi Lestari', 'P', 'X TKJ 1', 'TKJ', '2010-01-25', 'aktif', 'Dewi Sartika', '085712345678', 'Ibu'],
            ['2604', '0091234570', 'Dimas Arya Anggara', 'L', 'X TKJ 1', 'TKJ', '2010-11-30', 'aktif', 'Hendra Setiawan', '082133445566', 'Wali'],
        ];

        return $service->downloadTemplate('template_siswa.csv', $headers, $sampleRows);
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
        $activeYear = AcademicYear::where('school_id', $schoolId)->where('is_active', true)->first()
            ?? AcademicYear::firstOrCreate(['school_id' => $schoolId, 'year_label' => '2026/2027'], ['is_active' => true]);

        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            $nis = trim($row[0] ?? '');
            $nisn = trim($row[1] ?? '');
            $fullName = trim($row[2] ?? '');
            $gender = strtoupper(trim($row[3] ?? 'L'));
            $className = trim($row[4] ?? '');
            $majorCode = strtoupper(trim($row[5] ?? ''));
            $birthDate = trim($row[6] ?? '');
            $status = strtolower(trim($row[7] ?? 'aktif'));
            $guardianName = trim($row[8] ?? '');
            $guardianPhone = trim($row[9] ?? '');
            $guardianRelation = trim($row[10] ?? 'Wali');

            if (empty($fullName)) {
                continue;
            }

            if (!in_array($gender, ['L', 'P'])) {
                $gender = 'L';
            }
            if (!in_array($status, ['aktif', 'lulus', 'pindah', 'keluar'])) {
                $status = 'aktif';
            }

            // Validasi tanggal lahir
            $parsedDate = null;
            if (!empty($birthDate)) {
                $ts = strtotime($birthDate);
                if ($ts !== false) {
                    $parsedDate = date('Y-m-d', $ts);
                }
            }

            // 1. Hubungkan / Buat Jurusan jika dicantumkan
            $majorId = null;
            if (!empty($majorCode)) {
                $major = Major::firstOrCreate(
                    ['school_id' => $schoolId, 'code' => $majorCode],
                    ['name' => 'Program Keahlian ' . $majorCode]
                );
                $majorId = $major->id;
            }

            // 2. Hubungkan / Buat Kelas jika dicantumkan
            $classId = null;
            if (!empty($className)) {
                $schoolClass = SchoolClass::firstOrCreate(
                    ['school_id' => $schoolId, 'name' => $className],
                    [
                        'academic_year_id' => $activeYear->id,
                        'major_id' => $majorId,
                    ]
                );
                $classId = $schoolClass->id;
                if (!$majorId && $schoolClass->major_id) {
                    $majorId = $schoolClass->major_id;
                }
            }

            // 3. Hubungkan / Buat Wali Murid jika dicantumkan
            $guardianId = null;
            if (!empty($guardianName)) {
                $guardian = Guardian::firstOrCreate(
                    ['full_name' => $guardianName, 'relation_type' => $guardianRelation ?: 'Wali'],
                    ['phone_whatsapp' => $guardianPhone ?: null]
                );
                $guardianId = $guardian->id;
            }

            // 4. Update or Create Siswa
            $criteria = ['school_id' => $schoolId];
            if (!empty($nis)) {
                $criteria['nis'] = $nis;
            } else {
                $criteria['full_name'] = $fullName;
            }

            Student::updateOrCreate($criteria, [
                'nis' => $nis ?: null,
                'nisn' => $nisn ?: null,
                'full_name' => $fullName,
                'gender' => $gender,
                'class_id' => $classId,
                'major_id' => $majorId,
                'guardian_id' => $guardianId,
                'birth_date' => $parsedDate,
                'status' => $status,
            ]);

            $imported++;
        }

        return back()->with('toast', "Berhasil mengimpor {$imported} data peserta didik dengan sukses!");
    }
}
