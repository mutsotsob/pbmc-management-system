<?php

namespace Tests\Feature;

use App\Jobs\SendSampleRejectionNotificationEmail;
use App\Models\Iavic114PbmcReport;
use App\Models\SampleDispatch;
use App\Models\User;
use App\Notifications\SampleDispatchRejectedNotification;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class LaboratoryDispatchDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_laboratory_dashboard_shows_pending_dispatched_samples_for_receipt(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $pendingDispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-PENDING',
            'study' => 'C114',
            'visit' => 'v3',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 2,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Pending Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'dispatched',
        ]);
        $pendingDispatch->items()->create(['participant_id' => 'PID-PENDING']);

        $receivedDispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-RECEIVED',
            'study' => 'C114',
            'visit' => 'v4',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Received Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Good',
        ]);
        $receivedDispatch->items()->create(['participant_id' => 'PID-RECEIVED']);

        $response = $this->actingAs($laboratoryUser)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Laboratory Receipt Queue');
        $response->assertSee('Dispatched Samples');
        $response->assertSee('Processing Reports');
        $response->assertSee('PID-PENDING');
        $response->assertSee('v3');
        $response->assertSee('Receive');
        $response->assertSee('Reject');
        $response->assertSee(route('sample-dispatches.show', $pendingDispatch), false);
        $response->assertDontSee('PID-RECEIVED');

        $receivedResponse = $this->actingAs($laboratoryUser)->get(route('dashboard', [
            'sample_status' => 'received',
        ]));

        $receivedResponse->assertOk();
        $receivedResponse->assertSee('Received Samples');
        $receivedResponse->assertSee('PID-RECEIVED');
        $receivedResponse->assertSee('v4');
        $receivedResponse->assertSee('Good');
        $receivedResponse->assertDontSee('PID-PENDING');
    }

    public function test_laboratory_user_can_open_and_receive_a_dispatched_sample(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-OPEN',
            'study' => 'C114',
            'visit' => 'v5',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Open Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'dispatched',
        ]);

        $this->actingAs($laboratoryUser)
            ->get(route('sample-dispatches.show', $dispatch))
            ->assertOk()
            ->assertSee('Confirm Lab Receipt')
            ->assertSee('Back to Receipt Queue');

        $this->actingAs($laboratoryUser)
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.receive', $dispatch), [
                '_token' => 'test-token',
                'condition_on_arrival' => 'Good',
                'notes' => 'Received before processing.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sample_dispatches', [
            'id' => $dispatch->id,
            'status' => 'received',
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Good',
        ]);
    }

    public function test_laboratory_user_can_reject_a_dispatched_sample_and_notify_recipients(): void
    {
        Queue::fake();
        Notification::fake();

        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $driver = User::factory()->create([
            'department' => 'Administration',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $clinicalOperationsUser = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-REJECT',
            'study' => 'C114',
            'visit' => 'v6',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_user_id' => $driver->id,
            'driver_name' => $driver->name,
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'dispatched',
        ]);

        $this->actingAs($laboratoryUser)
            ->from(route('dashboard'))
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.reject', $dispatch), [
                '_token' => 'test-token',
                'rejection_reason' => '',
            ])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasErrors('rejection_reason');

        $this->actingAs($laboratoryUser)
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.reject', $dispatch), [
                '_token' => 'test-token',
                'rejection_reason' => 'Sample bag seal was broken on arrival.',
            ])
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('sample_dispatches', [
            'id' => $dispatch->id,
            'status' => 'received',
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => 'Sample bag seal was broken on arrival.',
        ]);

        Notification::assertSentTo($driver, SampleDispatchRejectedNotification::class);
        Notification::assertSentTo($dispatcher, SampleDispatchRejectedNotification::class);
        Notification::assertSentTo($clinicalOperationsUser, SampleDispatchRejectedNotification::class);

        Queue::assertPushed(SendSampleRejectionNotificationEmail::class, function (SendSampleRejectionNotificationEmail $job) use ($dispatch, $driver, $dispatcher, $clinicalOperationsUser) {
            return $job->dispatch->is($dispatch)
                && collect($job->recipients)->contains($driver->email)
                && collect($job->recipients)->contains($dispatcher->email)
                && collect($job->recipients)->contains($clinicalOperationsUser->email);
        });
    }

    public function test_laboratory_user_can_open_imported_reports_from_dashboard_card(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        Iavic114PbmcReport::create([
            'sample_id_visit_number' => 'C114001_V01',
            'report_date' => '2026-05-27',
            'sample_condition' => 'Good',
            'viability_percent' => 92,
            'cryovials_frozen' => 4,
            'operator_initials' => 'BM',
            'source_workbook' => 'Manual Entry',
            'source_sheet' => 'Dashboard Form',
        ]);

        $this->actingAs($laboratoryUser)
            ->get(route('iavic114-reports.index'))
            ->assertOk()
            ->assertSee('Imported Processing Reports')
            ->assertSee('C114001_V01');
    }

    public function test_laboratory_user_can_process_non_rejected_received_sample(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-PROCESS',
            'study' => 'C225',
            'visit' => 'v7',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Process Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Good',
        ]);

        $this->actingAs($laboratoryUser)
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.process', $dispatch), [
                '_token' => 'test-token',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('sample_dispatches', [
            'id' => $dispatch->id,
            'status' => 'processed',
        ]);
    }

    public function test_laboratory_user_cannot_process_rejected_sample(): void
    {
        $laboratoryUser = User::factory()->create([
            'department' => 'Laboratory',
            'user_type' => 'user',
            'user_status' => true,
        ]);

        $dispatcher = User::factory()->create([
            'department' => 'Clinical Operations',
            'user_status' => true,
        ]);

        $dispatch = SampleDispatch::create([
            'dispatch_date' => '2026-05-27',
            'sample_id' => 'PID-REJECTED',
            'study' => 'C225',
            'visit' => 'v8',
            'origin_location' => 'Mutala Vainona',
            'quantity' => 1,
            'no_of_bags' => 1,
            'destination' => 'IDRL Southerton',
            'driver_name' => 'Rejected Driver',
            'dispatched_by_user_id' => $dispatcher->id,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $laboratoryUser->id,
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => 'Damaged sample',
        ]);

        $this->actingAs($laboratoryUser)
            ->withSession(['_token' => 'test-token'])
            ->post(route('sample-dispatches.process', $dispatch), [
                '_token' => 'test-token',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('sample_dispatches', [
            'id' => $dispatch->id,
            'status' => 'received',
        ]);
    }
}
