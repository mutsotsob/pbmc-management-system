<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class UserReportExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_report_export_routes_are_registered(): void
    {
        $this->assertTrue(Route::has('admin.users.export.excel'));
        $this->assertTrue(Route::has('admin.users.export.csv'));
        $this->assertTrue(Route::has('admin.users.export.pdf'));
        $this->assertTrue(Route::has('admin.users.export.selected.excel'));
        $this->assertTrue(Route::has('admin.users.export.selected.csv'));
        $this->assertTrue(Route::has('admin.users.export.selected.pdf'));
    }

    public function test_admin_can_export_users_csv(): void
    {
        $admin = User::factory()->create([
            'department' => "CEO's Office",
            'user_type' => 'admin',
            'user_status' => true,
        ]);

        User::factory()->create([
            'name' => 'Report User',
            'email' => 'report-user@example.test',
            'department' => 'Clinical Operations',
            'job_title' => 'Coordinator',
            'user_status' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.export.csv'));

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Report User', $content);
        $this->assertStringContainsString('report-user@example.test', $content);
        $this->assertStringContainsString('Clinical Operations', $content);
    }

    public function test_admin_can_export_selected_users_csv(): void
    {
        $admin = User::factory()->create([
            'department' => "CEO's Office",
            'user_type' => 'admin',
            'user_status' => true,
        ]);

        $selected = User::factory()->create([
            'name' => 'Selected Report User',
            'email' => 'selected-report-user@example.test',
            'user_status' => true,
        ]);

        User::factory()->create([
            'name' => 'Unselected Report User',
            'email' => 'unselected-report-user@example.test',
            'user_status' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.export.selected.csv'), [
            'selected_user_ids' => [$selected->id],
        ]);

        $response->assertOk();

        $content = $response->streamedContent();

        $this->assertStringContainsString('Selected Report User', $content);
        $this->assertStringNotContainsString('Unselected Report User', $content);
    }
}
