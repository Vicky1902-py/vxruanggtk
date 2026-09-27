<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('position')->orderBy('full_name')->get();
        $positions = Position::orderBy('name')->get();

        return view('employees.index', compact('employees', 'positions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'nip' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $data['position_id'] = $data['position_id'] ?: null;
        $data['nip'] = $data['nip'] ?: null;

        Employee::create($data);

        return back()->with('toast', 'Pegawai "' . $data['full_name'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Employee $employee)
    {
        $data = $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'nip' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:aktif,nonaktif'],
        ]);

        $data['position_id'] = $data['position_id'] ?: null;
        $data['nip'] = $data['nip'] ?: null;

        $employee->update($data);

        return back()->with('toast', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with('toast', 'Pegawai berhasil dihapus.');
    }
}
