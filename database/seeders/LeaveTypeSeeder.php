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
                'accrual_frequency' => 'Yearly',
                'is_paid' => true,
                'max_carry_over_days' => 6.0,
                'requires_attachment' => false,
            ],
            [
                'name' => 'Sick Leave',
                'default_entitlement_days' => 6.0,
                'accrual_frequency' => 'Yearly',
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
        ];

        foreach ($leaveTypes as $type) {
            LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
