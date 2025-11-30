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
            // Drop the foreign key constraint first
            $table->dropForeign(['approver_user_id']);
            
            // Make the column nullable
            $table->foreignId('approver_user_id')->nullable()->change();
            
            // Re-add the foreign key constraint
            $table->foreign('approver_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approvals_history', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['approver_user_id']);
            
            // Make the column NOT nullable again
            $table->foreignId('approver_user_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('approver_user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('restrict');
        });
    }
};
