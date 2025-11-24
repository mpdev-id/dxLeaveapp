<?php

namespace App\Observers;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\EmployeeEntitlement;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // If user is created with hire_date, initialize annual leave
        if ($user->hire_date) {
            $this->initializeAnnualLeave($user);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If hire_date was just set (changed from null to a date)
        if ($user->isDirty('hire_date') && $user->hire_date && !$user->getOriginal('hire_date')) {
            $this->initializeAnnualLeave($user);
        }
    }

    /**
     * Initialize annual leave entitlement for newly hired user
     */
    private function initializeAnnualLeave(User $user): void
    {
        try {
            // Get Annual Leave type
            $annualLeaveType = LeaveType::where('name', 'LIKE', '%Annual%')
                ->orWhere('name', 'LIKE', '%Tahunan%')
                ->first();
            
            if (!$annualLeaveType) {
                Log::warning("Annual leave type not found when initializing for user {$user->id}");
                return;
            }
            
            // Check if entitlement already exists
            $existingEntitlement = EmployeeEntitlement::where('user_id', $user->id)
                ->where('leave_type_id', $annualLeaveType->id)
                ->first();
            
            if ($existingEntitlement) {
                Log::info("Annual leave entitlement already exists for user {$user->id}");
                return;
            }
            
            // Calculate which year this entitlement belongs to
            $hireDate = Carbon::parse($user->hire_date);
            $currentYear = Carbon::now()->year;
            
            // If hired in current year, use current year
            // If hired in previous year, they should already have entitlement from reset
            $entitlementYear = $hireDate->year >= $currentYear ? $currentYear : $hireDate->year;
            
            // Create initial entitlement
            EmployeeEntitlement::create([
                'user_id' => $user->id,
                'leave_type_id' => $annualLeaveType->id,
                'initial_balance' => $annualLeaveType->default_entitlement_days,
                'remaining_balance' => $annualLeaveType->default_entitlement_days,
                'used_balance' => 0,
                'year' => $entitlementYear,
            ]);
            
            Log::info("Initialized annual leave for user {$user->id} ({$user->name}). Hire date: {$hireDate->format('Y-m-d')}, Balance: {$annualLeaveType->default_entitlement_days} days, Year: {$entitlementYear}");
            
        } catch (\Exception $e) {
            Log::error("Failed to initialize annual leave for user {$user->id}: {$e->getMessage()}");
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
