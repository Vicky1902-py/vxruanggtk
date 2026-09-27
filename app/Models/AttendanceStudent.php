<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AttendanceStudent extends Model
{
    protected $table = 'attendance_student';

    protected $fillable = ['student_id', 'att_date', 'status', 'recorded_by'];

    protected function casts(): array
    {
        return ['att_date' => 'date'];
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function recorder()
    {
        return $this->belongsTo(Employee::class, 'recorded_by');
    }
}
