<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Saving extends Model
{
    protected $fillable = ['student_id', 'balance', 'last_transaction_at'];

    protected function casts(): array
    {
        return ['balance' => 'decimal:2', 'last_transaction_at' => 'datetime'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function transactions()
    {
        return $this->hasMany(SavingsTransaction::class)->latest();
    }
}
