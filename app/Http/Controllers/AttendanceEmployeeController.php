<?php

namespace App\Http\Controllers;

use App\Models\AttendanceEmployee;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AttendanceEmployeeController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = Auth::user()->school_id;
        $mode = $request->input('mode', 'harian');
        $date = $request->input('date', now()->toDateString());
        $month = $request->input('month', now()->format('Y-m'));

        $employees = Employee::with('position')
            ->where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->orderBy('full_name')
            ->get();

        $myEmployee = Auth::user()->employee;
        $myTodayAttendance = null;
        if ($myEmployee) {
            $myTodayAttendance = AttendanceEmployee::where('employee_id', $myEmployee->id)
                ->whereDate('att_date', now()->toDateString())
                ->first();
        }

        $existing = collect();
        $rekapMatrix = [];
        $rekapSummary = [];
        $daysInMonth = 0;

        if ($mode === 'rekap') {
            $carbonMonth = Carbon::parse($month . '-01');
            $daysInMonth = $carbonMonth->daysInMonth;
            $startDate = $carbonMonth->copy()->startOfMonth()->toDateString();
            $endDate = $carbonMonth->copy()->endOfMonth()->toDateString();

            $attendances = AttendanceEmployee::whereBetween('att_date', [$startDate, $endDate])
                ->whereIn('employee_id', $employees->pluck('id'))
                ->get();

            foreach ($employees as $emp) {
                $empAtts = $attendances->where('employee_id', $emp->id);
                $matrix = [];
                $counts = ['hadir' => 0, 'telat' => 0, 'izin' => 0, 'alpa' => 0];

                foreach ($empAtts as $att) {
                    $dayNum = (int) Carbon::parse($att->att_date)->format('j');
                    $matrix[$dayNum] = $att->status;
                    if (isset($counts[$att->status])) {
                        $counts[$att->status]++;
                    }
                }

                $totalRecorded = array_sum($counts);
                $presenceRate = $totalRecorded > 0 ? round((($counts['hadir'] + $counts['telat']) / $totalRecorded) * 100, 1) : 0;

                $rekapMatrix[$emp->id] = $matrix;
                $rekapSummary[$emp->id] = $counts + [
                    'total' => $totalRecorded,
                    'percentage' => $presenceRate,
                ];
            }
        } else {
            $existing = AttendanceEmployee::whereDate('att_date', $date)
                ->whereIn('employee_id', $employees->pluck('id'))
                ->get()->keyBy('employee_id');
        }

        return view('attendance-employee.index', compact(
            'mode', 'date', 'month', 'employees', 'existing', 'myEmployee', 'myTodayAttendance',
            'daysInMonth', 'rekapMatrix', 'rekapSummary'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'att_date' => ['required', 'date'],
            'statuses' => ['required', 'array'],
            'statuses.*' => ['in:hadir,telat,izin,alpa'],
        ]);

        $date = $data['att_date'];

        DB::transaction(function () use ($data, $date) {
            foreach ($data['statuses'] as $empId => $status) {
                AttendanceEmployee::updateOrCreate(
                    ['employee_id' => $empId, 'att_date' => $date],
                    ['status' => $status]
                );
            }
        });

        return back()->with('toast', 'Presensi pegawai tanggal ' . $date . ' berhasil disimpan.');
    }

    public function selfCheckIn(Request $request)
    {
        $employee = Auth::user()->employee;
        if (! $employee) {
            return back()->with('toast', 'Akun Anda tidak terhubung ke data pegawai.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:hadir,telat,izin'],
        ]);

        $today = now()->toDateString();

        AttendanceEmployee::updateOrCreate(
            ['employee_id' => $employee->id, 'att_date' => $today],
            ['status' => $data['status']]
        );

        return back()->with('toast', 'Check-in presensi hari ini berhasil dicatat (' . ucfirst($data['status']) . ').');
    }
}
