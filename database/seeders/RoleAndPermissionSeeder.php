<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Jalankan database seeder.
     */
    public function run(): void
    {
        // Pastikan izin sudah ada sebelum peran dibuat
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. BUAT SEMUA PERMISSIONS YANG DIPERLUKAN
        $permissions = [
            // Hak akses umum
            'manage departments',
            'view holidays',

            // Hak akses (Cuti)
            'create leave request',
            'view self leave history',
            'approve leave request',
            'manage leave types',
            'manage entitlements',

            // Hak akses Workflow
            'manage workflows',
            'manage approval steps',

            // Hak akses User & Role
            'manage users',
            'manage roles and permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'Super Admin',
            'SL',
            'SPV',
            'ASMEN',
            'TL',
            'Manager',
            'Employee',
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role]);
        }

        // 3. TETAPKAN PERMISSIONS KE ROLES
        // Super Admin: Diberikan semua izin
        $adminRole = Role::where('name', '=', 'Super Admin')->first();
        $adminRole->givePermissionTo(Permission::all());

        // spv,sl, asmen,tl,manager: Dapat menyetujui, membuat cuti dan melihat hari libur
        $spvRole = Role::where('name', '=', 'SPV')->first();
        $spvRole->givePermissionTo([
            'view holidays',
            'create leave request',
            'view self leave history',
            'approve leave request',
        ]);

        // Employee: Hanya dapat mengajukan cuti dan melihat riwayatnya sendiri
        $employeeRole = Role::where('name', '=', 'Employee')->first();
        $employeeRole->givePermissionTo([
            'view holidays',
            'create leave request',
            'view self leave history',
        ]);

        // 4. BUAT AKUN ADMIN UTAMA
        $admin = User::firstOrCreate(
            ['email' => 'mnprasetya@posco.net'],
            [
                'name' => 'Median Prasetya',
                'employee_code' => '112471',
                'password' => Hash::make('@mnprasetya12'), 
                'status' => 'Active',
                'department_id'=>2,
                'hire_date'=>'2019-11-04',
                'manager_id'=>2,
            ]
        );

        // Berikan Role Super Admin
        $admin->assignRole($adminRole);
    }
}
