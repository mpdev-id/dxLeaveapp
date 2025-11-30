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
        // Modify the enum to include new approver types
        DB::statement("ALTER TABLE workflow_steps MODIFY COLUMN required_approver_type ENUM('Manager', 'Role', 'User', 'DepartmentHead', 'PlantSupervisor', 'TeamLeader')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to original enum (might fail if data exists with new values, but for dev it's fine)
        DB::statement("ALTER TABLE workflow_steps MODIFY COLUMN required_approver_type ENUM('Manager', 'Role', 'User', 'DepartmentHead')");
    }
};
