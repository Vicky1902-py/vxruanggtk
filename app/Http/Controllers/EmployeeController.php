<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Position;
use App\Models\Role;
use App\Models\User;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with(['position', 'user'])->orderBy('full_name')->get();
        $positions = Position::orderBy('name')->get();

        return view('employees.index', compact('employees', 'positions'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'position_id' => ['nullable', 'exists:positions,id'],
            'nip' => ['nullable', 'string', 'max:30'],
            'full_name' => ['required', 'string', 'max:120'],
            'status' => ['required', 'in:aktif,nonaktif'],
            'create_user' => ['nullable', 'boolean'],
            'username' => ['nullable', 'string', 'max:50'],
        ]);

        $data['school_id'] = $schoolId;
        $data['position_id'] = $data['position_id'] ?? null;
        $data['nip'] = $data['nip'] ?? null;

        // Buat user akun jika diminta
        $userId = null;
        if (!empty($request->input('username'))) {
            $roleGuru = Role::firstOrCreate(['name' => 'guru']);
            $user = User::firstOrCreate(
                ['username' => $request->input('username')],
                [
                    'school_id' => $schoolId,
                    'role_id' => $roleGuru->id,
                    'password' => Hash::make('password'),
                ]
            );
            $userId = $user->id;
        }

        $data['user_id'] = $userId;
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

        $data['position_id'] = $data['position_id'] ?? null;
        $data['nip'] = $data['nip'] ?? null;

        $employee->update($data);

        return back()->with('toast', 'Data pegawai berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return back()->with('toast', 'Pegawai berhasil dihapus.');
    }

    public function template(SpreadsheetService $service)
    {
        $headers = [
            'NIP',
            'NAMA_LENGKAP',
            'JABATAN',
            'STATUS',
            'USERNAME',
            'EMAIL',
        ];

        $sampleRows = [
            ['198505122010011003', 'Budi Santoso, S.Pd.', 'Guru', 'aktif', 'bsantoso', 'budi@sekolah.sch.id'],
            ['199003152015021004', 'Siti Rahayu', 'Tenaga Usaha', 'aktif', 'tu.siti', 'siti@sekolah.sch.id'],
            ['198807202012012005', 'Rina Marlina, S.E.', 'Bendahara Sekolah', 'aktif', 'rina.marlina', 'rina@sekolah.sch.id'],
            ['197506102005011002', 'Drs. Hendra Wijaya, M.Pd.', 'Kepala Sekolah', 'aktif', 'hendra.wijaya', 'hendra@sekolah.sch.id'],
        ];

        return $service->downloadTemplate('template_gtk.xlsx', $headers, $sampleRows);
    }

    public function import(Request $request, SpreadsheetService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $rows = $service->parseFile($request->file('file'));
        if (count($rows) < 2) {
            return back()->with('toast', 'File spreadsheet kosong atau tidak memiliki baris data.');
        }

        $schoolId = auth()->user()->school_id;
        $roleGuru = Role::firstOrCreate(['name' => 'guru']);
        $roleTu = Role::firstOrCreate(['name' => 'staff_tu']);
        $imported = 0;

        foreach (array_slice($rows, 1) as $row) {
            $nip = trim($row[0] ?? '');
            $fullName = trim($row[1] ?? '');
            $positionName = trim($row[2] ?? 'Guru');
            $status = strtolower(trim($row[3] ?? 'aktif'));
            $username = trim($row[4] ?? '');
            $email = trim($row[5] ?? '');

            if (empty($fullName)) {
                continue;
            }

            if (!in_array($status, ['aktif', 'nonaktif'])) {
                $status = 'aktif';
            }

            // 1. Hubungkan / Buat Jabatan
            $positionId = null;
            if (!empty($positionName)) {
                $pos = Position::firstOrCreate(
                    ['school_id' => $schoolId, 'name' => $positionName],
                    ['base_salary' => 4000000]
                );
                $positionId = $pos->id;
            }

            // 2. Buat akun user jika username diisi
            $userId = null;
            if (!empty($username)) {
                $targetRole = (stripos($positionName, 'usaha') !== false || stripos($positionName, 'tu') !== false)
                    ? $roleTu
                    : $roleGuru;

                $user = User::firstOrCreate(
                    ['username' => $username],
                    [
                        'school_id' => $schoolId,
                        'role_id' => $targetRole->id,
                        'email' => $email ?: null,
                        'password' => Hash::make('password'),
                    ]
                );
                $userId = $user->id;
            }

            // 3. Update or Create Employee
            $criteria = ['school_id' => $schoolId];
            if (!empty($nip)) {
                $criteria['nip'] = $nip;
            } else {
                $criteria['full_name'] = $fullName;
            }

            Employee::updateOrCreate($criteria, [
                'nip' => $nip ?: null,
                'full_name' => $fullName,
                'position_id' => $positionId,
                'user_id' => $userId,
                'status' => $status,
            ]);

            $imported++;
        }

        return back()->with('toast', "Berhasil mengimpor {$imported} data GTK / Pegawai!");
    }

    public function export(Request $request, SpreadsheetService $service)
    {
        $employees = Employee::with(['position', 'user.role'])->orderBy('full_name')->get();

        $headers = [
            'NO',
            'NIP / NUPTK',
            'NAMA_LENGKAP',
            'JABATAN',
            'STATUS',
            'USERNAME_AKUN',
            'EMAIL',
            'ROLE_AKUN',
        ];

        $rows = [];
        $no = 1;
        foreach ($employees as $e) {
            $rows[] = [
                $no++,
                $e->nip ?? '-',
                $e->full_name,
                $e->position?->name ?? 'Staf / Pendidik',
                ucfirst($e->status),
                $e->user?->username ?? '-',
                $e->user?->email ?? '-',
                $e->user?->role?->name ?? '-',
            ];
        }

        $filename = 'data_gtk_' . date('Ymd_His') . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Data GTK');
    }
}
