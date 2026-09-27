<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\SiteSetting;

class LandingController extends Controller
{
    public function index()
    {
        $tagline = SiteSetting::get('site_tagline', 'Sistem Informasi Manajemen Sekolah multi-tenant — kelola siswa, guru, presensi, tagihan, dan pengumuman dalam satu tampilan yang tenang dan modern.');
        $heroImage = SiteSetting::get('site_hero_image', 'img/hero.svg');
        $footerText = SiteSetting::get('site_footer_text', 'Ruang GTK — Sistem Informasi Manajemen Sekolah');
        try {
            $footerPages = Page::published()->where('show_in_footer', true)->orderBy('sort_order')->get();
        } catch (\Throwable) {
            $footerPages = collect();
        }

        return view('landing', compact('tagline', 'heroImage', 'footerText', 'footerPages'));
    }
}
