<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->decimal('base_salary', 12, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('nip', 30)->nullable();
            $table->string('full_name');
            $table->string('status', 20)->default('aktif'); // aktif | nonaktif
            $table->timestamps();
        });

        Schema::create('attendance_employee', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->date('att_date');
            $table->string('check_in_photo_url')->nullable();
            $table->decimal('gps_lat', 10, 7)->nullable();
            $table->decimal('gps_lng', 10, 7)->nullable();
            $table->enum('status', ['hadir', 'telat', 'alpa', 'izin'])->default('hadir');
            $table->timestamps();
            $table->unique(['employee_id', 'att_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_employee');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('positions');
    }
};
