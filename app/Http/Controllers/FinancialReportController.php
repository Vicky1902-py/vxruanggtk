<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Payroll;
use App\Models\SavingsTransaction;
use App\Services\SpreadsheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FinancialReportController extends Controller
{
    public function index(Request $request)
    {
        $school = auth()->user()->school;
        $period = $request->input('period', date('Y-m'));

        $data = $this->buildReportData($school->id, $period);

        return view('reports.financial', array_merge($data, [
            'school' => $school,
            'period' => $period,
        ]));
    }

    public function export(Request $request, SpreadsheetService $service)
    {
        $school = auth()->user()->school;
        $period = $request->input('period', date('Y-m'));

        $data = $this->buildReportData($school->id, $period);

        $headers = [
            'NO',
            'TANGGAL',
            'NO REFERENSI',
            'KATEGORI KAS',
            'URAIAN TRANSAKSI',
            'PENERIMAAN / DEBET (RP)',
            'PENGELUARAN / KREDIT (RP)',
            'SALDO BERJALAN (RP)',
        ];

        $rows = [];
        $no = 1;
        foreach ($data['ledger'] as $row) {
            $rows[] = [
                $no++,
                $row['date'],
                $row['ref'],
                $row['type'],
                $row['description'],
                $row['debit'],
                $row['credit'],
                $row['balance'],
            ];
        }

        // Baris Total Rekap
        $rows[] = [
            '',
            'TOTAL REKAPITULASI',
            '',
            '',
            '',
            $data['totalIncome'],
            $data['totalExpense'],
            $data['netBalance'],
        ];

        $filename = 'buku-kas-umum-' . $period . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'BKU ' . $period);
    }

    public function print(Request $request)
    {
        $school = auth()->user()->school;
        $period = $request->input('period', date('Y-m'));

        $data = $this->buildReportData($school->id, $period);

        return view('reports.financial_print', array_merge($data, [
            'school' => $school,
            'period' => $period,
        ]));
    }

    protected function buildReportData(int $schoolId, string $period): array
    {
        $startDate = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $period)->endOfMonth();

        // 1. Pemasukan Kas (Pembayaran Siswa SPP & Tagihan)
        $payments = Payment::whereHas('bill', fn ($q) => $q->where('school_id', $schoolId))
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->with(['bill.student.schoolClass', 'bill.paymentType'])
            ->get();
        $totalIncome = (float) $payments->sum('amount_paid');

        // 2. Pengeluaran Kas (Penggajian GTK)
        $payrolls = Payroll::whereHas('employee', fn ($q) => $q->where('school_id', $schoolId))
            ->where('period', $period)
            ->with(['employee.position'])
            ->get();
        $totalExpense = (float) $payrolls->where('status', 'terbayar')->sum('net_amount');
        if ($totalExpense <= 0 && $payrolls->isNotEmpty()) {
            // Jika belum ada yang disalurkan, tampilkan komitmen gaji periode ini
            $totalPlannedExpense = (float) $payrolls->sum('net_amount');
        } else {
            $totalPlannedExpense = (float) $payrolls->sum('net_amount');
        }

        // 3. Arus Kas Titipan Tabungan Siswa
        $savingDeposits = (float) SavingsTransaction::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', 'setor')
            ->sum('amount');

        $savingWithdrawals = (float) SavingsTransaction::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('direction', 'tarik')
            ->sum('amount');

        $netSavings = $savingDeposits - $savingWithdrawals;
        $netBalance = $totalIncome - $totalExpense;

        // 4. Bangun Ledger Kronologis (Buku Kas Umum)
        $rawItems = [];

        foreach ($payments as $p) {
            $student = $p->bill?->student;
            $className = $student?->schoolClass?->name ?? '—';
            $typeName = $p->bill?->paymentType?->name ?? 'Tagihan';

            $rawItems[] = [
                'timestamp'   => $p->paid_at ? $p->paid_at->timestamp : $startDate->timestamp,
                'date'        => $p->paid_at ? $p->paid_at->format('Y-m-d H:i') : $startDate->format('Y-m-d'),
                'ref'         => 'KW-' . str_pad($p->id, 5, '0', STR_PAD_LEFT),
                'type'        => 'Penerimaan SPP / Tagihan',
                'description' => "Kas masuk {$typeName} — {$student?->full_name} (Kelas {$className})",
                'debit'       => (float) $p->amount_paid,
                'credit'      => 0.0,
            ];
        }

        foreach ($payrolls as $pr) {
            if ($pr->status === 'terbayar' || $totalExpense === 0.0) {
                $emp = $pr->employee;
                $pos = $emp?->position?->name ?? 'GTK';
                $paidDate = $pr->paid_at ? $pr->paid_at->format('Y-m-d H:i') : $startDate->format('Y-m-d 08:00');
                $ts = $pr->paid_at ? $pr->paid_at->timestamp : $startDate->timestamp;

                $rawItems[] = [
                    'timestamp'   => $ts,
                    'date'        => $paidDate,
                    'ref'         => 'SLIP-' . $pr->id . '-' . str_replace('-', '', $period),
                    'type'        => 'Belanja Pegawai (Payroll GTK)',
                    'description' => "Honorarium / Gaji {$emp?->full_name} ({$pos})",
                    'debit'       => 0.0,
                    'credit'      => (float) $pr->net_amount,
                ];
            }
        }

        // Urutkan kronologis berdasarkan timestamp
        usort($rawItems, fn ($a, $b) => $a['timestamp'] <=> $b['timestamp']);

        // Hitung Saldo Berjalan (Running Balance)
        $running = 0.0;
        $ledger = [];
        foreach ($rawItems as $item) {
            $running += ($item['debit'] - $item['credit']);
            $item['balance'] = $running;
            $ledger[] = $item;
        }

        return compact(
            'totalIncome', 'totalExpense', 'totalPlannedExpense',
            'savingDeposits', 'savingWithdrawals', 'netSavings',
            'netBalance', 'ledger', 'payrolls', 'payments'
        );
    }
}
