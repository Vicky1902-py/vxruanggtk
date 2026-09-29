<?php

namespace App\Http\Controllers;

use App\Models\AttendanceStudent;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceStudentController extends Controller
{
    public function index(Request $request)
    {
        $classes = SchoolClass::orderBy('name')->get();
        $classId = $request->input('class_id', $classes->first()?->id);
        $mode = $request->input('mode', 'harian');
        $date = $request->input('date', now()->toDateString());
        $month = $request->input('month', now()->format('Y-m'));

        $students = collect();
        $existing = collect();
        $rekapMatrix = [];
        $rekapSummary = [];
        $daysInMonth = 0;

        if ($classId) {
            $students = Student::where('class_id', $classId)->orderBy('full_name')->get();

            if ($mode === 'rekap') {
                $carbonMonth = \Illuminate\Support\Carbon::parse($month . '-01');
                $daysInMonth = $carbonMonth->daysInMonth;
                $startDate = $carbonMonth->copy()->startOfMonth()->toDateString();
                $endDate = $carbonMonth->copy()->endOfMonth()->toDateString();

                $attendances = AttendanceStudent::whereBetween('att_date', [$startDate, $endDate])
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get();

                foreach ($students as $student) {
                    $stAtts = $attendances->where('student_id', $student->id);
                    $matrix = [];
                    $counts = ['hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpa' => 0];

                    foreach ($stAtts as $att) {
                        $dayNum = (int) \Illuminate\Support\Carbon::parse($att->att_date)->format('j');
                        $matrix[$dayNum] = $att->status;
                        if (isset($counts[$att->status])) {
                            $counts[$att->status]++;
                        }
                    }

                    $totalRecorded = array_sum($counts);
                    $percentage = $totalRecorded > 0 ? round(($counts['hadir'] / $totalRecorded) * 100, 1) : 0;

                    $rekapMatrix[$student->id] = $matrix;
                    $rekapSummary[$student->id] = $counts + [
                        'total' => $totalRecorded,
                        'percentage' => $percentage,
                    ];
                }
            } else {
                $existing = AttendanceStudent::whereDate('att_date', $date)
                    ->whereIn('student_id', $students->pluck('id'))
                    ->get()->keyBy('student_id');
            }
        }

        return view('attendance.index', compact(
            'classes', 'classId', 'date', 'month', 'mode', 'students', 'existing',
            'daysInMonth', 'rekapMatrix', 'rekapSummary'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'att_date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['in:hadir,izin,sakit,alpa'],
        ]);

        $employeeId = Auth::user()->employee?->id;
        $date = $data['att_date'];

        DB::transaction(function () use ($data, $employeeId, $date) {
            foreach ($data['statuses'] as $studentId => $status) {
                AttendanceStudent::updateOrCreate(
                    ['student_id' => $studentId, 'att_date' => $date],
                    ['status' => $status, 'recorded_by' => $employeeId]
                );
            }
        });

        return redirect()->route('attendance.index', [
            'class_id' => $data['class_id'],
            'date' => $date,
        ])->with('toast', 'Presensi tanggal ' . $date . ' berhasil disimpan.');
    }

    public function export(Request $request, \App\Services\SpreadsheetService $service)
    {
        $classId = $request->input('class_id');
        $month = $request->input('month', date('Y-m'));

        $selectedClass = $classId ? SchoolClass::find($classId) : SchoolClass::first();
        if (!$selectedClass) {
            return back()->with('toast', 'Pilih kelas terlebih dahulu.');
        }

        $students = Student::where('class_id', $selectedClass->id)->orderBy('full_name')->get();

        $carbonMonth = \Illuminate\Support\Carbon::parse($month . '-01');
        $startDate = $carbonMonth->copy()->startOfMonth()->toDateString();
        $endDate = $carbonMonth->copy()->endOfMonth()->toDateString();

        $attendances = AttendanceStudent::whereBetween('att_date', [$startDate, $endDate])
            ->whereIn('student_id', $students->pluck('id'))
            ->get();

        $headers = [
            'NO',
            'NIS',
            'NAMA_SISWA',
            'KELAS',
            'BULAN',
            'HADIR (H)',
            'IZIN (I)',
            'SAKIT (S)',
            'ALPA (A)',
            'TOTAL_PERTEMUAN',
            'PERSENTASE_KEHADIRAN',
        ];

        $rows = [];
        $no = 1;
        foreach ($students as $student) {
            $stAtts = $attendances->where('student_id', $student->id);
            $h = $stAtts->where('status', 'hadir')->count();
            $i = $stAtts->where('status', 'izin')->count();
            $s = $stAtts->where('status', 'sakit')->count();
            $a = $stAtts->where('status', 'alpa')->count();
            $total = $h + $i + $s + $a;
            $pct = $total > 0 ? round(($h / $total) * 100, 1) . '%' : '0%';

            $rows[] = [
                $no++,
                $student->nis ?? '-',
                $student->full_name,
                'Kelas ' . $selectedClass->name,
                $carbonMonth->translatedFormat('F Y'),
                $h,
                $i,
                $s,
                $a,
                $total,
                $pct,
            ];
        }

        $filename = 'rekap_presensi_' . str_replace(' ', '_', $selectedClass->name) . '_' . $month . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Rekap Presensi Siswa');
    }
}
