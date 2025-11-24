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
        Schema::table('approvals_history', function (Blueprint $table) {
            // Explicitly drop the foreign key by its exact name from the error message
            // We use a try-catch or check to avoid errors if it's already gone/changed
            // But Schema builder doesn't support 'if exists' for constraints easily.
            // Let's rely on the fact that the error says it exists.
            
            $table->dropForeign('approvals_history_workflow_step_id_foreign');
        });

        Schema::table('approvals_history', function (Blueprint $table) {
            // Make sure column is nullable
            $table->foreignId('workflow_step_id')->nullable()->change();
            
            // Re-add the foreign key with SET NULL
            $table->foreign('workflow_step_id', 'approvals_history_workflow_step_id_foreign')
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
            $table->dropForeign('approvals_history_workflow_step_id_foreign');
            
            $table->foreign('workflow_step_id', 'approvals_history_workflow_step_id_foreign')
                  ->references('id')
                  ->on('workflow_steps')
                  ->onDelete('restrict');
        });
    }
};
