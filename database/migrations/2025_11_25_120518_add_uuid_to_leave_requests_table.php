<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->uuid('uuid')->after('id')->nullable();
        });

        // Populate existing records
        $requests = DB::table('leave_requests')->get();
        foreach ($requests as $request) {
            DB::table('leave_requests')
                ->where('id', $request->id)
                ->update(['uuid' => (string) Str::uuid()]);
        }

        // Make it not nullable and unique after population
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->uuid('uuid')->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};
