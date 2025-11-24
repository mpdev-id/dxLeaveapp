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
        Schema::table('leave_requests', function (Blueprint $table) {
            // 1. Drop existing foreign key
            $table->dropForeign(['workflow_id']);
            
            // 2. Make column nullable
            $table->foreignId('workflow_id')->nullable()->change();

            // 3. Add new foreign key with SET NULL
            $table->foreign('workflow_id')
                  ->references('id')
                  ->on('workflows')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['workflow_id']);

            // Revert to restrict (and ideally not null, but that's risky if data became null)
            // $table->foreignId('workflow_id')->nullable(false)->change(); 
            
            $table->foreign('workflow_id')
                  ->references('id')
                  ->on('workflows')
                  ->onDelete('restrict');
        });
    }
};
