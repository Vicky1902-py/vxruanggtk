<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $fillable = ['entry_date', 'account', 'debit', 'credit', 'reference_type', 'reference_id'];

    protected function casts(): array
    {
        return ['entry_date' => 'date', 'debit' => 'decimal:2', 'credit' => 'decimal:2'];
    }
}
