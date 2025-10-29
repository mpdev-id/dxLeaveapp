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
        // Tabel Konfigurasi Alur Kerja (Workflow)
        Schema::create('workflows', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nama template alur kerja.');
            $table->string('applicable_model')->comment('Model Eloquent yang menggunakan alur kerja ini (misalnya, App\\Models\\LeaveRequest).');
            $table->timestamps();
        });
    }

    /**
     * Balikkan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflows');
    }
};
