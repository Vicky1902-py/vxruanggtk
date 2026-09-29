<?php

namespace App\Http\Controllers;

use App\Models\Counseling;
use App\Models\Permit;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Violation;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;

class StudentWelfareController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $tab = $request->input('tab', 'pelanggaran'); // pelanggaran | izin | konseling

        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();
        $students = Student::where('school_id', $schoolId)->where('status', 'aktif')->orderBy('full_name')->get();

        // 1. Data Pelanggaran
        $violationsQuery = Violation::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->with(['student.schoolClass'])
            ->latest('incident_date');

        if ($request->filled('class_id')) {
            $violationsQuery->whereHas('student', fn ($s) => $s->where('class_id', $request->integer('class_id')));
        }
        if ($request->filled('q')) {
            $q = $request->string('q');
            $violationsQuery->where(function ($w) use ($q) {
                $w->where('category', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%")
                    ->orWhereHas('student', fn ($s) => $s->where('full_name', 'like', "%{$q}%")->orWhere('nis', 'like', "%{$q}%"));
            });
        }
        $violations = $violationsQuery->paginate(20, ['*'], 'violations_page')->withQueryString();

        // 2. Data Izin
        $permitsQuery = Permit::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->with(['student.schoolClass', 'approver'])
            ->latest('start_time');
        $permits = $permitsQuery->paginate(20, ['*'], 'permits_page')->withQueryString();

        // 3. Data Konseling & Prestasi
        $counselingsQuery = Counseling::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->with(['student.schoolClass'])
            ->latest('session_date');
        $counselings = $counselingsQuery->paginate(20, ['*'], 'counselings_page')->withQueryString();

        // Top Poin Pelanggaran Siswa
        $topViolations = Student::where('school_id', $schoolId)
            ->whereHas('violations')
            ->withSum('violations', 'points')
            ->with('schoolClass')
            ->orderByDesc('violations_sum_points')
            ->take(5)
            ->get();

        return view('welfare.index', compact(
            'tab', 'classes', 'students', 'violations', 'permits', 'counselings', 'topViolations'
        ));
    }

    public function storeViolation(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'category' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:500'],
            'incident_date' => ['required', 'date'],
            'points' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        Student::where('school_id', $schoolId)->findOrFail($validated['student_id']);

        Violation::create($validated);

        return back()->with('toast', 'Pelanggaran kedisiplinan berhasil dicatat (+ ' . $validated['points'] . ' poin).');
    }

    public function destroyViolation(Violation $violation)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($violation->student?->school_id === $schoolId, 403);

        $violation->delete();

        return back()->with('toast', 'Catatan pelanggaran berhasil dihapus.');
    }

    public function storePermit(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'in:izin,keluar,pulang_awal'],
            'start_time' => ['required', 'date'],
            'end_time' => ['nullable', 'date', 'after_or_equal:start_time'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        Student::where('school_id', $schoolId)->findOrFail($validated['student_id']);

        $employeeId = auth()->user()->employee?->id;
        if ($validated['status'] === 'approved') {
            $validated['approved_by'] = $employeeId;
        }

        Permit::create($validated);

        return back()->with('toast', 'Surat izin siswa berhasil dicatat.');
    }

    public function updatePermitStatus(Request $request, Permit $permit)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($permit->student?->school_id === $schoolId, 403);

        $validated = $request->validate([
            'status' => ['required', 'in:pending,approved,rejected'],
        ]);

        $employeeId = auth()->user()->employee?->id;
        $permit->update([
            'status' => $validated['status'],
            'approved_by' => $validated['status'] === 'approved' ? $employeeId : null,
        ]);

        return back()->with('toast', 'Status izin diperbarui menjadi ' . ucfirst($validated['status']) . '.');
    }

    public function storeCounseling(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'type' => ['required', 'in:konseling,prestasi'],
            'session_date' => ['required', 'date'],
            'notes' => ['required', 'string'],
        ]);

        Student::where('school_id', $schoolId)->findOrFail($validated['student_id']);

        Counseling::create($validated);

        $label = $validated['type'] === 'prestasi' ? 'Catatan Prestasi' : 'Catatan Konseling BK';
        return back()->with('toast', "{$label} berhasil disimpan.");
    }

    public function exportViolations(Request $request, SpreadsheetService $service)
    {
        $schoolId = auth()->user()->school_id;

        $violations = Violation::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->with(['student.schoolClass'])
            ->latest('incident_date')
            ->get();

        $headers = [
            'NO',
            'NIS',
            'NAMA_SISWA',
            'KELAS',
            'TANGGAL_KEJADIAN',
            'KATEGORI_PELANGGARAN',
            'POIN_PELANGGARAN',
            'DESKRIPSI_KETERANGAN',
        ];

        $rows = [];
        $no = 1;
        foreach ($violations as $v) {
            $rows[] = [
                $no++,
                $v->student?->nis ?? '-',
                $v->student?->full_name ?? 'Siswa',
                $v->student?->schoolClass ? 'Kelas ' . $v->student->schoolClass->name : '-',
                $v->incident_date ? $v->incident_date->format('d/m/Y') : '-',
                $v->category,
                $v->points,
                $v->description ?? '-',
            ];
        }

        $filename = 'rekap_pelanggaran_siswa_' . date('Ymd_His') . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Pelanggaran Kedisiplinan');
    }
}
