<?php

namespace Tests\Dish;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('allows dishes resource up to the rate limit only', function () {
    RateLimiter::clear('dishes'); // Clear rate limiter state

    $this->actingAs($this->user);

    // Simulate hitting the rate limit
    for ($i = 1; $i < 61; $i++) {
        $response = $this->getJson(route('dishes.index'));

        $response->assertStatus(200);
    }

    // The 61st request should be rate limited
    $response = $this->getJson(route('dishes.index'));

    $response->assertStatus(429) // Too many attempts
        ->assertJson([
            'message' => 'Too Many Attempts.',
        ]);
});

it('resets the rate limit for dishes resource after the time window', function () {
    RateLimiter::clear('dishes'); // Clear rate limiter state

    $this->actingAs($this->user);

    // Simulate hitting the rate limit
    for ($i = 1; $i < 61; $i++) {
        $response = $this->getJson(route('dishes.index'));

        $response->assertStatus(200);
    }

    // The 61st request should be rate limited
    $response = $this->getJson(route('dishes.index'));

    $response->assertStatus(429) // Too many attempts
        ->assertJson([
            'message' => 'Too Many Attempts.',
        ]);

    // Simulate waiting for the rate limit window to reset
    $this->travel(1)->minute();

    // The next request should be allowed again
    $this->getJson(route('dishes.index'))
        ->assertStatus(200);
});
