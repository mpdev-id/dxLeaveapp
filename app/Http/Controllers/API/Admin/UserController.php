<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return ResponseFormatter::success(UserResource::collection($users), 'Users retrieved successfully');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'employee_code' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'roles' => ['array'],
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(['errors' => $validator->errors()], 'Validation failed', 422);
        }

        $user = User::create([
            'name' => $request->name,
            'employee_code' => $request->employee_code,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        if ($request->has('roles')) {
            $user->assignRole($request->roles);
        }

        return ResponseFormatter::success(new UserResource($user), 'User created successfully');
    }

    public function show(User $user)
    {
        return ResponseFormatter::success(new UserResource($user->load('roles')), 'User retrieved successfully');
    }

    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['string', 'max:255'],
            'employee_code' => ['string', 'max:255', 'unique:users,employee_code,' . $user->id],
            'email' => ['string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'string', 'min:8'],
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
    }

    public function destroy(User $user)
    {
        $user->delete();
        return ResponseFormatter::success(null, 'User deleted successfully');
    }
}
