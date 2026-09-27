<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavingsTransaction extends Model
{
    protected $fillable = ['student_id', 'direction', 'amount', 'balance_after', 'note'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2', 'balance_after' => 'decimal:2'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
