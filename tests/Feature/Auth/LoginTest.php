<?php

namespace Tests\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);
});

it('can log in with valid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => $this->user->email,
        'password' => 'password',
    ]);

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'access_token',
        'token_type',
        'expires_in',
    ]);
});

it('cannot log in with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => $this->user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401);
    $response->assertJson(['error' => 'Unauthorized']);
});

it('validates email and password fields', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => '',
        'password' => '',
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email', 'password']);
});
