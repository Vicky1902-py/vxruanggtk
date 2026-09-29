<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Letter;
use App\Models\LetterType;
use App\Models\Student;
use App\Services\LetterNumberService;
use App\Services\SpreadsheetService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LetterController extends Controller
{
    public function index(Request $request)
    {
        $school = auth()->user()->school;

        // Pastikan template dasar tersedia untuk sekolah
        if ($school->letterTypes()->count() === 0) {
            LetterType::seedDefaultTemplatesForSchool($school);
        }

        $query = $school->letters()->with(['letterType', 'student', 'employee', 'creator'])->latest('letter_date');

        // Filter Kategori (SK vs Surat Keluar)
        if ($request->filled('category') && in_array($request->category, ['sk', 'surat_keluar'])) {
            $query->where('category', $request->category);
        }

        // Filter Jenis Surat
        if ($request->filled('type_id')) {
            $query->where('letter_type_id', $request->type_id);
        }

        // Pencarian Kata Kunci
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($sub) use ($q) {
                $sub->where('reference_number', 'like', "%{$q}%")
                    ->orWhere('subject', 'like', "%{$q}%")
                    ->orWhere('recipient', 'like', "%{$q}%");
            });
        }

        // Filter Tahun
        $year = $request->input('year', date('Y'));
        if ($year !== 'all') {
            $query->where('year', $year);
        }

        $letters = $query->paginate(15)->withQueryString();

        // Statistik
        $totalAll = $school->letters()->count();
        $totalSK = $school->letters()->where('category', 'sk')->count();
        $totalSuratKeluar = $school->letters()->where('category', 'surat_keluar')->count();
        $letterTypes = $school->letterTypes()->where('is_active', true)->orderBy('name')->get();

        return view('letters.index', compact(
            'letters',
            'totalAll',
            'totalSK',
            'totalSuratKeluar',
            'letterTypes',
            'year'
        ));
    }

    public function create(Request $request, LetterNumberService $service)
    {
        $school = auth()->user()->school;

        if ($school->letterTypes()->count() === 0) {
            LetterType::seedDefaultTemplatesForSchool($school);
        }

        $letterTypes = $school->letterTypes()->where('is_active', true)->orderBy('category')->orderBy('name')->get();
        $students = $school->students()->with('schoolClass')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']);
        $employees = $school->employees()->with('position')->orderBy('full_name')->get(['id', 'full_name', 'nip', 'position_id']);

        $selectedTypeId = $request->input('type_id', $letterTypes->first()?->id);
        $selectedType = $letterTypes->firstWhere('id', $selectedTypeId) ?? $letterTypes->first();

        $previewData = null;
        if ($selectedType) {
            $previewData = $service->generate($school, $selectedType, $request->input('date', date('Y-m-d')));
        }

        return view('letters.create', compact('letterTypes', 'students', 'employees', 'selectedType', 'previewData'));
    }

    public function store(Request $request, LetterNumberService $service)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'letter_type_id'          => ['required', 'exists:letter_types,id'],
            'letter_date'             => ['required', 'date'],
            'subject'                 => ['required', 'string', 'max:255'],
            'recipient'               => ['nullable', 'string', 'max:255'],
            'student_id'              => ['nullable', 'exists:students,id'],
            'employee_id'             => ['nullable', 'exists:employees,id'],
            'content'                 => ['required', 'string'],
            'status'                  => ['required', 'in:draft,diterbitkan,diarsipkan'],
            'signed_by_principal'     => ['nullable', 'boolean'],
            'custom_reference_number' => ['nullable', 'string', 'max:120'],
        ]);

        $letterType = $school->letterTypes()->findOrFail($validated['letter_type_id']);
        $signedByPrincipal = $request->boolean('signed_by_principal', true);

        $letter = DB::transaction(function () use ($school, $letterType, $validated, $service, $signedByPrincipal) {
            $date = Carbon::parse($validated['letter_date']);
            $year = (int) $date->format('Y');

            // Deteksi urutan dan nomor dengan locking aman
            $sequence = $service->getNextSequence($school, $letterType->category, $year, true);
            $gen = $service->generate($school, $letterType, $validated['letter_date'], $sequence);

            $refNumber = !empty($validated['custom_reference_number'])
                ? trim($validated['custom_reference_number'])
                : $gen['reference_number'];

            return Letter::create([
                'school_id'           => $school->id,
                'letter_type_id'      => $letterType->id,
                'category'            => $letterType->category,
                'sequence_number'     => $sequence,
                'year'                => $year,
                'reference_number'    => $refNumber,
                'letter_date'         => $validated['letter_date'],
                'subject'             => $validated['subject'],
                'recipient'           => $validated['recipient'] ?? null,
                'student_id'          => $validated['student_id'] ?? null,
                'employee_id'         => $validated['employee_id'] ?? null,
                'content'             => $validated['content'],
                'status'              => $validated['status'],
                'signed_by_principal' => $signedByPrincipal,
                'created_by'          => auth()->id(),
            ]);
        });

        return redirect()->route('letters.show', $letter)->with('toast', "Surat berhasil diterbitkan dengan nomor {$letter->reference_number}.");
    }

    public function show(Letter $letter)
    {
        $this->authorizeSchool($letter);
        $letter->load(['letterType', 'student.schoolClass', 'employee.position', 'creator']);
        $school = auth()->user()->school;

        return view('letters.show', compact('letter', 'school'));
    }

    public function edit(Letter $letter)
    {
        $this->authorizeSchool($letter);
        $school = auth()->user()->school;

        $letterTypes = $school->letterTypes()->where('is_active', true)->orderBy('name')->get();
        $students = $school->students()->with('schoolClass')->orderBy('full_name')->get(['id', 'full_name', 'nis', 'class_id']);
        $employees = $school->employees()->with('position')->orderBy('full_name')->get(['id', 'full_name', 'nip', 'position_id']);

        return view('letters.edit', compact('letter', 'letterTypes', 'students', 'employees'));
    }

    public function update(Request $request, Letter $letter)
    {
        $this->authorizeSchool($letter);

        $validated = $request->validate([
            'reference_number'    => ['required', 'string', 'max:120'],
            'letter_date'         => ['required', 'date'],
            'subject'             => ['required', 'string', 'max:255'],
            'recipient'           => ['nullable', 'string', 'max:255'],
            'student_id'          => ['nullable', 'exists:students,id'],
            'employee_id'         => ['nullable', 'exists:employees,id'],
            'content'             => ['required', 'string'],
            'status'              => ['required', 'in:draft,diterbitkan,diarsipkan'],
            'signed_by_principal' => ['nullable', 'boolean'],
        ]);

        $validated['signed_by_principal'] = $request->boolean('signed_by_principal', true);

        $letter->update($validated);

        return redirect()->route('letters.show', $letter)->with('toast', 'Surat dan konten berhasil diperbarui.');
    }

    public function destroy(Letter $letter)
    {
        $this->authorizeSchool($letter);
        $ref = $letter->reference_number;
        $letter->delete();

        return redirect()->route('letters.index')->with('toast', "Surat {$ref} berhasil dihapus dari buku agenda.");
    }

    public function print(Letter $letter)
    {
        $this->authorizeSchool($letter);
        $letter->load(['letterType', 'student.schoolClass', 'employee.position']);
        $school = auth()->user()->school;

        return view('letters.print', compact('letter', 'school'));
    }

    /**
     * Endpoint AJAX untuk pratinjau nomor surat dan memuat template saat dropdown berubah.
     */
    public function previewNumber(Request $request, LetterNumberService $service)
    {
        $school = auth()->user()->school;
        $typeId = $request->input('type_id');
        $date = $request->input('date', date('Y-m-d'));

        $type = $school->letterTypes()->find($typeId);
        if (!$type) {
            return response()->json(['error' => 'Jenis surat tidak ditemukan'], 404);
        }

        $gen = $service->generate($school, $type, $date);

        // Ambil data siswa & guru jika dipilih untuk auto-fill template
        $student = $request->filled('student_id') ? $school->students()->with('schoolClass')->find($request->student_id) : null;
        $employee = $request->filled('employee_id') ? $school->employees()->with('position')->find($request->employee_id) : null;

        $parsedContent = $service->parseTemplatePlaceholders(
            $type->default_template_body ?? '',
            $school,
            [
                'reference_number' => $gen['reference_number'],
                'subject'          => $request->input('subject', ''),
                'recipient'        => $request->input('recipient', ''),
                'letter_date'      => $date,
            ],
            $student,
            $employee
        );

        return response()->json([
            'reference_number' => $gen['reference_number'],
            'sequence_number'  => $gen['sequence_number'],
            'category'         => $type->category,
            'category_badge'   => $type->category === 'sk' ? 'Surat Keputusan (SK)' : 'Surat Keluar / Dinas',
            'template_body'    => $parsedContent,
        ]);
    }

    /**
     * Ekspor Buku Agenda Surat & SK ke Excel (.xlsx).
     */
    public function export(Request $request, SpreadsheetService $excel)
    {
        $school = auth()->user()->school;
        $query = $school->letters()->with(['letterType', 'student', 'employee'])->latest('letter_date');

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('type_id')) {
            $query->where('letter_type_id', $request->type_id);
        }

        $letters = $query->get();

        $rows = [];
        foreach ($letters as $idx => $l) {
            $rows[] = [
                $idx + 1,
                strtoupper($l->category === 'sk' ? 'SK' : 'SURAT KELUAR'),
                $l->reference_number,
                $l->letter_date?->format('d/m/Y'),
                $l->letterType?->name ?? '—',
                $l->subject,
                $l->recipient ?? ($l->student?->full_name ?? ($l->employee?->full_name ?? '—')),
                ucfirst($l->status),
            ];
        }

        $headers = [
            'No',
            'Kategori Agenda',
            'Nomor Surat / SK',
            'Tanggal Surat',
            'Jenis Persuratan',
            'Perihal / Hal',
            'Tujuan / Penerima',
            'Status',
        ];

        $filename = 'Buku_Agenda_Surat_' . $school->subdomain . '_' . date('Ymd_His') . '.xlsx';

        return $excel->exportXlsx($filename, $headers, $rows, 'Buku Agenda');
    }

    private function authorizeSchool(Letter $letter): void
    {
        if ($letter->school_id !== auth()->user()->school_id) {
            abort(403, 'Akses tidak diizinkan untuk data sekolah ini.');
        }
    }
}
