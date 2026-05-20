<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ReportImportedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $sampleId,
        public readonly string $importedBy,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'report_imported',
            'title'   => 'New IAVIC114 Report Imported',
            'message' => "{$this->importedBy} added report for sample {$this->sampleId}.",
            'url'     => route('dashboard'),
        ];
    }
}
