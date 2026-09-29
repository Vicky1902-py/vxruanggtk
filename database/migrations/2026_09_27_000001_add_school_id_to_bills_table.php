<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah school_id ke tabel bills untuk multi-tenant isolation
        Schema::table('bills', function (Blueprint $table) {
            // Tambah setelah id, nullable dulu agar data lama tidak error
            $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
        });

        // Backfill: isi school_id dari student.school_id untuk data yang sudah ada
        if (DB::getDriverName() === 'mysql') {
            DB::statement('
                UPDATE bills b
                JOIN students s ON s.id = b.student_id
                SET b.school_id = s.school_id
                WHERE b.school_id IS NULL
            ');
            Schema::table('bills', function (Blueprint $table) {
                $table->foreignId('school_id')->nullable(false)->change();
            });
        } else {
            DB::statement('
                UPDATE bills
                SET school_id = (SELECT school_id FROM students WHERE students.id = bills.student_id)
                WHERE school_id IS NULL
            ');
        }
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn('school_id');
        });
    }
};
