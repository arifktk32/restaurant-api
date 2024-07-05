<?php

namespace Tests\Dish;

use App\Models\User;
use App\Models\Dish;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tests\Traits\AuthHelper;

uses(RefreshDatabase::class);
uses(AuthHelper::class);

beforeEach(function() {
    $this->user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $this->token = $this->getAccessToken($this->user);
});

test('an authenticated user can create a dish', function () {
    $dishData = [
        'name' => 'Test Dish',
        'description' => 'Test Description',
        'image_url' => 'https://via.placeholder.com/150',
        'price' => 25.50,
    ];

    $response = $this->actingAs($this->user)
        ->postJson(
            route('dishes.store'),
            $dishData,
            ['Authorization' => "Bearer $this->token"]
        );

    $response->assertStatus(201)
             ->assertJson([
                 'data' => [
                     'name' => $dishData['name'],
                     'description' => $dishData['description'],
                     'image_url' => $dishData['image_url'],
                     'price' => $dishData['price'],
                 ]
             ]);

    $this->assertDatabaseHas('dishes', [
        'user_id' => $this->user->id,
        'name' => $dishData['name'],
        'description' => $dishData['description'],
        'image_url' => $dishData['image_url'],
        'price' => $dishData['price'],
    ]);
});

test('a user can not create a dish with the same name', function () {
    Dish::factory()->create([
        'user_id' => $this->user->id,
        'name' => 'Test Dish',
    ]);

    $dishData = [
        'name' => 'Test Dish',
        'description' => 'Another Description',
        'image_url' => 'https://via.placeholder.com/150',
        'price' => 20.00,
    ];

    $response = $this->actingAs($this->user)
        ->postJson(
            route('dishes.store'),
            $dishData,
            ['Authorization' => "Bearer $this->token"]
        );

    $response->assertStatus(422)
             ->assertJsonValidationErrors('name');
});

test('a user can not create a dish with the same description', function () {
    Dish::factory()->create([
        'user_id' => $this->user->id,
        'description' => 'Test Description',
    ]);

    $dishData = [
        'name' => 'Another Dish',
        'description' => 'Test Description',
        'image_url' => 'https://via.placeholder.com/150',
        'price' => 20.00,
    ];

    $response = $this->actingAs($this->user)
        ->postJson(
            route('dishes.store'),
            $dishData,
            ['Authorization' => "Bearer $this->token"]
        );

    $response->assertStatus(422)
             ->assertJsonValidationErrors('description');
});

test('a user can not create a dish with invalid data', function () {
    $dishData = [
        'name' => '',
        'description' => '',
        'image_url' => 'invalid-url',
        'price' => -10,
    ];

    $response = $this->actingAs($this->user)
        ->postJson(
            route('dishes.store'),
            $dishData,
            ['Authorization' => "Bearer $this->token"]
        );

    $response->assertStatus(422)
             ->assertJsonValidationErrors([
                'name',
                'description',
                'image_url',
                'price']
            );
});

test('a guest can not create a dish', function () {
    // Log out authenticated user
    $this->postJson(
        '/api/auth/logout',
        [],
        ['Authorization' => "Bearer $this->token"]
    );

    $dishData = [
        'name' => 'Test Dish',
        'description' => 'Test Description',
        'image_url' => 'https://via.placeholder.com/150',
        'price' => 25.50,
    ];

    $response = $this->postJson(
        route('dishes.store'),
        $dishData
    );

    $response->assertStatus(401);
});
