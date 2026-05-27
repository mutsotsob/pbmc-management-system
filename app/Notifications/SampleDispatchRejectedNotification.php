<?php

namespace App\Notifications;

use App\Models\SampleDispatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SampleDispatchRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly SampleDispatch $dispatch) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $this->dispatch->loadMissing(['receivedBy']);

        return [
            'type' => 'sample_rejected',
            'title' => 'Sample Rejected',
            'message' => sprintf(
                '%s rejected dispatch %s. Reason: %s',
                $this->dispatch->receivedBy?->name ?? 'Laboratory',
                $this->dispatch->reference,
                $this->dispatch->rejection_reason ?: 'No reason provided'
            ),
            'url' => $notifiable->isDepartment('Administration')
                ? route('dashboard')
                : route('sample-dispatches.show', $this->dispatch),
        ];
    }
}
