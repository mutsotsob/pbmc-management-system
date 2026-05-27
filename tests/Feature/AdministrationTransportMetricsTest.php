<?php

namespace Tests\Feature;

use App\Models\SampleDispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdministrationTransportMetricsTest extends TestCase
{
    use RefreshDatabase;

    public function test_administration_user_sees_only_their_transported_sample_metrics(): void
    {
        $driver = User::factory()->create([
            'department' => 'Administration',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $otherDriver = User::factory()->create([
            'department' => 'Administration',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $ownDispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-26',
            'sample_id' => 'OWN-PID',
            'study' => 'C114',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 2,
            'destination' => 'IDRL Southerton',
            'driver_user_id' => $driver->id,
            'driver_name' => $driver->name,
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $dispatcher->id,
        ]);
        $ownDispatch->items()->create(['participant_id' => 'OWN-PID']);

        $otherDispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-26',
            'sample_id' => 'OTHER-PID',
            'study' => 'C114',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 5,
            'destination' => 'IDRL Southerton',
            'driver_user_id' => $otherDriver->id,
            'driver_name' => $otherDriver->name,
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'dispatched',
        ]);
        $otherDispatch->items()->create(['participant_id' => 'OTHER-PID']);

        $response = $this->actingAs($driver)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('My Transport Metrics');
        $response->assertSee('Samples Transported');
        $response->assertSee('OWN-PID');
        $response->assertSee('Transport Metrics');
        $response->assertSee('Profile');
        $response->assertSee('Logout');
        $response->assertDontSee('OTHER-PID');
        $response->assertDontSee('Sample Dispatch');
        $response->assertDontSee('Overview');
        $response->assertDontSee('Users');
        $response->assertDontSee('Settings');
    }

    public function test_administration_user_is_redirected_from_full_dispatch_module(): void
    {
        $driver = User::factory()->create([
            'department' => 'Administration',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $this->actingAs($driver)
            ->get(route('sample-dispatches.index'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_administration_transport_log_shows_study_and_sorts_paginated_rows(): void
    {
        $driver = User::factory()->create([
            'department' => 'Administration',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        foreach (range(1, 13) as $index) {
            $study = match ($index) {
                1 => 'ZULU-STUDY',
                2 => 'ALPHA-STUDY',
                default => sprintf('MIDDLE-STUDY-%02d', $index),
            };

            SampleDispatch::create([
                'dispatch_date' => now()->subDays($index)->toDateString(),
                'sample_id' => sprintf('PID-%02d', $index),
                'study' => $study,
                'origin_location' => 'Mutala Vainona',
                'quantity' => 1,
                'no_of_bags' => $index,
                'destination' => 'IDRL Southerton',
                'driver_user_id' => $driver->id,
                'driver_name' => $driver->name,
                'dispatched_by_user_id' => $dispatcher->id,
                'status' => 'dispatched',
            ]);
        }

        $ascending = $this->actingAs($driver)->get(route('dashboard', [
            'sort' => 'study',
            'dir' => 'asc',
        ]));

        $ascending->assertOk();
        $ascending->assertSee('Study');
        $ascending->assertSee('Showing 1-12 of 13 records');
        $ascending->assertSee('ALPHA-STUDY');
        $ascending->assertDontSee('ZULU-STUDY');
        $ascending->assertSee('sort=study', false);
        $ascending->assertSee('dir=desc', false);

        $descending = $this->actingAs($driver)->get(route('dashboard', [
            'sort' => 'study',
            'dir' => 'desc',
        ]));

        $descending->assertOk();
        $descending->assertSee('ZULU-STUDY');
        $descending->assertDontSee('ALPHA-STUDY');
    }
}
