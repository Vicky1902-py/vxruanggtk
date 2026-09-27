<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\Bill;
use App\Models\Employee;
use App\Models\SchoolClass;
use App\Models\Student;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $school = $user->school;

        $stats = [
            'students' => Student::count(),
            'classes' => SchoolClass::count(),
            'employees' => Employee::count(),
            'announcements' => Announcement::latest('published_at')->take(5)->get(),
            'unpaid_bills' => Bill::where('status', '!=', 'lunas')->count(),
            'unpaid_amount' => (float) Bill::where('status', '!=', 'lunas')->sum('amount'),
        ];

        return view('dashboard', compact('user', 'school', 'stats'));
    }
}
