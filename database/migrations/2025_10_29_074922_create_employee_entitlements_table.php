<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Jatah Cuti Karyawan (Entitlement)
        Schema::create('employee_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('restrict');
            $table->year('year')->comment('Tahun jatah cuti berlaku.');
            $table->decimal('initial_balance', 4, 2)->comment('Total hari cuti yang diberikan.');
            $table->decimal('days_taken', 4, 2)->default(0.00)->comment('Hari yang sudah digunakan.');
            $table->decimal('carry_over_days', 4, 2)->default(0.00)->comment('Saldo yang dibawa dari tahun sebelumnya.');

            // Memastikan jatah cuti unik per pengguna, tahun, dan jenis cuti
            $table->unique(['user_id', 'leave_type_id', 'year']);

            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_entitlements');
    }
};
