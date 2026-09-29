<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SchoolSettingController extends Controller
{
    public function index()
    {
        $school = auth()->user()->school;

        return view('school.settings', compact('school'));
    }

    public function update(Request $request)
    {
        $school = auth()->user()->school;

        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:150'],
            'npsn'                => ['nullable', 'string', 'max:20'],
            'level'               => ['required', 'string', 'max:20'],
            'status_sekolah'      => ['required', 'in:Negeri,Swasta'],
            'email'               => ['nullable', 'email', 'max:100'],
            'phone'               => ['nullable', 'string', 'max:30'],
            'website'             => ['nullable', 'string', 'max:100'],
            'address'             => ['nullable', 'string', 'max:255'],
            'postal_code'         => ['nullable', 'string', 'max:10'],
            'city'                => ['nullable', 'string', 'max:100'],
            'province'            => ['nullable', 'string', 'max:100'],

            'header_line_1'       => ['nullable', 'string', 'max:191'],
            'header_line_2'       => ['nullable', 'string', 'max:191'],
            'header_line_3'       => ['nullable', 'string', 'max:191'],
            'header_line_4'       => ['nullable', 'string', 'max:500'],

            'principal_name'      => ['nullable', 'string', 'max:150'],
            'principal_nip'       => ['nullable', 'string', 'max:40'],
            'principal_title'     => ['nullable', 'string', 'max:50'],

            'logo_school'         => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'logo_government'     => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
            'signature'           => ['nullable', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:2048'],
        ]);

        $uploadDir = public_path('uploads/school');
        if (!File::isDirectory($uploadDir)) {
            File::makeDirectory($uploadDir, 0755, true, true);
        }

        // 1. Upload Logo Sekolah (Kiri)
        if ($request->hasFile('logo_school')) {
            $file = $request->file('logo_school');
            $filename = 'logo_' . $school->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $validated['logo_url'] = 'uploads/school/' . $filename;
        }

        // 2. Upload Logo Pemprov / Dinas (Kanan)
        if ($request->hasFile('logo_government')) {
            $file = $request->file('logo_government');
            $filename = 'pemprov_' . $school->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $validated['logo_government_url'] = 'uploads/school/' . $filename;
        }

        // 3. Upload Tanda Tangan / Stempel
        if ($request->hasFile('signature')) {
            $file = $request->file('signature');
            $filename = 'sig_' . $school->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $file->move($uploadDir, $filename);
            $validated['signature_url'] = 'uploads/school/' . $filename;
        }

        $school->update($validated);

        return back()->with('toast', 'Pengaturan sekolah, kop surat, dan data kepala sekolah berhasil diperbarui.');
    }
}
