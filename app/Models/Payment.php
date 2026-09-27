<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = ['bill_id', 'amount_paid', 'method', 'gateway_ref', 'paid_at'];

    protected function casts(): array
    {
        return ['amount_paid' => 'decimal:2', 'paid_at' => 'datetime'];
    }

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
