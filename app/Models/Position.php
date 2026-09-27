<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use BelongsToSchool;

    protected $fillable = ['school_id', 'name', 'base_salary'];

    protected function casts(): array
    {
        return ['base_salary' => 'decimal:2'];
    }

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
