<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'name', 'subdomain', 'package_tier', 'address', 'logo_url', 'is_active',
        'npsn', 'level', 'status_sekolah', 'email', 'phone', 'website',
        'postal_code', 'city', 'province',
        'header_line_1', 'header_line_2', 'header_line_3', 'header_line_4',
        'logo_government_url',
        'principal_name', 'principal_nip', 'principal_title', 'signature_url',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function getLogoSchoolAttribute(): string
    {
        return $this->logo_url ? asset($this->logo_url) : asset('img/logo.svg');
    }

    public function getLogoGovernmentAttribute(): string
    {
        return $this->logo_government_url ? asset($this->logo_government_url) : asset('img/tutwuri.svg');
    }

    public function getHeader1(): string
    {
        if (!empty($this->header_line_1)) {
            return $this->header_line_1;
        }
        $prov = strtoupper($this->province ?? 'JAWA TIMUR');
        return "PEMERINTAH PROVINSI {$prov}";
    }

    public function getHeader2(): string
    {
        return !empty($this->header_line_2) ? $this->header_line_2 : 'DINAS PENDIDIKAN';
    }

    public function getHeader3(): string
    {
        return !empty($this->header_line_3) ? $this->header_line_3 : strtoupper($this->name);
    }

    public function getHeader4(): string
    {
        if (!empty($this->header_line_4)) {
            return $this->header_line_4;
        }
        $parts = [];
        if ($this->address) $parts[] = $this->address;
        if ($this->city) $parts[] = $this->city;
        if ($this->phone) $parts[] = "Telp: {$this->phone}";
        if ($this->email) $parts[] = "Email: {$this->email}";
        if ($this->website) $parts[] = "Web: {$this->website}";

        return implode(' · ', $parts) ?: 'Jl. Pendidikan Nusantara No. 1 · Email: info@sekolah.sch.id';
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function academicYears()
    {
        return $this->hasMany(AcademicYear::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function paymentTypes()
    {
        return $this->hasMany(PaymentType::class);
    }

    public function budgetItems()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
