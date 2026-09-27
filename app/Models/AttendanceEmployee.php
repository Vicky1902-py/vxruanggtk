<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class AttendanceEmployee extends Model
{
    use BelongsToSchool;

    protected $table = 'attendance_employee';

    protected $fillable = ['school_id', 'employee_id', 'att_date', 'check_in_photo_url', 'gps_lat', 'gps_lng', 'status'];

    protected function casts(): array
    {
        return ['att_date' => 'date', 'gps_lat' => 'decimal:7', 'gps_lng' => 'decimal:7'];
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
