<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    use BelongsToSchool;

    protected $table = 'classes';

    protected $fillable = [
        'school_id',
        'academic_year_id',
        'major_id',
        'name',
        'homeroom_teacher_id',
    ];

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function homeroomTeacher()
    {
        return $this->belongsTo(Employee::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
