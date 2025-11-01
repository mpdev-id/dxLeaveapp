<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'John Doe',
                'employee_code' => '12345',
                'email' => 'john.doe@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'department_id' => 1,
                'hire_date' => '2022-01-15',
                'manager_id' => 1, 
            ],
            [
                'name' => 'Jane Smith',
                'employee_code' => '67890',
                'email' => 'jane.smith@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'department_id' => 2,
                'hire_date' => '2021-11-20',
                'manager_id' => 1, 
            ],
            [
                'name' => 'Alice Johnson',
                'employee_code' => '54321',
                'email' => 'alice.johnson@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'department_id' => 1,
                'hire_date' => '2023-03-10',
                'manager_id' => 2, 
            ],
            [
                'name' => 'Robert Brown',
                'employee_code' => '98765',
                'email' => 'robert.brown@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'department_id' => 1,
                'hire_date' => '2020-07-25',
                'manager_id' => 1, 
            ],
            [
                'name' => 'Emily Davis',
                'employee_code' => '13579',
                'email' => 'emily.davis@example.com',
                'password' => Hash::make('password'),
                'status' => 'Active',
                'department_id' => 2,
                'hire_date' => '2022-08-30',
                'manager_id' => 2, 
            ],
        ];

        $employeeRole = Role::where('name', 'Employee')->first();

        foreach ($users as $userData) {
            $user = User::create($userData);
            $user->assignRole($employeeRole);
        }
    }
}
