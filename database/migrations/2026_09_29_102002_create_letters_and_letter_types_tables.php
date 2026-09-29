<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('letter_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('code', 50);
            $table->string('category', 40)->default('surat_keluar'); // 'sk' or 'surat_keluar'
            $table->string('classification_code', 30)->nullable(); // Misal: 800 (Kepegawaian), 421.5 (Kurikulum), 422 (Kesiswaan), 005 (Undangan)
            $table->string('numbering_format', 200)->default('{KODE}/{NOMOR}/{SEKOLAH}/{BULAN_ROMAWI}/{TAHUN}');
            $table->unsignedSmallInteger('padding_digits')->default(3); // 3 digit: 001, 002
            $table->text('default_template_body')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['school_id', 'category']);
        });

        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();
            $table->foreignId('letter_type_id')->constrained('letter_types')->cascadeOnDelete();
            $table->string('category', 40)->default('surat_keluar'); // 'sk' or 'surat_keluar'
            $table->unsignedInteger('sequence_number'); // 1, 2, 3...
            $table->unsignedSmallInteger('year'); // 2026
            $table->string('reference_number', 120); // Nomor surat jadi lengkap
            $table->date('letter_date');
            $table->string('subject', 255);
            $table->string('recipient', 255)->nullable();
            $table->foreignId('student_id')->nullable()->constrained('students')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->longText('content');
            $table->string('status', 30)->default('diterbitkan'); // 'draft', 'diterbitkan', 'diarsipkan'
            $table->boolean('signed_by_principal')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['school_id', 'category', 'year']);
            $table->index(['school_id', 'reference_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
        Schema::dropIfExists('letter_types');
    }
};
