<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use App\Models\AcademicYear;
use App\Models\Employee;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher'])
            ->orderBy('name')->get();
        $academicYears = AcademicYear::orderByDesc('year_label')->get();
        $teachers = Employee::orderBy('full_name')->get();

        return view('classes.index', compact('classes', 'academicYears', 'teachers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:30'],
            'homeroom_teacher_id' => ['nullable', 'exists:employees,id'],
        ]);

        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;

        SchoolClass::create($data);

        return back()->with('toast', 'Kelas "' . $data['name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, SchoolClass $class)
    {
        $data = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:30'],
            'homeroom_teacher_id' => ['nullable', 'exists:employees,id'],
        ]);

        $data['homeroom_teacher_id'] = $data['homeroom_teacher_id'] ?: null;

        $class->update($data);

        return back()->with('toast', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();

        return back()->with('toast', 'Kelas berhasil dihapus.');
    }
}
