<?php

namespace Tests\Feature;

use App\Jobs\SendDispatchNotificationEmail;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SampleDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_sample_dispatch_bulk_route_is_registered(): void
    {
        $this->assertTrue(Route::has('sample-dispatches.bulk'));
        $this->assertSame('sample-dispatches/bulk', Route::getRoutes()->getByName('sample-dispatches.bulk')->uri());
    }

    public function test_bulk_dispatch_creates_dispatches_and_items(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $driver = User::factory()->create([
            'name' => 'Dispatch Driver',
            'department' => 'Administration',
            'phone_number' => '0712345678',
            'user_status' => true,
        ]);

        $response = $this->actingAs($user)->withSession(['_token' => 'test-token'])->post(route('sample-dispatches.bulk'), [
            '_token' => 'test-token',
            'bulk_rows' => [
                [
                    'participant_id' => 'PID-001',
                    'study' => 'C114',
                    'visit' => 'v1',
                    'no_of_bags' => 2,
                    'dispatch_date' => '2026-05-26',
                    'dispatch_time' => '09:30',
                    'origin_location' => 'Mutala Vainona',
                    'destination' => 'IDRL Southerton',
                    'driver_user_id' => $driver->id,
                    'driver_name' => '',
                    'driver_phone' => '',
                    'description' => 'First queued sample',
                ],
                [
                    'participant_id' => 'PID-002',
                    'study' => 'C114',
                    'visit' => 'v2',
                    'no_of_bags' => 3,
                    'dispatch_date' => '2026-05-26',
                    'dispatch_time' => '10:00',
                    'origin_location' => 'Mutala Vainona',
                    'destination' => 'IDRL Southerton',
                    'driver_user_id' => $driver->id,
                    'driver_name' => '',
                    'driver_phone' => '',
                    'description' => 'Second queued sample',
                ],
            ],
        ]);

        $response->assertRedirect(route('sample-dispatches.index'));

        $this->assertDatabaseHas('sample_dispatches', [
            'sample_id' => 'PID-001',
            'visit' => 'v1',
            'driver_name' => 'Dispatch Driver',
            'driver_phone' => '0712345678',
            'quantity' => 1,
            'no_of_bags' => 2,
        ]);

        $this->assertDatabaseHas('sample_dispatches', [
            'sample_id' => 'PID-002',
            'visit' => 'v2',
            'driver_name' => 'Dispatch Driver',
            'driver_phone' => '0712345678',
            'quantity' => 1,
            'no_of_bags' => 3,
        ]);

        $this->assertDatabaseHas('sample_dispatch_items', [
            'participant_id' => 'PID-001',
        ]);

        $this->assertDatabaseHas('sample_dispatch_items', [
            'participant_id' => 'PID-002',
        ]);

        Queue::assertPushed(SendDispatchNotificationEmail::class, 2);
    }

    public function test_bulk_dispatch_requires_integer_bag_count(): void
    {
        Queue::fake();

        $user = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $response = $this->actingAs($user)
            ->from(route('sample-dispatches.index'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.bulk'), [
                '_token' => 'test-token',
                'bulk_rows' => [
                    [
                        'participant_id' => 'PID-003',
                        'study' => 'C114',
                        'visit' => 'v1',
                        'no_of_bags' => '1.5',
                        'dispatch_date' => '2026-05-26',
                        'dispatch_time' => '09:30',
                        'origin_location' => 'Mutala Vainona',
                        'destination' => 'IDRL Southerton',
                        'driver_user_id' => null,
                        'driver_name' => 'Manual Driver',
                        'driver_phone' => '0712345678',
                        'description' => 'Invalid bag count',
                    ],
                ],
            ]);

        $response->assertRedirect(route('sample-dispatches.index'));
        $response->assertSessionHasErrors('bulk_rows.0.no_of_bags');

        $this->assertDatabaseMissing('sample_dispatches', [
            'sample_id' => 'PID-003',
        ]);
    }
}
