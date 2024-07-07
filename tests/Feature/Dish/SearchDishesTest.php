<?php

namespace Tests\Dish;

use App\Models\Dish;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function() {
    $this->user = User::factory()->create();
    
    Dish::factory()->for($this->user)->create(['name' => 'Shinwari Tikka', 'description' => 'A yummy dish']);
    Dish::factory()->for($this->user)->create(['name' => 'Beef Pulao', 'description' => 'Bannu special dish']);
    Dish::factory()->for($this->user)->create(['name' => 'Biryani', 'description' => 'A spicy rice dish']);
    Dish::factory()->for($this->user)->create(['name' => 'Chicken Pulao', 'description' => 'Savour Food']);
});

it('can filter dishes by name', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('dishes.index', ['name' => 'ikk']));

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id',
                         'name',
                         'description',
                         'price',
                     ]
                 ],
                 'links',
                 'meta'
             ])
             ->assertJsonCount(1, 'data')
             ->assertJsonFragment(['name' => 'Shinwari Tikka']);
});

it('can filter dishes by description', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('dishes.index', ['description' => 'spicy']));

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id',
                         'name',
                         'description',
                         'price',
                     ]
                 ],
                 'links',
                 'meta'
             ])
             ->assertJsonCount(1, 'data')
             ->assertJsonFragment(['description' => 'A spicy rice dish']);
});

it('can filter dishes by both name and description', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('dishes.index', ['name' => 'Pulao', 'description' => 'Savour']));

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id',
                         'name',
                         'description',
                         'price',
                     ]
                 ],
                 'links',
                 'meta'
             ])
             ->assertJsonCount(1, 'data')
             ->assertJsonFragment(['name' => 'Chicken Pulao', 'description' => 'Savour Food']);
});

it('returns paginated results with correct structure', function () {
    $this->actingAs($this->user);

    Dish::factory()->for($this->user)->count(20)->create();

    $response = $this->getJson(route('dishes.index'));

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'data' => [
                     '*' => [
                         'id',
                         'name',
                         'description',
                         'price',
                     ]
                 ],
                 'links',
                 'meta'
             ])
             ->assertJsonPath('meta.total', 24) // 4 dishes created in beforeEach()
             ->assertJsonPath('meta.per_page', 15);
});

it('validates the search parameters', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('dishes.index', ['name' => str_repeat('a', 256)]));

    $response->assertStatus(422)
             ->assertJsonValidationErrors(['name']);
});

it('can limit the number of dishes returned', function () {
    $this->actingAs($this->user);

    $response = $this->getJson(route('dishes.index', ['limit' => 2]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'description',
                    'image_url',
                    'price',
                ]
            ],
            'links',
            'meta'
        ])
        ->assertJsonCount(2, 'data');
});

it('can apply offset to the number of dishes returned', function () {
    $this->actingAs($this->user);

    Dish::factory()->count(20)->create();

    $response = $this->getJson(route('dishes.index', ['limit' => 5, 'offset' => 2]));

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'name',
                    'description',
                    'image_url',
                    'price',
                ]
            ],
            'links',
            'meta'
        ])
        ->assertJsonCount(5, 'data')
        ->assertJsonPath('meta.total', 24)
        ->assertJsonFragment(['id' => 6])
        ->assertJsonFragment(['id' => 10])
        ->assertJsonMissingExact(['id' => 11]);
});