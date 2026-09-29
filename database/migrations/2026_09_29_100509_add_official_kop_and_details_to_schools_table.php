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
        Schema::table('schools', function (Blueprint $table) {
            $table->string('npsn', 20)->nullable()->after('name');
            $table->string('level', 20)->default('SMK')->after('npsn');
            $table->string('status_sekolah', 20)->default('Negeri')->after('level');
            $table->string('email', 100)->nullable()->after('address');
            $table->string('phone', 30)->nullable()->after('email');
            $table->string('website', 100)->nullable()->after('phone');
            $table->string('postal_code', 10)->nullable()->after('website');
            $table->string('city', 100)->nullable()->after('postal_code');
            $table->string('province', 100)->nullable()->after('city');

            // Kop Surat Resmi & Dual Logo
            $table->string('header_line_1', 191)->nullable()->after('province');
            $table->string('header_line_2', 191)->nullable()->after('header_line_1');
            $table->string('header_line_3', 191)->nullable()->after('header_line_2');
            $table->text('header_line_4')->nullable()->after('header_line_3');
            $table->string('logo_government_url', 255)->nullable()->after('logo_url');

            // Kepala Sekolah
            $table->string('principal_name', 150)->nullable()->after('logo_government_url');
            $table->string('principal_nip', 40)->nullable()->after('principal_name');
            $table->string('principal_title', 50)->default('Kepala Sekolah')->after('principal_nip');
            $table->string('signature_url', 255)->nullable()->after('principal_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn([
                'npsn', 'level', 'status_sekolah', 'email', 'phone', 'website',
                'postal_code', 'city', 'province',
                'header_line_1', 'header_line_2', 'header_line_3', 'header_line_4',
                'logo_government_url',
                'principal_name', 'principal_nip', 'principal_title', 'signature_url'
            ]);
        });
    }
};
