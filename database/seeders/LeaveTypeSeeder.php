<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class LeaveTypeSeeder extends Seeder
{
    /**
     * Jalankan database seeder.
     */
    public function run(): void
    {
        $leaveTypes = [
            [
                'name' => 'Annual Leave',
                'default_entitlement_days' => 12.0,
                'accrual_frequency' => 'Annually',
                'is_paid' => true,
                'max_carry_over_days' => 6.0,
                'requires_attachment' => false,
            ],
            [
                'name' => 'Sick Leave',
                'default_entitlement_days' => 6.0,
                'accrual_frequency' => 'Annually',
                'is_paid' => true,
                'max_carry_over_days' => 0.0,
                'requires_attachment' => true, // Membutuhkan Surat Dokter
            ],
            [
                'name' => 'Maternity Leave',
                'default_entitlement_days' => 90.0,
                'accrual_frequency' => 'Per Request',
                'is_paid' => true,
                'max_carry_over_days' => 0.0,
                'requires_attachment' => true,
            ],
            [
                'name' => 'Special Leave',
                'default_entitlement_days' => 14.0,
                'accrual_frequency' => 'Per Request',
                'is_paid' => true,
                'max_carry_over_days' => 0.0,
                'requires_attachment' => true,
            ],
            [
                'name' => 'Unpaid Leave',
                'default_entitlement_days' => 0.0,
                'accrual_frequency' => 'Per Request',
                'is_paid' => false,
                'max_carry_over_days' => 0.0,
                'requires_attachment' => false,
            ],
            [
                'name' => 'Monthly Leave',
                'default_entitlement_days' => 1.0,
                'accrual_frequency' => 'Per Request',
                'is_paid' => true,
                'max_carry_over_days' => 0.0,
                'requires_attachment' => false,
            ]
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
