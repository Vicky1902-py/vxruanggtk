<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'student_id', 'payment_type_id', 'amount', 'due_date', 'status'];

    protected function casts(): array
    {
        return ['due_date' => 'date', 'amount' => 'decimal:2'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function paymentType()
    {
        return $this->belongsTo(PaymentType::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
