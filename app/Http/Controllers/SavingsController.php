<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\SavingsTransaction;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Services\SpreadsheetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SavingsController extends Controller
{
    public function index(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $query = Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->with(['schoolClass', 'savingAccount'])
            ->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }

        if ($request->filled('q')) {
            $q = $request->string('q');
            $query->where(function ($w) use ($q) {
                $w->where('full_name', 'like', "%{$q}%")
                    ->orWhere('nis', 'like', "%{$q}%");
            });
        }

        $students = $query->paginate(25)->withQueryString();
        $classes = SchoolClass::where('school_id', $schoolId)->orderBy('name')->get();

        // Telemetri Total Tabungan
        $totalBalance = Saving::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))->sum('balance');
        $recentTransactions = SavingsTransaction::whereHas('student', fn ($s) => $s->where('school_id', $schoolId))
            ->with('student.schoolClass')
            ->latest()
            ->take(10)
            ->get();

        return view('savings.index', compact(
            'students', 'classes', 'totalBalance', 'recentTransactions'
        ));
    }

    public function store(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $validated = $request->validate([
            'student_id' => ['required', 'exists:students,id'],
            'direction' => ['required', 'in:setor,tarik'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'note' => ['nullable', 'string', 'max:255'],
        ], [
            'amount.min' => 'Nominal transaksi minimal Rp 1.000.',
            'student_id.required' => 'Pilih siswa terlebih dahulu.',
        ]);

        $student = Student::where('school_id', $schoolId)->findOrFail($validated['student_id']);

        $res = DB::transaction(function () use ($student, $validated) {
            $saving = Saving::firstOrCreate(
                ['student_id' => $student->id],
                ['balance' => 0]
            );

            $amount = (float) $validated['amount'];
            $currentBalance = (float) $saving->balance;

            if ($validated['direction'] === 'tarik' && $currentBalance < $amount) {
                return [
                    'success' => false,
                    'message' => 'Saldo tabungan tidak mencukupi. Saldo saat ini: Rp ' . number_format($currentBalance, 0, ',', '.'),
                ];
            }

            $newBalance = $validated['direction'] === 'setor'
                ? $currentBalance + $amount
                : $currentBalance - $amount;

            $transaction = SavingsTransaction::create([
                'student_id' => $student->id,
                'direction' => $validated['direction'],
                'amount' => $amount,
                'balance_after' => $newBalance,
                'note' => $validated['note'] ?: ($validated['direction'] === 'setor' ? 'Setoran tunai' : 'Penarikan tabungan'),
            ]);

            $saving->update([
                'balance' => $newBalance,
                'last_transaction_at' => now(),
            ]);

            return [
                'success' => true,
                'transaction' => $transaction,
                'newBalance' => $newBalance,
            ];
        });

        if (! $res['success']) {
            return back()->with('toast', 'Gagal: ' . $res['message']);
        }

        $dirText = $validated['direction'] === 'setor' ? 'Setoran' : 'Penarikan';
        return back()->with('toast', "{$dirText} Rp " . number_format($validated['amount'], 0, ',', '.') . " untuk {$student->full_name} berhasil dicatat. Sisa saldo: Rp " . number_format($res['newBalance'], 0, ',', '.'));
    }

    public function show(Student $student)
    {
        $schoolId = auth()->user()->school_id;
        abort_unless($student->school_id === $schoolId, 403);

        $saving = Saving::firstOrCreate(['student_id' => $student->id], ['balance' => 0]);
        $transactions = SavingsTransaction::where('student_id', $student->id)
            ->latest()
            ->paginate(30);

        return view('savings.show', compact('student', 'saving', 'transactions'));
    }

    public function export(Request $request, SpreadsheetService $service)
    {
        $schoolId = auth()->user()->school_id;

        $query = Student::where('school_id', $schoolId)
            ->where('status', 'aktif')
            ->with(['schoolClass', 'savingAccount'])
            ->orderBy('full_name');

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'));
        }

        $students = $query->get();

        $headers = [
            'NO',
            'NIS',
            'NAMA_SISWA',
            'KELAS',
            'TOTAL_SALDO_TABUNGAN',
            'TERAKHIR_TRANSAKSI',
        ];

        $rows = [];
        $no = 1;
        foreach ($students as $s) {
            $balance = (float) ($s->savingAccount?->balance ?? 0);
            $lastTx = $s->savingAccount?->last_transaction_at
                ? $s->savingAccount->last_transaction_at->format('d/m/Y H:i')
                : 'Belum ada transaksi';

            $rows[] = [
                $no++,
                $s->nis ?? '-',
                $s->full_name,
                $s->schoolClass ? 'Kelas ' . $s->schoolClass->name : '-',
                number_format($balance, 0, ',', '.'),
                $lastTx,
            ];
        }

        $filename = 'rekap_tabungan_siswa_' . date('Ymd_His') . '.xlsx';
        return $service->exportXlsx($filename, $headers, $rows, 'Tabungan Siswa');
    }
}
