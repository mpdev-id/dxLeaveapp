<?php

namespace App\Services;

use App\Models\EmployeeEntitlement;
use App\Models\LeaveRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class EntitlementService
{
    public function getEntitlements(Request $request)
    {
        $query = EmployeeEntitlement::with(['user', 'leaveType'])
                    ->select('employee_entitlements.*');

        // Filter by user if user_id is provided
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($subq) use ($search) {
                    $subq->where('name', 'like', '%' . $search . '%');
                })->orWhereHas('leaveType', function ($subq) use ($search) {
                    $subq->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        // Sorting functionality
        if ($request->filled('sort_by')) {
            $sortBy = $request->input('sort_by');
            $sortDir = $request->input('sort_dir', 'asc');

            $allowedSorts = ['year', 'entitlement', 'user_name', 'leave_type_name'];

            if (in_array($sortBy, $allowedSorts)) {
                if ($sortBy === 'user_name') {
                    $query->join('users', 'employee_entitlements.user_id', '=', 'users.id')
                          ->orderBy('users.name', $sortDir);
                } elseif ($sortBy === 'leave_type_name') {
                    $query->join('leave_types', 'employee_entitlements.leave_type_id', '=', 'leave_types.id')
                          ->orderBy('leave_types.name', $sortDir);
                } else {
                    $query->orderBy($sortBy, $sortDir);
                }
            }
        }

        if ($request->input('all') === 'true') {
            $entitlements = $query->get();
        } else {
            $entitlements = $query->paginate($request->input('per_page', 10));
        }

        return $entitlements;
    }

    public function findEntitlementById($id)
    {
        return EmployeeEntitlement::find($id);
    }

    public function createEntitlement(array $data)
    {
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2000',
            'initial_balance' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return EmployeeEntitlement::create($data);
    }

    public function updateEntitlement(EmployeeEntitlement $entitlement, array $data)
    {
        \Illuminate\Support\Facades\Log::info('Updating entitlement in service', [
            'data' => $data,
            'entitlement' => $entitlement
        ]);
        $validator = Validator::make($data, [
            'user_id' => 'exists:users,id',
            'leave_type_id' => 'exists:leave_types,id',
            'year' => 'integer|min:2000',
            'initial_balance' => 'numeric|min:0',
            'days_taken' => 'numeric|min:0',
            'carry_over_days' => 'numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $result = $entitlement->update($data);
        \Illuminate\Support\Facades\Log::info('Entitlement update result', [
            'result' => $result,
            'entitlement' => $entitlement
        ]);
        return $entitlement;
    }

    public function deleteEntitlement(EmployeeEntitlement $entitlement)
    {
        $entitlement->delete();
    }

    public function hasSufficientBalance(User $user, int $leaveTypeId, int $daysNeeded): bool
    {
        $year = Carbon::now()->year;

        $entitlement = EmployeeEntitlement::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$entitlement) {
            return false; // No entitlement found for this leave type and year
        }

        $remainingBalance = $entitlement->initial_balance + $entitlement->carry_over_days - $entitlement->days_taken;

        return $remainingBalance >= $daysNeeded;
    }

    /**
     * Mengurangi saldo jatah cuti setelah permintaan disetujui penuh (FINAL APPROVAL).
     *
     * @param LeaveRequest $leaveRequest Permintaan cuti yang telah disetujui.
     * @return void
     */
    public function deductLeaveBalance(LeaveRequest $leaveRequest): void
    {
        $year = Carbon::parse($leaveRequest->start_date)->year;

        $entitlement = EmployeeEntitlement::where('user_id', $leaveRequest->user_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', $year)
            ->first();

        if ($entitlement) {
            // Gunakan increment untuk operasi atomik & menghindari race condition
            $entitlement->increment('days_taken', $leaveRequest->duration_days);
        } else {
            // Log jika jatah cuti untuk tahun tersebut tidak ditemukan, ini seharusnya tidak terjadi dalam alur normal
            Log::warning("Entitlement record not found for user {$leaveRequest->user_id} for leave type {$leaveRequest->leave_type_id} in year {$year}. Could not deduct leave days.");
        }
    }

    /**
     * Retrieves all leave entitlements and calculated remaining balances for a specific user and year.
     *
     * @param User $user The user to retrieve leave balances for.
     * @param int $year The year for which to retrieve balances.
     * @return array An array of associative arrays, each containing leave type details and balance information.
     */
    public function getUserLeaveBalances(User $user, int $year): array
    {
        $entitlements = EmployeeEntitlement::with('leaveType')
            ->where('user_id', $user->id)
            ->where('year', $year)
            ->get();

        $balances = [];
        foreach ($entitlements as $entitlement) {
            $remaining = $entitlement->initial_balance + $entitlement->carry_over_days - $entitlement->days_taken;
            $balances[] = [
                'leave_type_id' => $entitlement->leaveType->id,
                'leave_type_name' => $entitlement->leaveType->name,
                'remaining_days' => $remaining,
                'total_entitlement' => $entitlement->initial_balance + $entitlement->carry_over_days,
                'days_taken' => $entitlement->days_taken,
                'year' => $entitlement->year
            ];
        }
        return $balances;
    }

    /**
     * Calculates the remaining leave balance for a specific leave type for a user in a given year.
     *
     * @param User $user The user.
     * @param int $leaveTypeId The ID of the leave type.
     * @param int $year The year.
     * @return float The remaining balance, or 0.0 if no entitlement is found.
     */
    public function getRemainingBalanceForLeaveType(User $user, int $leaveTypeId, int $year): float
    {
        $entitlement = EmployeeEntitlement::where('user_id', $user->id)
            ->where('leave_type_id', $leaveTypeId)
            ->where('year', $year)
            ->first();

        if (!$entitlement) {
            return 0.0;
        }

        return (float) ($entitlement->initial_balance + $entitlement->carry_over_days - $entitlement->days_taken);
    }
}
