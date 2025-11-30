<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support ALTER ENUM directly, so we need to modify the column
        DB::statement("ALTER TABLE approvals_history MODIFY COLUMN action ENUM('Approved', 'Rejected', 'Canceled', 'Pending', 'Auto-Approved') DEFAULT 'Pending' COMMENT 'Tindakan spesifik yang dicatat.'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to original ENUM values
        DB::statement("ALTER TABLE approvals_history MODIFY COLUMN action ENUM('Approved', 'Rejected', 'Canceled', 'Pending') DEFAULT 'Pending' COMMENT 'Tindakan spesifik yang dicatat.'");
    }
};
