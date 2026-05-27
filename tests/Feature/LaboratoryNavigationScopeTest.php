<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaboratoryNavigationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_laboratory_user_sees_only_home_overview_profile_and_logout_navigation(): void
    {
        $user = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'admin',
            'user_status' => true,
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Home');
        $response->assertSee('Overview');
        $response->assertSee('Profile');
        $response->assertSee('Logout');
        $response->assertDontSee('Sample Dispatch');
        $response->assertDontSee('Transport Metrics');
        $response->assertDontSee('Users');
        $response->assertDontSee('Audit Log');
        $response->assertDontSee('Settings');
        $response->assertDontSee('Notifications');
    }

    public function test_laboratory_user_is_restricted_to_home_overview_and_profile_routes(): void
    {
        $user = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'admin',
            'user_status' => true,
        ]);

        $this->actingAs($user)
            ->get(route('analytics.index'))
            ->assertOk();

        $this->actingAs($user)
            ->get(route('sample-dispatches.index'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('admin.users'))
            ->assertRedirect(route('dashboard'));

        $this->actingAs($user)
            ->get(route('settings'))
            ->assertRedirect(route('dashboard'));
    }
}
