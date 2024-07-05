<?php

namespace Tests\Traits;

use App\Models\User;

trait AuthHelper
{
    public function getAccessToken(User $user)
    {
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        return $loginResponse->json('access_token');
    }
}
