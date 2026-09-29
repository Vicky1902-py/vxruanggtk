<?php

namespace App\Http\Controllers;

use App\Models\LetterType;
use Illuminate\Http\Request;

class LetterTypeController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        if ($school->letterTypes()->count() === 0) {
            LetterType::seedDefaultTemplatesForSchool($school);
        }

        $letterTypes = $school->letterTypes()->withCount('letters')->orderBy('category')->orderBy('name')->get();

        return view('letters.types', compact('letterTypes'));
    }

    public function store(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'code'                  => ['required', 'string', 'max:50'],
            'category'              => ['required', 'in:sk,surat_keluar'],
            'classification_code'   => ['nullable', 'string', 'max:30'],
            'numbering_format'      => ['required', 'string', 'max:200'],
            'padding_digits'        => ['required', 'integer', 'min:1', 'max:6'],
            'default_template_body' => ['nullable', 'string'],
        ]);

        $validated['school_id'] = $school->id;
        $validated['is_active'] = true;

        LetterType::create($validated);

        return back()->with('toast', "Jenis surat '{$validated['name']}' berhasil ditambahkan.");
    }

    public function update(Request $request, LetterType $letterType)
    {
        if ($letterType->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:150'],
            'code'                  => ['required', 'string', 'max:50'],
            'category'              => ['required', 'in:sk,surat_keluar'],
            'classification_code'   => ['nullable', 'string', 'max:30'],
            'numbering_format'      => ['required', 'string', 'max:200'],
            'padding_digits'        => ['required', 'integer', 'min:1', 'max:6'],
            'default_template_body' => ['nullable', 'string'],
            'is_active'             => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        $letterType->update($validated);

        return back()->with('toast', "Format jenis surat '{$letterType->name}' berhasil diperbarui.");
    }

    public function destroy(LetterType $letterType)
    {
        if ($letterType->school_id !== auth()->user()->school_id) {
            abort(403);
        }

        if ($letterType->letters()->count() > 0) {
            $letterType->update(['is_active' => false]);
            return back()->with('toast', "Jenis surat '{$letterType->name}' telah dinonaktifkan karena telah memiliki arsip surat.");
        }

        $name = $letterType->name;
        $letterType->delete();

        return back()->with('toast', "Jenis surat '{$name}' berhasil dihapus.");
    }
}
