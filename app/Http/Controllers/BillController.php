<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Payment;
use App\Models\PaymentType;
use App\Models\SchoolClass;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    public function index(Request $request)
    {
        $query = Bill::with(['student.schoolClass', 'paymentType', 'payments'])
            ->orderByDesc('due_date');

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->whereHas('student', function ($sq) use ($q) {
                $sq->where('full_name', 'like', "%{$q}%")
                   ->orWhere('nis', 'like', "%{$q}%")
                   ->orWhere('nisn', 'like', "%{$q}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->integer('class_id')));
        }

        $bills = $query->get();
        $classes = SchoolClass::orderBy('name')->get();
        $paymentTypes = PaymentType::orderBy('name')->get();

        return view('bills.index', compact('bills', 'classes', 'paymentTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'payment_type_id' => ['required', 'exists:payment_types,id'],
            'class_id' => ['nullable', 'exists:classes,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
        ]);

        $students = Student::query()
            ->when($data['class_id'] ?? null, fn ($q, $cid) => $q->where('class_id', $cid))
            ->where('status', 'aktif')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('toast', 'Tidak ada siswa aktif pada target tagihan.');
        }

        $created = 0;
        $schoolId = auth()->user()->school_id;
        DB::transaction(function () use ($students, $data, $schoolId, &$created) {
            foreach ($students as $student) {
                Bill::create([
                    'school_id'       => $schoolId,
                    'student_id'      => $student->id,
                    'payment_type_id' => $data['payment_type_id'],
                    'amount'          => $data['amount'],
                    'due_date'        => $data['due_date'],
                ]);
                $created++;
            }
        });

        return back()->with('toast', "{$created} tagihan berhasil dibuat.");
    }

    public function pay(Request $request, Bill $bill)
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:1'],
            'method' => ['required', 'in:VA,QRIS,Transfer,Tunai,Retail'],
        ]);

        DB::transaction(function () use ($bill, $data) {
            Payment::create([
                'bill_id' => $bill->id,
                'amount_paid' => $data['amount_paid'],
                'method' => $data['method'],
                'paid_at' => now(),
            ]);

            $totalPaid = (float) $bill->payments()->sum('amount_paid');
            $bill->update([
                'status' => $totalPaid >= (float) $bill->amount ? 'lunas' : 'sebagian',
            ]);
        });

        return back()->with('toast', 'Pembayaran berhasil dicatat.');
    }

    public function destroy(Bill $bill)
    {
        $bill->delete();

        return back()->with('toast', 'Data tagihan berhasil dihapus.');
    }

    public function export(Request $request, \App\Services\SpreadsheetService $service)
    {
        $query = Bill::with(['student.schoolClass', 'paymentType', 'payments'])
            ->orderByDesc('due_date');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('class_id')) {
            $query->whereHas('student', fn ($q) => $q->where('class_id', $request->integer('class_id')));
        }

        $bills = $query->get();

        $headers = [
            'NO',
            'NOMOR_TAGIHAN',
            'NIS',
            'NAMA_SISWA',
            'KELAS',
            'JENIS_TAGIHAN',
            'NOMINAL_TAGIHAN',
            'TOTAL_DIBAYAR',
            'SISA_TAGIHAN',
            'JATUH_TEMPO',
            'STATUS_PEMBAYARAN',
        ];

        $rows = [];
        $no = 1;
        foreach ($bills as $b) {
            $paid = (float) $b->payments->sum('amount_paid');
            $remaining = max(0, (float) $b->amount - $paid);
            $statusLabel = match ($b->status) {
                'lunas' => 'Lunas',
                'sebagian' => 'Dibayar Sebagian',
                default => 'Belum Bayar',
            };

            $rows[] = [
                $no++,
                'TAG-' . str_pad($b->id, 6, '0', STR_PAD_LEFT),
                $b->student?->nis ?? '-',
                $b->student?->full_name ?? 'Siswa Terhapus',
                $b->student?->schoolClass ? 'Kelas ' . $b->student->schoolClass->name : '-',
                $b->paymentType?->name ?? 'SPP',
                number_format((float) $b->amount, 0, ',', '.'),
                number_format($paid, 0, ',', '.'),
                number_format($remaining, 0, ',', '.'),
                $b->due_date ? date('d/m/Y', strtotime($b->due_date)) : '-',
                $statusLabel,
            ];
        }

        $filename = 'laporan_tagihan_spp_' . date('Ymd_His') . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Laporan Tagihan SPP');
    }
}
