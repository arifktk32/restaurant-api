<?php

namespace Tests\Dish;

use App\Models\Dish;
use App\Models\User;
use Pest\Plugins\Only;

it('a non authenticated user can not delete a dish', function () {
    $dish = Dish::factory()
        ->for(User::factory())
        ->create();

    $response = $this->deleteJson(route('dishes.destroy', $dish->id));

    $response->assertStatus(401);
});

it('an authenticated user can delete their own dishes', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);

    $dish = Dish::factory()
        ->for($user)
        ->create();

    $response = $this->deleteJson(route('dishes.destroy', $dish->id));

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'The dish was deleted successfully.'
        ]);

    $this->assertDatabaseMissing('dishes', ['id' => $dish->id]);
});

it('a user can not delete anothers user dish', function () {
    $user = User::factory()->create();
    
    $this->actingAs($user);

    $dish = Dish::factory()
        ->for(User::factory())
        ->create();

    $response = $this->deleteJson(route('dishes.destroy', $dish->id));

    $response->assertStatus(403);
});
