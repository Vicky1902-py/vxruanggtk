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
        Schema::table('payrolls', function (Blueprint $table) {
            $table->enum('status', ['draft', 'disetujui', 'terbayar'])->default('draft')->after('net_amount');
            $table->timestamp('paid_at')->nullable()->after('status');
            $table->string('payment_method', 40)->default('Transfer Bank')->after('paid_at');
            $table->string('notes')->nullable()->after('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn(['status', 'paid_at', 'payment_method', 'notes']);
        });
    }
};
