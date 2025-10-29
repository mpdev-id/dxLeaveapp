<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    /**
     * Jalankan database seeder.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources (HR)'],
            ['name' => 'IT Business'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate($department);
        }
    }
}
