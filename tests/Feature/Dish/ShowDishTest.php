<?php

namespace Tests\Dish;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Traits\AuthHelper;

uses(RefreshDatabase::class);
uses(AuthHelper::class);

beforeEach(function() {
    $this->user = User::factory()
        ->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

    $this->token = $this->getAccessToken($this->user);
});

test('an authenticated user can view details of a specific dish', function () {
    $this->actingAs($this->user);

    $dish = Dish::factory()
        ->for($this->user)
        ->create();

    $response = $this->getJson(
        route('dishes.show', $dish->id),
        ['Authorization' => "Bearer $this->token"]
    );

    $response->assertStatus(200)
             ->assertJson([
                 'data' => [
                     'id' => $dish->id,
                     'name' => $dish->name,
                     'description' => $dish->description,
                     'image_url' => $dish->image_url,
                     'price' => $dish->price,
                 ]
             ]);
});

test('retrieving a non existent dish returns 404 not found', function () {
    $this->actingAs($this->user);

    $nonExistentDishId = 999;
    $response = $this->getJson(
        route('dishes.show', $nonExistentDishId),
        ['Authorization' => "Bearer $this->token"]
    );

    $response->assertStatus(404)
             ->assertJson([
                 'error' => 'Dish not found.'
             ]);
});

test('a guest can not view a dish', function () {
    // Log out authenticated user
    $this->postJson(
        '/api/auth/logout',
        [],
        ['Authorization' => "Bearer $this->token"]
    );

    $dish = Dish::factory()
        ->for($this->user)
        ->create();

    $response = $this->getJson(route('dishes.show', $dish->id));

    $response->assertStatus(401);
});
