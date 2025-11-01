<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\PublicHoliday;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $holidays = [
            [
                'name' => 'New Year\'s Day',
                'date' => '2025-01-01',
            ],
            [
                'name' => 'Christmas Day',
                'date' => '2025-12-25',
            ],
        ];

        foreach ($holidays as $holiday) {
            PublicHoliday::firstOrCreate($holiday);
        }
    }
}
