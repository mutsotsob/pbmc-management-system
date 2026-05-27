<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\SampleDispatch;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class SampleDispatchWorkflowService
{
    public function receive(SampleDispatch $dispatch, User $actor, array $payload): SampleDispatch
    {
        if ($dispatch->status !== 'dispatched') {
            throw ValidationException::withMessages([
                'dispatch' => 'This sample has already been processed by the laboratory workflow.',
            ]);
        }

        $fromStatus = $dispatch->status;

        $dispatch->update([
            ...$payload,
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $actor->id,
        ]);

        $dispatch->refresh();
        $this->recordTransition($dispatch, $fromStatus, $dispatch->status, $actor->id, $payload);

        return $dispatch;
    }

    public function reject(SampleDispatch $dispatch, User $actor, string $reason): SampleDispatch
    {
        if ($dispatch->status !== 'dispatched') {
            throw ValidationException::withMessages([
                'dispatch' => 'This sample has already been processed by the laboratory workflow.',
            ]);
        }

        $fromStatus = $dispatch->status;

        $dispatch->update([
            'status' => 'received',
            'received_at' => now(),
            'received_by_user_id' => $actor->id,
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => $reason,
        ]);

        $dispatch->refresh();
        $this->recordTransition($dispatch, $fromStatus, $dispatch->status, $actor->id, [
            'condition_on_arrival' => 'Rejected',
            'rejection_reason' => $reason,
        ]);

        return $dispatch;
    }

    public function process(SampleDispatch $dispatch, User $actor): SampleDispatch
    {
        if ($dispatch->status !== 'received') {
            throw ValidationException::withMessages([
                'dispatch' => 'Only received samples can be processed.',
            ]);
        }

        if ($dispatch->condition_on_arrival === 'Rejected') {
            throw ValidationException::withMessages([
                'dispatch' => 'Rejected samples cannot be processed.',
            ]);
        }

        $fromStatus = $dispatch->status;

        $dispatch->update([
            'status' => 'processed',
        ]);

        $dispatch->refresh();
        $this->recordTransition($dispatch, $fromStatus, $dispatch->status, $actor->id);

        return $dispatch;
    }

    private function recordTransition(
        SampleDispatch $dispatch,
        string $fromStatus,
        string $toStatus,
        int $actorUserId,
        array $context = []
    ): void {
        AuditLog::record('workflow_transition', $dispatch, [
            'status' => $fromStatus,
        ], [
            'status' => $toStatus,
            'actor_user_id' => $actorUserId,
            ...$context,
        ]);
    }
}
