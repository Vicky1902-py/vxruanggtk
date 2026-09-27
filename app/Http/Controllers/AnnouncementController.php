<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('schoolClass')
            ->orderByDesc('published_at')->get();
        $classes = SchoolClass::orderBy('name')->get();

        return view('announcements.index', compact('announcements', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string'],
            'class_id' => ['nullable', 'exists:classes,id'],
        ]);

        $data['class_id'] = $data['class_id'] ?: null;
        Announcement::create($data + ['published_at' => now()]);

        return back()->with('toast', 'Pengumuman berhasil dipublikasikan.');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return back()->with('toast', 'Pengumuman berhasil dihapus.');
    }
}
