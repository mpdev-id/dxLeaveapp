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
        // Riwayat Persetujuan (Log Audit) - Menggunakan Polymorphic Relationship
        Schema::create('approvals_history', function (Blueprint $table) {
            $table->id();
            // Polimorfik: untuk mengaudit model apapun (LeaveRequest, ExpenseReport, dll)
            $table->morphs('approvable'); // Membuat kolom approvable_id (BIGINT) dan approvable_type (VARCHAR)

            $table->foreignId('workflow_step_id')->constrained('workflow_steps')->onDelete('restrict')
                  ->comment('Langkah alur kerja yang ditindaklanjuti.');
            $table->foreignId('approver_user_id')->constrained('users')->onDelete('restrict')
                  ->comment('Pengguna yang mengambil tindakan (Menyetujui/Menolak).');
            $table->enum('action', ['Approved', 'Rejected', 'Canceled', 'Pending'])->default('Pending')
                  ->comment('Tindakan spesifik yang dicatat.');
            $table->text('comments')->nullable()->comment('Pembenaran atau catatan dari pemberi persetujuan.');
            $table->timestamp('acted_at')->useCurrent()->comment('Waktu tindakan dicatat.');

            // Indeks untuk pencarian riwayat polimorfik yang cepat
            $table->index(['approvable_id', 'approvable_type']);
        });

     
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals_history');
    }
};
