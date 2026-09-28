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
        $classes = SchoolClass::with(['academicYear', 'homeroomTeacher', 'students'])
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

    public function storeYear(Request $request)
    {
        $data = $request->validate([
            'year_label' => ['required', 'string', 'max:20'],
        ]);

        $exists = AcademicYear::where('year_label', $data['year_label'])->exists();
        if ($exists) {
            return back()->with('toast', 'Tahun ajaran tersebut sudah ada.');
        }

        AcademicYear::create([
            'school_id' => auth()->user()->school_id,
            'year_label' => $data['year_label'],
            'is_active' => false,
        ]);

        return back()->with('toast', 'Tahun ajaran ' . $data['year_label'] . ' berhasil ditambahkan.');
    }

    public function activateYear(AcademicYear $year)
    {
        AcademicYear::where('is_active', true)->update(['is_active' => false]);
        $year->update(['is_active' => true]);

        return back()->with('toast', 'Tahun ajaran aktif diubah ke ' . $year->year_label . '.');
    }
}
