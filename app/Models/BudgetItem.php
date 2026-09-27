<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'category', 'planned_amount', 'realized_amount', 'period'];

    protected function casts(): array
    {
        return ['planned_amount' => 'decimal:2', 'realized_amount' => 'decimal:2'];
    }
}
