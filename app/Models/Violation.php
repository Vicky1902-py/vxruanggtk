<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Violation extends Model
{
    protected $fillable = ['student_id', 'category', 'description', 'incident_date', 'points'];

    protected function casts(): array
    {
        return ['incident_date' => 'date'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
