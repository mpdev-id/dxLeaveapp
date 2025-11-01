<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);



it('allows a user to register', function () {
    $department = Department::create(['name' => 'Test Department']);
    $manager = User::create([
        'name' => 'Test Manager',
        'employee_code' => 'EMP' . Str::random(5),
        'email' => 'manager@example.com',
        'password' => 'password',
        'department_id' => $department->id,
        'hire_date' => '2023-01-01',
    ]);

    $this->withoutExceptionHandling();

    $response = $this->postJson('/api/register', [
        'name' => 'Test User',
        'employee_code' => 'EMP' . Str::random(5),
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'department_id' => $department->id,
        'manager_id' => $manager->id,
        'status' => 'active',
        'hire_date' => '2023-01-01',
    ]);

    dd($response->getContent());

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'user' => [
                    'id',
                    'name',
                    'employee_code',
                    'email',
                    'department_id',
                    'manager_id',
                    'status',
                    'hire_date',
                    'created_at',
                    'updated_at',
                ],
            ],
        ]);

    $this->assertDatabaseHas('users', [
        'email' => 'test@example.com',
        'employee_code' => $response['data']['user']['employee_code'],
        'department_id' => $department->id,
        'manager_id' => $manager->id,
        'status' => 'active',
        'hire_date' => '2023-01-01',
    ]);
});

it('allows a user to login with email', function () {
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'identifier' => 'test@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'user',
            ],
        ]);
});

it('allows a user to login with employee code', function () {
    $user = User::factory()->create([
        'employee_code' => 'EMP002',
        'password' => Hash::make('password'),
    ]);

    $response = $this->postJson('/api/login', [
        'identifier' => $user->employee_code,
        'password' => 'password',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'access_token',
                'token_type',
                'user',
            ],
        ]);
});

it('allows a logged-in user to fetch their data', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')->getJson('/api/user');

    $response->assertStatus(200)
        ->assertJson([
            'data' => [
                'id' => $user->id,
                'email' => $user->email,
            ],
        ]);
});

it('allows a logged-in user to logout', function () {
    $user = User::factory()->create();
    $token = $user->createToken('test_token');

    $response = $this->postJson('/api/logout', [], ['Authorization' => 'Bearer ' . $token->plainTextToken]);

    $response->assertStatus(200)
        ->assertJson([
            'data' => true, 
        ]);

    $this->assertDatabaseMissing('personal_access_tokens', [
        'token' => hash('sha256', $token->plainTextToken),
    ]);
});

it('allows a user to request a password reset link with email', function () {
    $user = User::factory()->create();

    $response = $this->postJson('/api/forgot-password', [
        'identifier' => $user->email,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'message',
                'token',
            ],
        ]);

    $this->assertDatabaseHas(config('auth.passwords.users.table'), [
        'email' => $user->email,
    ]);
});

it('allows a user to request a password reset link with employee code', function () {
    $user = User::factory()->create();

    $this->withoutExceptionHandling();

    $response = $this->postJson('/api/forgot-password', [
        'identifier' => $user->employee_code,
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                'message',
                'token',
            ],
        ]);

    $this->assertDatabaseHas(config('auth.passwords.users.table'), [
        'email' => $user->email,
    ]);
});
