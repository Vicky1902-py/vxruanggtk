<?php

namespace App\Http\Controllers;

use App\Models\Page;

class PublicPageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::published()->where('slug', $slug)->firstOrFail();
        $footerPages = Page::published()->where('show_in_footer', true)->orderBy('sort_order')->get();

        return view('page', compact('page', 'footerPages'));
    }
}
