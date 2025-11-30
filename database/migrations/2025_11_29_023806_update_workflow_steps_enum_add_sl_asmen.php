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
        DB::statement("ALTER TABLE workflow_steps MODIFY COLUMN required_approver_type ENUM('Manager', 'Role', 'User', 'DepartmentHead', 'PlantSupervisor', 'TeamLeader', 'ShiftLeader', 'AssistantManager')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE workflow_steps MODIFY COLUMN required_approver_type ENUM('Manager', 'Role', 'User', 'DepartmentHead', 'PlantSupervisor', 'TeamLeader')");
    }
};
