<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\Guardian;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['schoolClass', 'guardian'])->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
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
        $guardians = Guardian::orderBy('full_name')->get();

        return view('students.index', compact('students', 'classes', 'guardians'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'class_id' => ['nullable', 'exists:classes,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'nis' => ['nullable', 'string', 'max:30'],
            'nisn' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,lulus,pindah,keluar'],
        ]);

        Student::create($data);

        return back()->with('toast', 'Siswa "' . $data['full_name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Student $student)
    {
        $data = $request->validate([
            'class_id' => ['nullable', 'exists:classes,id'],
            'guardian_id' => ['nullable', 'exists:guardians,id'],
            'nis' => ['nullable', 'string', 'max:30'],
            'nisn' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:L,P'],
            'birth_date' => ['nullable', 'date'],
            'status' => ['required', 'in:aktif,lulus,pindah,keluar'],
        ]);

        $student->update($data);

        return back()->with('toast', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return back()->with('toast', 'Siswa berhasil dihapus.');
    }
}
