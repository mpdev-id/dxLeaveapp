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
        // 1. Create teams table
        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('department_id')->constrained('departments')->onDelete('cascade');
            $table->foreignId('leader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('additional_leader_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Create plants table
        Schema::create('plants', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('team_id')->constrained('teams')->onDelete('cascade');
            $table->foreignId('supervisor_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 3. Add head_id to departments
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('head_id')->nullable()->constrained('users')->onDelete('set null');
        });

        // 4. Add plant_id to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('plant_id')->nullable()->constrained('plants')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['plant_id']);
            $table->dropColumn('plant_id');
        });

        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
            $table->dropColumn('head_id');
        });

        Schema::dropIfExists('plants');
        Schema::dropIfExists('teams');
    }
};
