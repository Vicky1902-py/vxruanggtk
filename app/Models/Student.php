<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use BelongsToSchool;

    protected $fillable = [
        'school_id', 'class_id', 'major_id', 'guardian_id', 'nis', 'nisn',
        'full_name', 'gender', 'birth_date', 'status', 'photo_url',
    ];

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function major()
    {
        return $this->belongsTo(Major::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function attendances()
    {
        return $this->hasMany(AttendanceStudent::class);
    }

    public function violations()
    {
        return $this->hasMany(Violation::class);
    }

    public function permits()
    {
        return $this->hasMany(Permit::class);
    }

    public function counselings()
    {
        return $this->hasMany(Counseling::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function savingAccount()
    {
        return $this->hasOne(Saving::class);
    }
}
