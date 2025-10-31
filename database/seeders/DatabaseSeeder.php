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
            LeaveApprovalWorkflowSeeder::class, // Alur kerja 5 langkah yang baru
            LeaveTypeSeeder::class,
            PublicHolidaySeeder::class,
        ]);
    }
}
