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
        $date = $request->input('date', now()->toDateString());

        $students = collect();
        $existing = collect();

        if ($classId) {
            $students = Student::where('class_id', $classId)->orderBy('full_name')->get();
            $existing = AttendanceStudent::whereDate('att_date', $date)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()->keyBy('student_id');
        }

        return view('attendance.index', compact('classes', 'classId', 'date', 'students', 'existing'));
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

        return back()->with('toast', 'Presensi tanggal ' . $date . ' berhasil disimpan.');
    }
}
