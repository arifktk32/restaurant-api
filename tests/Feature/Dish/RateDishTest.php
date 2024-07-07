<?php

namespace Tests\Dish;

use App\Models\User;
use App\Models\Dish;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->dish = Dish::factory()->create();
});

test('an authenticated user can rate a dish', function () {
    $this->actingAs($this->user);

    $response = $this->postJson(
        route('dishes.rate', $this->dish), [
            'rating' => 4
        ]);

    $response->assertStatus(201)
        ->assertJson([
            'message' => 'Rating submitted successfully.',
        ]);

    $this->assertDatabaseHas('ratings', [
        'user_id' => $this->user->id,
        'dish_id' => $this->dish->id,
        'rating' => 4,
    ]);
});

test('a guest can not rate a dish', function () {
    $response = $this->postJson(
        route('dishes.rate', $this->dish), [
            'rating' => 3
        ]);

    $response->assertStatus(401);
});

test('a user can not rate a dish more than once', function () {
    $this->actingAs($this->user);

    $this->user->ratings()->create([
        'dish_id' => $this->dish->id,
        'rating' => 5,
    ]);

    $response = $this->postJson(route('dishes.rate', $this->dish), [
            'rating' => 1
        ]);

    $response->assertStatus(403)
             ->assertJson([
                 'message' => 'You have already rated this dish.',
             ]);
});
