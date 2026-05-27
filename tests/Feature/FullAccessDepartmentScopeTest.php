<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FullAccessDepartmentScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_ceo_office_and_it_data_systems_users_can_access_all_major_sections(): void
    {
        foreach (["CEO's Office", 'IT and Data Systems', 'ITand Data Systems'] as $department) {
            $user = User::factory()->create([
                'department' => $department,
                'user_type' => 'user',
                'user_status' => true,
            ]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertSee('Home')
                ->assertSee('Overview')
                ->assertSee('Sample Dispatch')
                ->assertSee('Users')
                ->assertSee('Audit Log')
                ->assertSee('Settings')
                ->assertSee('Profile')
                ->assertSee('Logout');

            $this->actingAs($user)->get(route('admin.users'))->assertOk();
            $this->actingAs($user)->get(route('admin.audit-logs'))->assertOk();
            $this->actingAs($user)->get(route('sample-dispatches.create'))->assertOk();
            $this->actingAs($user)->get(route('drivers.index'))->assertOk();
            $this->actingAs($user)->get(route('settings'))->assertOk();
        }
    }
}
