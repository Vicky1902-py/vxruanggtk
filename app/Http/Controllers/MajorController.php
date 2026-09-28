<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Major;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;

class MajorController extends Controller
{
    public function index()
    {
        $majors = Major::with('headOfMajor')
            ->withCount(['classes', 'students'])
            ->orderBy('code')
            ->get();

        $teachers = Employee::orderBy('full_name')->get();

        return view('majors.index', compact('majors', 'teachers'));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'code' => ['required', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'head_of_major_id' => ['nullable', 'exists:employees,id'],
        ]);

        $exists = Major::where('school_id', $schoolId)->where('code', $data['code'])->exists();
        if ($exists) {
            return back()->with('toast', 'Kode jurusan "' . $data['code'] . '" sudah terdaftar.');
        }

        $data['school_id'] = $schoolId;
        $data['head_of_major_id'] = $data['head_of_major_id'] ?: null;

        Major::create($data);

        return back()->with('toast', 'Jurusan "' . $data['name'] . ' (' . $data['code'] . ')" berhasil ditambahkan.');
    }

    public function update(Request $request, Major $major)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'code' => ['required', 'string', 'max:25'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'head_of_major_id' => ['nullable', 'exists:employees,id'],
        ]);

        $exists = Major::where('school_id', $schoolId)
            ->where('code', $data['code'])
            ->where('id', '!=', $major->id)
            ->exists();

        if ($exists) {
            return back()->with('toast', 'Kode jurusan "' . $data['code'] . '" sudah digunakan jurusan lain.');
        }

        $data['head_of_major_id'] = $data['head_of_major_id'] ?: null;

        $major->update($data);

        return back()->with('toast', 'Jurusan "' . $major->code . '" berhasil diperbarui.');
    }

    public function destroy(Major $major)
    {
        $major->delete();

        return back()->with('toast', 'Jurusan berhasil dihapus.');
    }

    public function template(SpreadsheetService $service)
    {
        $headers = ['KODE_JURUSAN', 'NAMA_JURUSAN', 'NIP_KAPROG', 'KETERANGAN'];
        $sampleRows = [
            ['RPL', 'Rekayasa Perangkat Lunak', '198505122010011003', 'Konsentrasi Rekayasa Perangkat Lunak & Aplikasi'],
            ['TKJ', 'Teknik Komputer & Jaringan', '', 'Konsentrasi Jaringan Komputer, Server & Cloud'],
            ['AKL', 'Akuntansi & Keuangan Lembaga', '', 'Konsentrasi Akuntansi Perbankan & Pembukuan'],
        ];

        return $service->downloadTemplate('template_jurusan.xlsx', $headers, $sampleRows);
    }

    public function import(Request $request, SpreadsheetService $service)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'],
        ]);

        $rows = $service->parseFile($request->file('file'));
        if (count($rows) < 2) {
            return back()->with('toast', 'File tidak memiliki baris data yang valid.');
        }

        $schoolId = auth()->user()->school_id;
        $imported = 0;

        // Baris 0 adalah Header
        foreach (array_slice($rows, 1) as $row) {
            $code = strtoupper(trim($row[0] ?? ''));
            $name = trim($row[1] ?? '');
            $nipKaprog = trim($row[2] ?? '');
            $desc = trim($row[3] ?? '');

            if (empty($code) || empty($name)) {
                continue;
            }

            $headId = null;
            if (!empty($nipKaprog)) {
                $head = Employee::where('school_id', $schoolId)
                    ->where(fn ($q) => $q->where('nip', $nipKaprog)->orWhere('full_name', 'like', "%{$nipKaprog}%"))
                    ->first();
                if ($head) {
                    $headId = $head->id;
                }
            }

            Major::updateOrCreate(
                ['school_id' => $schoolId, 'code' => $code],
                [
                    'name' => $name,
                    'description' => $desc ?: null,
                    'head_of_major_id' => $headId,
                ]
            );

            $imported++;
        }

        return back()->with('toast', "Berhasil mengimpor {$imported} data jurusan.");
    }
}
