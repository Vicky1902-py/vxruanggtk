<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Jurusan / Program Keahlian (Majors)
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('code', 25); // misal: RPL, TKJ, IPA, IPS, AKL
            $table->string('name', 120); // misal: Rekayasa Perangkat Lunak
            $table->text('description')->nullable();
            $table->foreignId('head_of_major_id')->nullable()->constrained('employees')->nullOnDelete(); // Kaprog / Kepala Jurusan
            $table->timestamps();

            $table->unique(['school_id', 'code']);
        });

        // 2. Hubungkan Kelas ke Jurusan
        Schema::table('classes', function (Blueprint $table) {
            $table->foreignId('major_id')->nullable()->after('academic_year_id')->constrained('majors')->nullOnDelete();
        });

        // 3. Hubungkan Siswa ke Jurusan
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('major_id')->nullable()->after('class_id')->constrained('majors')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('major_id');
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('major_id');
        });

        Schema::dropIfExists('majors');
    }
};
