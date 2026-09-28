<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Major extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id',
        'code',
        'name',
        'description',
        'head_of_major_id',
    ];

    public function headOfMajor()
    {
        return $this->belongsTo(Employee::class, 'head_of_major_id');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class, 'major_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'major_id');
    }
}
