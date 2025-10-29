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
        // Urutan sangat penting:
        // 1. Tabel Acuan Dasar (Departemen)
        // $this->call(DepartmentsSeeder::class);

        // 2. Roles, Permissions, dan Admin User
        // Role & User harus dibuat setelah Department (untuk admin user)
        $this->call(RoleAndPermissionSeeder::class);

        // 3. Jenis Cuti
        // $this->call(LeaveTypeSeeder::class);

        // Anda dapat menambahkan seeder lain di sini
        // $this->call(WorkflowSeeder::class);
        // $this->call(PublicHolidaySeeder::class);
    }
}
