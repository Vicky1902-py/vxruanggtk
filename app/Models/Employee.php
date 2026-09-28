<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'position_id', 'user_id', 'nip', 'full_name', 'status'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'homeroom_teacher_id');
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceEmployee::class);
    }

    public function headOfMajors()
    {
        return $this->hasMany(Major::class, 'head_of_major_id');
    }
}
