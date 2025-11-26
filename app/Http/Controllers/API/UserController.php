<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EntitlementService;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    protected $entitlementService;
    protected $whatsappService;

    public function __construct(EntitlementService $entitlementService, WhatsAppService $whatsappService)
    {
        $this->entitlementService = $entitlementService;
        $this->whatsappService = $whatsappService;
    }

    public function forgotPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $validator->errors(),
                ], 'Validation Failed', 422);
            }

            $identifier = $request->input('identifier');
            $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_code';

            $user = User::where($fieldType, $identifier)->first();

            if (!$user) {
                return ResponseFormatter::error([
                    'message' => 'User not found',
                ], 'User Not Found', 404);
            }

            if (!$user->phone_number) {
                return ResponseFormatter::error([
                    'message' => 'No phone number registered for this account',
                ], 'Phone Number Required', 400);
            }

            // Generate 6-digit OTP
            $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            
            // Store OTP in database with expiration (10 minutes)
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $user->email],
                [
                    'token' => Hash::make($otp),
                    'created_at' => now()
                ]
            );

            // Send OTP via WhatsApp
            $sent = $this->whatsappService->sendOTP($user->phone_number, $otp, $user->name);

            if (!$sent) {
                \Log::warning('Failed to send OTP via WhatsApp, but OTP was generated', [
                    'user_id' => $user->id,
                    'phone' => $user->phone_number
                ]);
            }

            return ResponseFormatter::success([
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'message' => 'OTP has been sent to your WhatsApp number',
            ], 'OTP sent successfully');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Failed to generate OTP', 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
                'otp' => 'required|string|size:6',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'errors' => $validator->errors(),
                ], 'Validation failed', 422);
            }

            // Check if OTP exists and is valid
            $passwordReset = DB::table('password_reset_tokens')
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset) {
                return ResponseFormatter::error([
                    'message' => 'No password reset request found for this email',
                ], 'Invalid Request', 404);
            }

            // Check if OTP is correct
            if (!Hash::check($request->otp, $passwordReset->token)) {
                return ResponseFormatter::error([
                    'message' => 'Invalid OTP code',
                ], 'Invalid OTP', 400);
            }

            // Check if OTP is expired (10 minutes)
            if (Carbon::parse($passwordReset->created_at)->addMinutes(10)->isPast()) {
                DB::table('password_reset_tokens')->where('email', $request->email)->delete();
                return ResponseFormatter::error([
                    'message' => 'OTP has expired. Please request a new one.',
                ], 'OTP Expired', 400);
            }

            // Update password
            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            // Delete the used OTP
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            return ResponseFormatter::success([
                'message' => 'Password has been reset successfully',
            ], 'Password Reset Successful');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Failed to reset password', 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone_number' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'manager_id' => ['nullable', 'exists:users,id'],
            'status' => ['nullable', 'string', 'max:255'],
            'hire_date' => ['nullable', 'date'],
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

                // 2. Assign 'Employee' role
                $employeeRole = Role::where('name', 'Employee')->first();
                if ($employeeRole) {
                    $newUser->assignRole($employeeRole);
                }

                // 3. Automatically create annual leave entitlement for the new user
                // Assuming '1' is the ID for 'Cuti Tahunan' (Annual Leave) and '2' is the ID for 'Cuti Bulanan' (Monthly Leave)
                $currentYear = Carbon::now()->year;
                $leaveTypeId = $currentYear === Carbon::parse($request->hire_date)->year ? 6 : 1;
                $this->entitlementService->createEntitlement([
                    'user_id' => $newUser->id,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $currentYear,
                    'initial_balance' => 12, // Default 12 days
                    'days_taken' => 0,
                    'carry_over_days' => 0,
                ]);

                return $newUser;
            });

            // Create token for the new user
            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'User Registered & Entitlement Created Successfully');

        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong during registration.',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
                'password' => 'required',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $validator->errors(),
                ], 'Authentication Failed', 500);
            }

            $identifier = $request->input('identifier');
            $password = $request->input('password');

            $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_code';

            if (!Auth::attempt([$fieldType => $identifier, 'password' => $password])) {
                return ResponseFormatter::error([
                    'message' => 'Unauthorized'
                ], 'Authentication Failed', 500);
            }

            $user = User::where($fieldType, $identifier)->first();

            if (!Hash::check($password, $user->password, [])) {
                throw new \Exception('Invalid Credentials');
            }

            $tokenResult = $user->createToken('authToken')->plainTextToken;

            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => new UserResource($user),
            ], 'Authenticated');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->user()->currentAccessToken()->delete();
            return ResponseFormatter::success($token, 'Token Revoked');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to logout: ' . $e->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            return ResponseFormatter::success(new UserResource($request->user()), 'Data profile user berhasil diambil');
        } catch (\Exception $e) {
            return ResponseFormatter::error(null, 'Failed to fetch user: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get the authenticated user's leave balances for the current year.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
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

    /**
     * Update user's phone number and send WhatsApp notification
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePhoneNumber(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'phone_number' => 'required|string|min:10|max:15|regex:/^[0-9]+$/',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    ['errors' => $validator->errors()],
                    'Validation failed',
                    422
                );
            }

            $user = $request->user();
            $oldPhone = $user->phone_number;
            $newPhone = $request->phone_number;

            // Ensure phone starts with country code (62 for Indonesia)
            if (!str_starts_with($newPhone, '62')) {
                // Remove leading 0 if exists and add 62
                $newPhone = '62' . ltrim($newPhone, '0');
            }

            // Update phone number
            $user->phone_number = $newPhone;
            $user->save();

            // Send WhatsApp notification
            $sent = $this->whatsappService->sendPhoneChangeNotification($newPhone, $user->name, $oldPhone);

            if (!$sent) {
                \Log::warning('Failed to send WhatsApp notification for phone change', [
                    'user_id' => $user->id,
                    'new_phone' => $newPhone
                ]);
            }

            return ResponseFormatter::success(
                new UserResource($user),
                'Phone number updated successfully'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to update phone number: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Change password for authenticated user
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error(
                    ['errors' => $validator->errors()],
                    'Validation failed',
                    422
                );
            }

            $user = $request->user();

            // Verify current password
            if (!Hash::check($request->current_password, $user->password)) {
                return ResponseFormatter::error(
                    ['message' => 'Current password is incorrect'],
                    'Invalid Password',
                    400
                );
            }

            // Update password
            $user->password = Hash::make($request->new_password);
            $user->save();

            return ResponseFormatter::success(
                ['message' => 'Password changed successfully'],
                'Password Changed'
            );
        } catch (\Exception $e) {
            return ResponseFormatter::error(
                null,
                'Failed to change password: ' . $e->getMessage(),
                500
            );
        }
    }
}
