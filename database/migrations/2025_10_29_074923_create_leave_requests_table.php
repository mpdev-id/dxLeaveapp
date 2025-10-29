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
        // Permintaan Cuti Transaksional (Leave Requests)
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained('leave_types')->onDelete('restrict');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('duration_days', 4, 2)->comment('Durasi cuti dalam hari/decimal.');
            $table->text('reason')->comment('Alasan pengajuan cuti.');
            $table->string('supporting_attachment_path')->nullable()->comment('Path ke file lampiran (misalnya, sertifikat medis).');

            // Status yang dikelola oleh alur kerja
            $table->enum('current_status', ['Pending', 'Approved', 'Rejected', 'Canceled', 'Draft'])->default('Draft');
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('restrict')->comment('Template alur kerja yang digunakan.');
            // Membutuhkan workflow_steps
            $table->foreignId('current_workflow_step_id')->nullable()->constrained('workflow_steps')->onDelete('set null')
                  ->comment('Langkah alur kerja yang sedang menunggu tindakan.');

            // Indeks untuk kueri cepat pada status dan pengguna
            $table->index(['user_id', 'current_status']);
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
