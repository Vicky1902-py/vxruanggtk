<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Counseling extends Model
{
    protected $fillable = ['student_id', 'type', 'notes', 'session_date'];

    protected function casts(): array
    {
        return ['session_date' => 'date'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
