<?php

namespace Tests\Feature;

use App\Models\SampleDispatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiSampleDispatchWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_laboratory_user_can_process_received_sample_via_api(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-API-001',
            'study' => 'C225',
            'visit' => 'v1',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Api Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Good',
        ]);

        $this->actingAs($laboratoryUser)
            ->postJson(route('api.v1.sample-dispatches.process', $dispatch))
            ->assertOk()
            ->assertJsonPath('data.status', 'processed');
    }

    public function test_laboratory_user_cannot_process_rejected_sample_via_api(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-API-002',
            'study' => 'C225',
            'visit' => 'v2',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Api Driver 2',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => 'Damaged',
        ]);

        $this->actingAs($laboratoryUser)
            ->postJson(route('api.v1.sample-dispatches.process', $dispatch))
            ->assertForbidden();
    }
}
