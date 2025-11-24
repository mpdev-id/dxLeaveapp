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
        Schema::table('approvals_history', function (Blueprint $table) {
            // 1. Drop existing foreign key
            $table->dropForeign(['workflow_step_id']);
            
            // 2. Make column nullable
            $table->foreignId('workflow_step_id')->nullable()->change();

            // 3. Add new foreign key with SET NULL
            $table->foreign('workflow_step_id')
                  ->references('id')
                  ->on('workflow_steps')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals_history', function (Blueprint $table) {
            $table->dropForeign(['workflow_step_id']);

            // We cannot easily revert nullable to not null if there are null values.
            // But for structure:
            // $table->foreignId('workflow_step_id')->nullable(false)->change(); 
            
            // Revert to restrict
            $table->foreign('workflow_step_id')
                  ->references('id')
                  ->on('workflow_steps')
                  ->onDelete('restrict');
        });
    }
};
