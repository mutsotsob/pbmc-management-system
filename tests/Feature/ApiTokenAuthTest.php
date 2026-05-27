<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_issue_sanctum_token_with_valid_credentials(): void
    {
        $password = 'secret-password';
        $user = User::factory()->create([
            'email' => 'lab@example.com',
            'password' => $password,
            'department' => 'Laboratory',
            'user_status' => true,
        ]);

        $this->postJson(route('api.v1.auth.token.store'), [
            'email' => $user->email,
            'password' => $password,
            'device_name' => 'postman-local',
        ])
            ->assertOk()
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email', 'department', 'user_type'],
            ]);
    }

    public function test_protected_api_requires_valid_sanctum_token(): void
    {
        $this->getJson(route('api.v1.sample-dispatches.index'))
            ->assertUnauthorized();
    }
}
