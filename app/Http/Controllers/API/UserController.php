<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\EntitlementService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    protected $entitlementService;

    public function __construct(EntitlementService $entitlementService)
    {
        $this->entitlementService = $entitlementService;
    }

    public function forgotPassword(Request $request)
    {
        try {
            error_log('Forgot password request data: ' . json_encode($request->all()));
            $validator = Validator::make($request->all(), [
                'identifier' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $validator->errors(),
                ], 'Authentication Failed', 500);
            }

            $identifier = $request->input('identifier');
            $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'employee_code';

            $user = User::where($fieldType, $identifier)->first();
            error_log('User found: ' . json_encode($user));

            if (!$user) {
                return ResponseFormatter::error([
                    'message' => 'User not found',
                ], 'Authentication Failed', 404);
            }

            error_log('Before token creation');
            $token = Str::random(60);
            DB::table(config('auth.passwords.users.table'))->updateOrInsert(
                ['email' => $user->email],
                ['token' => Hash::make($token), 'created_at' => now()]
            );

            // TODO: Send email to user with token
            // Mail::to($user->email)->send(new PasswordResetMail($token));

            return ResponseFormatter::success([
                'message' => 'Password reset link sent to your email',
                'token' => $token,
            ], 'Password Reset');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function resetPassword(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required|string|min:8|confirmed',
                'token' => 'required|string',
            ]);

            if ($validator->fails()) {
                return ResponseFormatter::error([
                    'message' => 'Something went wrong',
                    'error' => $validator->errors(),
                ], 'Authentication Failed', 500);
            }

            $passwordReset = DB::table(config('auth.passwords.users.table'))
                ->where('email', $request->email)
                ->first();

            if (!$passwordReset || !Hash::check($request->token, $passwordReset->token)) {
                return ResponseFormatter::error([
                    'message' => 'Invalid token',
                ], 'Authentication Failed', 400);
            }

            $user = User::where('email', $request->email)->first();
            $user->password = Hash::make($request->password);
            $user->save();

            DB::table(config('auth.passwords.users.table'))->where('email', $request->email)->delete();

            return ResponseFormatter::success([
                'message' => 'Password has been reset',
            ], 'Password Reset');
        } catch (\Exception $e) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 'Authentication Failed', 500);
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
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
                    'password' => Hash::make($request->password),
                    'department_id' => $request->department_id,
                    'manager_id' => $request->manager_id,
                    'status' => $request->status,
                    'hire_date' => $request->hire_date,
                ]);

                // 2. Automatically create annual leave entitlement for the new user
                // Assuming '1' is the ID for 'Cuti Tahunan' (Annual Leave)
                $this->entitlementService->createEntitlement([
                    'user_id' => $newUser->id,
                    'leave_type_id' => 1, // Default to Annual Leave
                    'year' => Carbon::now()->year,
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
                'user' => $user,
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
        $token = $request->user()->currentAccessToken()->delete();
        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(new UserResource($request->user()), 'Data profile user berhasil diambil');
    }
}
