<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            // Leave Management
            'view-leaves',
            'create-leave',
            'edit-own-leave',
            'delete-own-leave',
            'approve-leave',
            'reject-leave',
            'cancel-leave',
            
            // User Management
            'view-users',
            'create-user',
            'edit-user',
            'delete-user',
            
            // Department Management
            'view-departments',
            'create-department',
            'edit-department',
            'delete-department',
            
            // Leave Type Management
            'view-leave-types',
            'create-leave-type',
            'edit-leave-type',
            'delete-leave-type',
            
            // Public Holiday Management
            'view-holidays',
            'create-holiday',
            'edit-holiday',
            'delete-holiday',
            
            // Entitlement Management
            'view-entitlements',
            'create-entitlement',
            'edit-entitlement',
            'delete-entitlement',
            
            // Team Management
            'view-teams',
            'create-team',
            'edit-team',
            'delete-team',
            
            // Plant Management
            'view-plants',
            'create-plant',
            'edit-plant',
            'delete-plant',
            
            // Workflow Management
            'view-workflows',
            'create-workflow',
            'edit-workflow',
            'delete-workflow',
            
            // Reports
            'view-reports',
            'export-reports',
            
            // Approver Log
            'view-approver-log',
            
            // Settings
            'manage-settings',
            'manage-roles-permissions',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign permissions to roles
        $this->assignPermissionsToRoles();
    }

    private function assignPermissionsToRoles(): void
    {
        // Super Admin - All permissions
        $superAdmin = Role::firstOrCreate(['name' => 'Super Admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Manager - Most permissions except user/role management
        $manager = Role::firstOrCreate(['name' => 'Manager']);
        $manager->givePermissionTo([
            'view-leaves', 'approve-leave', 'reject-leave',
            'view-users', 'view-departments', 'view-leave-types',
            'view-holidays', 'view-entitlements', 'view-teams',
            'view-plants', 'view-workflows', 'view-reports',
            'export-reports', 'view-approver-log',
        ]);

        // ASMEN (Assistant Manager)
        $asmen = Role::firstOrCreate(['name' => 'ASMEN']);
        $asmen->givePermissionTo([
            'view-leaves', 'approve-leave', 'reject-leave',
            'view-users', 'view-departments', 'view-leave-types',
            'view-holidays', 'view-entitlements', 'view-reports',
            'view-approver-log',
        ]);

        // SPV (Supervisor)
        $spv = Role::firstOrCreate(['name' => 'SPV']);
        $spv->givePermissionTo([
            'view-leaves', 'approve-leave', 'reject-leave',
            'view-users', 'view-departments', 'view-reports',
            'view-approver-log',
        ]);

        // TL (Team Leader)
        $tl = Role::firstOrCreate(['name' => 'TL']);
        $tl->givePermissionTo([
            'view-leaves', 'approve-leave', 'reject-leave',
            'view-users', 'view-reports', 'view-approver-log',
        ]);

        // SL (Shift Leader)
        $sl = Role::firstOrCreate(['name' => 'SL']);
        $sl->givePermissionTo([
            'view-leaves', 'approve-leave', 'reject-leave',
            'view-users', 'view-approver-log',
        ]);

        // Employee - Basic permissions
        $employee = Role::firstOrCreate(['name' => 'Employee']);
        $employee->givePermissionTo([
            'view-leaves', 'create-leave', 'edit-own-leave',
            'delete-own-leave', 'cancel-leave',
        ]);
    }

    public function down(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Delete all permissions
        Permission::whereIn('name', [
            'view-leaves', 'create-leave', 'edit-own-leave', 'delete-own-leave',
            'approve-leave', 'reject-leave', 'cancel-leave',
            'view-users', 'create-user', 'edit-user', 'delete-user',
            'view-departments', 'create-department', 'edit-department', 'delete-department',
            'view-leave-types', 'create-leave-type', 'edit-leave-type', 'delete-leave-type',
            'view-holidays', 'create-holiday', 'edit-holiday', 'delete-holiday',
            'view-entitlements', 'create-entitlement', 'edit-entitlement', 'delete-entitlement',
            'view-teams', 'create-team', 'edit-team', 'delete-team',
            'view-plants', 'create-plant', 'edit-plant', 'delete-plant',
            'view-workflows', 'create-workflow', 'edit-workflow', 'delete-workflow',
            'view-reports', 'export-reports', 'view-approver-log',
            'manage-settings', 'manage-roles-permissions',
        ])->delete();
    }
};
