<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Jalankan database seeders.
     */
    public function run(): void
    {
        $this->call([
            DepartmentsSeeder::class,
            RoleAndPermissionSeeder::class,
            UserSeeder::class,
            UniversalLeaveWorkflowSeeder::class, // Universal workflow for all teams
            LeaveTypeSeeder::class,
            PublicHolidaySeeder::class,
        ]);
    }
}
