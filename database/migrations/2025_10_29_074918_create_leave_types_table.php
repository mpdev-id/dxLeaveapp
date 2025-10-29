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
        // Tabel Konfigurasi Jenis Cuti (LMS)
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Contoh: Cuti Tahunan, Cuti Sakit.');
            $table->decimal('default_entitlement_days', 4, 2)->default(0.00)->comment('Jatah hari standar yang diberikan.');
            $table->enum('accrual_frequency', ['Annually', 'Monthly', 'LumpSum'])->default('Annually');
            $table->boolean('is_paid')->default(true);
            $table->decimal('max_carry_over_days', 4, 2)->default(0.00)->comment('Maksimum saldo yang dibawa ke tahun berikutnya.');
            $table->boolean('requires_attachment')->default(false)->comment('Apakah memerlukan lampiran pendukung (misalnya, surat dokter).');
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
