<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Letter extends Model
{
    protected $fillable = [
        'school_id',
        'letter_type_id',
        'category',
        'sequence_number',
        'year',
        'reference_number',
        'letter_date',
        'subject',
        'recipient',
        'student_id',
        'employee_id',
        'content',
        'status',
        'signed_by_principal',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'letter_date'         => 'date',
            'sequence_number'     => 'integer',
            'year'                => 'integer',
            'signed_by_principal' => 'boolean',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function letterType(): BelongsTo
    {
        return $this->belongsTo(LetterType::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
