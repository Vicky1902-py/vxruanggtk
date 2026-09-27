<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'status', 'show_in_footer', 'sort_order'];

    protected function casts(): array
    {
        return ['show_in_footer' => 'boolean'];
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }
}
