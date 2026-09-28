<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bill;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\SchoolClass;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $school = $user->school;
        $userRole = $user->role?->name;

        $stats = [
            'students' => Student::count(),
            'classes' => SchoolClass::count(),
            'employees' => Employee::count(),
            'announcements' => Announcement::latest('published_at')->take(5)->get(),
            'unpaid_bills' => Bill::where('status', '!=', 'lunas')->count(),
            'unpaid_amount' => (float) Bill::where('status', '!=', 'lunas')->sum('amount'),
            'paid_amount' => (float) Payment::whereHas('bill', fn ($q) => $q->where('school_id', $school->id))->sum('amount_paid'),
        ];

        // Konteks spesifik Wali Murid
        $myChildren = collect();
        if ($userRole === 'wali' && $user->guardian) {
            $myChildren = $user->guardian->students()
                ->with([
                    'schoolClass',
                    'attendances' => fn ($q) => $q->latest('att_date')->take(7),
                    'bills' => fn ($q) => $q->with('paymentType')->latest(),
                ])
                ->get();
        }

        // Konteks spesifik Guru Wali Kelas
        $myHomeroomClass = null;
        if ($userRole === 'guru' && $user->employee) {
            $myHomeroomClass = SchoolClass::where('homeroom_teacher_id', $user->employee->id)
                ->withCount('students')
                ->first();
        }

        return view('dashboard', compact('user', 'school', 'userRole', 'stats', 'myChildren', 'myHomeroomClass'));
    }
}
