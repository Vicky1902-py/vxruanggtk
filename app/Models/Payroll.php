<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = ['employee_id', 'period', 'gross_amount', 'deductions', 'net_amount'];

    protected function casts(): array
    {
        return ['gross_amount' => 'decimal:2', 'deductions' => 'decimal:2', 'net_amount' => 'decimal:2'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
