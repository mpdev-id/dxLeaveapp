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
        // Langkah-Langkah dalam Alur Kerja (Workflow Steps)
        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('workflows')->onDelete('cascade');
            $table->unsignedTinyInteger('step_number')->comment('Urutan langkah dalam alur kerja.');

            // Kriteria Persetujuan
            $table->enum('required_approver_type', ['Manager', 'Role', 'User', 'DepartmentHead'])
                  ->comment('Tipe entitas yang wajib menyetujui.');

            // Kolom opsional, bergantung pada required_approver_type.
            // Spatie menggunakan tabel 'roles' secara default.
            $table->foreignId('approver_role_id')->nullable()->constrained('roles')->onDelete('set null');
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->unsignedTinyInteger('required_approvals')->default(1)->comment('Jumlah minimum persetujuan yang dibutuhkan untuk langkah ini.');
            $table->boolean('is_final_step')->default(false)->comment('Jika true, persetujuan lengkap setelah langkah ini.');

            $table->index(['workflow_id', 'step_number']);
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_steps');
    }
};
