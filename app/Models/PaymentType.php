<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class PaymentType extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'default_amount', 'recurrence'];

    protected function casts(): array
    {
        return ['default_amount' => 'decimal:2'];
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }
}
