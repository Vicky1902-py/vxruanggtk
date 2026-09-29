<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $period = $request->input('period', date('Y-m'));

        $employees = Employee::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->with(['position', 'user'])
            ->orderBy('full_name')
            ->get();

        $payrolls = Payroll::whereIn('employee_id', $employees->pluck('id'))
            ->where('period', $period)
            ->get()
            ->keyBy('employee_id');

        $totalNetSalary = (float) $payrolls->sum('net_amount');
        $totalGross = (float) $payrolls->sum('gross_amount');
        $totalDeductions = (float) $payrolls->sum('deductions');
        $totalPaid = (float) $payrolls->where('status', 'terbayar')->sum('net_amount');
        $paidCount = $payrolls->where('status', 'terbayar')->count();

        return view('payrolls.index', compact(
            'period', 'employees', 'payrolls', 'totalNetSalary', 'totalGross', 'totalDeductions', 'totalPaid', 'paidCount'
        ));
    }

    public function disburse(Request $request, Payroll $payroll)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($payroll->employee?->school_id === $schoolId, 403);

        $method = $request->input('payment_method', 'Transfer Bank');
        $payroll->update([
            'status' => 'terbayar',
            'paid_at' => now(),
            'payment_method' => $method,
        ]);

        return back()->with('toast', 'Gaji ' . $payroll->employee?->full_name . ' berhasil disalurkan (' . $method . ').');
    }

    public function disburseAll(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $period = $request->input('period', date('Y-m'));
        $method = $request->input('payment_method', 'Transfer Bank');

        $employees = Employee::where('school_id', $schoolId)->where('status', 'aktif')->pluck('id');
        $updated = Payroll::whereIn('employee_id', $employees)
            ->where('period', $period)
            ->where('status', '!=', 'terbayar')
            ->update([
                'status' => 'terbayar',
                'paid_at' => now(),
                'payment_method' => $method,
            ]);

        return back()->with('toast', "Berhasil mencairkan {$updated} slip gaji periode {$period}.");
    }

    public function generate(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $period = $request->input('period', date('Y-m'));

        $employees = Employee::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->with('position')
            ->get();

        if ($employees->isEmpty()) {
            return back()->with('toast', 'Tidak ada data pegawai aktif untuk digenerate.');
        }

        $count = 0;
        DB::transaction(function () use ($employees, $period, &$count) {
            foreach ($employees as $emp) {
                $baseSalary = (float) ($emp->position?->base_salary ?? 0);
                if ($baseSalary <= 0) {
                    $baseSalary = 3000000; // Default minimum UMR acuan jika posisi belum diisi
                }

                $gross = $baseSalary;
                $deductions = 0;
                $net = $gross - $deductions;

                Payroll::updateOrCreate(
                    ['employee_id' => $emp->id, 'period' => $period],
                    [
                        'gross_amount' => $gross,
                        'deductions' => $deductions,
                        'net_amount' => $net,
                    ]
                );
                $count++;
            }
        });

        return back()->with('toast', "Berhasil men-generate {$count} slip gaji untuk periode {$period}.");
    }

    public function update(Request $request, Payroll $payroll)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($payroll->employee?->school_id === $schoolId, 403);

        $validated = $request->validate([
            'gross_amount' => ['required', 'numeric', 'min:0'],
            'deductions' => ['required', 'numeric', 'min:0'],
        ]);

        $net = max(0, (float) $validated['gross_amount'] - (float) $validated['deductions']);

        $payroll->update([
            'gross_amount' => $validated['gross_amount'],
            'deductions' => $validated['deductions'],
            'net_amount' => $net,
        ]);

        return back()->with('toast', 'Data penggajian untuk ' . $payroll->employee?->full_name . ' berhasil diperbarui.');
    }

    public function slip(Payroll $payroll)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($payroll->employee?->school_id === $schoolId, 403);

        $employee = $payroll->employee;
        $school = $employee->school;

        return view('payrolls.slip', compact('payroll', 'employee', 'school'));
    }

    public function export(Request $request, SpreadsheetService $service)
    {
        $schoolId = auth()->user()->school_id;
        $period = $request->input('period', date('Y-m'));

        $employees = Employee::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->with(['position'])
            ->orderBy('full_name')
            ->get();

        $payrolls = Payroll::whereIn('employee_id', $employees->pluck('id'))
            ->where('period', $period)
            ->get()
            ->keyBy('employee_id');

        $headers = [
            'NO',
            'NIP',
            'NAMA_PEGAWAI',
            'JABATAN',
            'PERIODE',
            'GAJI_KOTOR_TUNJANGAN',
            'POTONGAN',
            'GAJI_BERSIH',
        ];

        $rows = [];
        $no = 1;
        foreach ($employees as $e) {
            $p = $payrolls->get($e->id);
            $rows[] = [
                $no++,
                $e->nip ?? '-',
                $e->full_name,
                $e->position?->name ?? 'Staf / Pendidik',
                $period,
                $p ? number_format((float) $p->gross_amount, 0, ',', '.') : '0',
                $p ? number_format((float) $p->deductions, 0, ',', '.') : '0',
                $p ? number_format((float) $p->net_amount, 0, ',', '.') : '0',
            ];
        }

        $filename = 'rekap_penggajian_gtk_' . $period . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Penggajian GTK');
    }
}
