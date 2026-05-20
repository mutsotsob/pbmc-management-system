<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AcrnSyncCompletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly bool $success,
        public readonly string $summary,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'sync_completed',
            'title'   => $this->success ? 'ACRN Sync Completed' : 'ACRN Sync Failed',
            'message' => $this->summary,
            'url'     => route('dashboard'),
        ];
    }
}
