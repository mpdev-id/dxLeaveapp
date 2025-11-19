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
        Schema::table('employee_entitlements', function (Blueprint $table) {
            $table->decimal('initial_balance', 5, 2)->change();
            $table->decimal('days_taken', 5, 2)->change();
            $table->decimal('carry_over_days', 5, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_entitlements', function (Blueprint $table) {
            $table->decimal('initial_balance', 4, 2)->change();
            $table->decimal('days_taken', 4, 2)->change();
            $table->decimal('carry_over_days', 4, 2)->change();
        });
    }
};
