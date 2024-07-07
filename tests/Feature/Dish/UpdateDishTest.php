<?php

namespace Tests\Dish;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function() {
    $this->user = User::factory()->create();

    $this->dish = Dish::factory()
        ->for($this->user)
        ->create([
            'name' => 'Dish Name',
            'description' => 'Dish Description',
            'price' => 20,
        ]);
});

it('updates a dish successfully', function () {
    $this->actingAs($this->user);

    $data = [
        'name' => 'Updated Dish Name',
        'description' => 'Updated Description',
        'price' => 9.99,
    ];

    $response = $this->putJson(route('dishes.update', $this->dish->id), $data);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     'id',
                     'name',
                     'description',
                     'price',
                 ]
             ])
             ->assertJson([
                 'data' => [
                     'name' => 'Updated Dish Name',
                     'description' => 'Updated Description',
                     'price' => 9.99,
                 ]
             ]);
});

it('validates updating a dish with unique constraints', function() {
    $this->actingAs($this->user);

    Dish::factory()
        ->for($this->user)
        ->create([
            'name' => 'Another Dish Name',
            'description' => 'Another Dish Description',
        ]);

    $data = [
        'name' => 'Another Dish Name',
        'description' => 'It should render an error on update for the duplicate dish name',
    ];

    $response = $this->putJson(route('dishes.update', $this->dish->id), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('validates updating a dish with incorrect data', function() {
    $this->actingAs($this->user);

    $data = [
        'name' => str_repeat('a', 256),
        'description' => str_repeat('b', 256),
        'image_url' => 'incorrect url',
        'price' => -20,
    ];

    $response = $this->putJson(route('dishes.update', $this->dish->id), $data);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'description', 'image_url', 'price']);


});

it('ensures a user can update its own dish only', function() {
    $anotherUser = User::factory()->create();

    $this->actingAs($anotherUser);

    $data = [
        'name' => 'Updated Dish Name',
        'description' => 'Updated Dish Description',
    ];

    $response = $this->putJson(route('dishes.update', $this->dish->id), $data);

    $response->assertStatus(403);
});