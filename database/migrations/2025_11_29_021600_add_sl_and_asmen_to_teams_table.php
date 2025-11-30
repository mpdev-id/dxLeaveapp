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
        Schema::table('teams', function (Blueprint $table) {
            $table->foreignId('sl_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('asmen_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('teams', function (Blueprint $table) {
            $table->dropForeign(['sl_id']);
            $table->dropColumn('sl_id');
            $table->dropForeign(['asmen_id']);
            $table->dropColumn('asmen_id');
        });
    }
};
