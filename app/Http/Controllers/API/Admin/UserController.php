<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;
use App\Services\EntitlementService;
class UserController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    public function index(Request $request)
    {
        try {
            $query = User::with('roles')
                ->when($request->filled('search'), function ($query) use ($request) {
                    $search = $request->input('search');
                    return $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', '%' . $search . '%')
                          ->orWhere('email', 'like', '%' . $search . '%');
                    });
                })
                ->when($request->filled('sort_by'), function ($query) use ($request) {
                    $sortBy = $request->input('sort_by');
                    $sortDir = $request->input('sort_dir', 'asc');

                    // Whitelist columns to prevent arbitrary sorting
                    $allowedSorts = ['name', 'email', 'created_at'];
                    if (in_array($sortBy, $allowedSorts)) {
                        return $query->orderBy($sortBy, $sortDir);
                    }
                });

            if ($request->input('all') === 'true') {
                $users = $query->get();
            } else {
                $users = $query->paginate($request->input('per_page', 10));
            }

            return ResponseFormatter::success($users, 'Users retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve users: ' . $e->getMessage(), 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['nullable', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
            'roles' => ['array'],
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        try {
            $user = DB::transaction(function () use ($request) {
                // 1. Create the user
                $newUser = User::create([
                    'name' => $request->name,
                    'employee_code' => $request->employee_code,
                    'email' => $request->email,
                    'phone_number' => $request->phone_number,
                    'password' => Hash::make($request->password),
                    'department_id' => $request->department_id,
                    'manager_id' => $request->manager_id,
                    'status' => $request->status,
                    'hire_date' => $request->hire_date,
                ]);

                if ($request->has('roles')) {
                    $newUser->syncRoles($request->roles);
                }

                
                // 3. Automatically create annual leave entitlement for the new user
                // Assuming '1' is the ID for 'Cuti Tahunan' (Annual Leave) and '2' is the ID for 'Cuti Bulanan' (Monthly Leave)
                $currentYear = Carbon::now()->year;
                $leaveTypeId = $currentYear === Carbon::parse($request->hire_date)->year ? 6 : 1;
                $this->entitlementService->createEntitlement([
                    'user_id' => $newUser->id,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $request->hire_date ? Carbon::parse($request->hire_date)->year : null,
                    'initial_balance' => 12, // Default 12 days
                    'days_taken' => 0,
                    'carry_over_days' => 0,
                ]);

                return $newUser;
            });

            return ResponseFormatter::success(new UserResource($user), 'User created successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong during user creation.',
                'error' => $e->getMessage(),
            ], 'User Creation Failed', 500);
        }
    }

    public function show(User $user)
    {
        try {
            return ResponseFormatter::success(new UserResource($user->load('roles')), 'User retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve user: ' . $e->getMessage(), 500);
        }
    }

    public function update(Request $request, User $user)
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => ['string', 'max:255'],
                'employee_code' => ['string', 'max:255', 'unique:users,employee_code,' . $user->id],
                'email' => ['string', 'email', 'max:255', 'unique:users,email,' . $user->id],
                'phone_number' => ['nullable', 'string', 'max:255'],
                'password' => ['nullable', 'string', 'min:8'],
                'department_id' => ['nullable', 'exists:departments,id'],
                'manager_id' => ['nullable', 'exists:users,id'],
                'status' => ['nullable', 'string', 'max:255'],
                'hire_date' => ['nullable', 'date'],
                'roles' => ['array'],
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
            }

            $userData = $request->except('password');
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($request->has('roles')) {
                $user->syncRoles($request->roles);
            }

            return ResponseFormatter::success(new UserResource($user), 'User updated successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to update user: ' . $e->getMessage(), 500);
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return ResponseFormatter::success(null, 'User deleted successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to delete user: ' . $e->getMessage(), 500);
        }
    }

    public function getStatus(User $user)
    {
        try {
            return ResponseFormatter::success($user->status, 'User status retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve user status: ' . $e->getMessage(), 500);
        }
    }
  
    public function getLeaveBalances(Request $request)
    {
        try {
            $user = $request->user();
            $currentYear = Carbon::now()->year;
            $balances = $this->entitlementService->getUserLeaveBalances($user, $currentYear);

            return ResponseFormatter::success($balances, 'User leave balances retrieved successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to retrieve leave balances: ' . $e->getMessage(), 500);
        }
    }
}
