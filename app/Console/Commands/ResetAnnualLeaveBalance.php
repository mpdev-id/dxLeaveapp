<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\EmployeeEntitlement;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ResetAnnualLeaveBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:reset-annual-balance';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset annual leave balance for employees based on their hire date anniversary (15 days before)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting annual leave balance reset...');
        
        $today = Carbon::today();
        $resetCount = 0;
        
        // Get Annual Leave type
        $annualLeaveType = LeaveType::where('name', 'LIKE', '%Annual%')
            ->orWhere('name', 'LIKE', '%Tahunan%')
            ->first();
        
        if (!$annualLeaveType) {
            $this->error('Annual leave type not found!');
            Log::error('Annual leave type not found for reset command');
            return 1;
        }
        
        // Get all active users with hire_date
        $users = User::whereNotNull('hire_date')
            ->where('status', 'active')
            ->get();
        
        $this->info("Found {$users->count()} active users with hire date");
        
        foreach ($users as $user) {
            try {
                // Calculate the anniversary date for this year
                $hireDate = Carbon::parse($user->hire_date);
                $currentYear = $today->year;
                
                // Get anniversary date for current year
                $anniversaryThisYear = Carbon::create($currentYear, $hireDate->month, $hireDate->day);
                
                // If anniversary has passed this year, check next year's anniversary
                if ($anniversaryThisYear->lt($today)) {
                    $anniversaryThisYear = Carbon::create($currentYear + 1, $hireDate->month, $hireDate->day);
                }
                
                // Calculate reset date (15 days before anniversary)
                $resetDate = $anniversaryThisYear->copy()->subDays(15);
                
                // Check if today is the reset date
                if ($today->isSameDay($resetDate)) {
                    $this->resetUserAnnualLeave($user, $annualLeaveType);
                    $resetCount++;
                    $this->info("✓ Reset annual leave for: {$user->name} (Hire Date: {$hireDate->format('d M Y')})");
                }
                
            } catch (\Exception $e) {
                $this->error("✗ Failed to reset for user {$user->name}: {$e->getMessage()}");
                Log::error("Failed to reset annual leave for user {$user->id}: {$e->getMessage()}");
            }
        }
        
        $this->info("Reset completed! Total users reset: {$resetCount}");
        Log::info("Annual leave reset completed. Total users reset: {$resetCount}");
        
        return 0;
    }
    
    /**
     * Reset annual leave balance for a specific user
     */
    private function resetUserAnnualLeave(User $user, LeaveType $annualLeaveType)
    {
        DB::transaction(function () use ($user, $annualLeaveType) {
            // Find or create employee entitlement for annual leave
            $entitlement = EmployeeEntitlement::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'leave_type_id' => $annualLeaveType->id,
                ],
                [
                    'initial_balance' => $annualLeaveType->default_entitlement_days,
                    'remaining_balance' => $annualLeaveType->default_entitlement_days,
                    'used_balance' => 0,
                    'year' => Carbon::now()->year,
                ]
            );
            
            // Handle carry over if applicable
            $carryOver = 0;
            if ($annualLeaveType->max_carry_over_days > 0 && $entitlement->remaining_balance > 0) {
                $carryOver = min($entitlement->remaining_balance, $annualLeaveType->max_carry_over_days);
            }
            
            // Reset the balance
            $entitlement->update([
                'initial_balance' => $annualLeaveType->default_entitlement_days + $carryOver,
                'remaining_balance' => $annualLeaveType->default_entitlement_days + $carryOver,
                'used_balance' => 0,
                'year' => Carbon::now()->year,
            ]);
            
            Log::info("Reset annual leave for user {$user->id}. New balance: {$entitlement->remaining_balance} (Carry over: {$carryOver})");
        });
    }
}
