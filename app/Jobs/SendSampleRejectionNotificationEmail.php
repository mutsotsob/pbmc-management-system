<?php

namespace App\Jobs;

use App\Models\SampleDispatch;
use App\Services\MicrosoftGraphMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSampleRejectionNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    /**
     * @param string[] $recipients
     */
    public function __construct(
        public readonly SampleDispatch $dispatch,
        public readonly array $recipients,
    ) {}

    public function handle(MicrosoftGraphMailer $mailer): void
    {
        $recipients = collect($this->recipients)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($recipients)) {
            Log::info('SendSampleRejectionNotificationEmail: no recipients found, skipping.', [
                'reference' => $this->dispatch->reference,
            ]);

            return;
        }

        $this->dispatch->loadMissing(['dispatchedBy', 'driverUser', 'receivedBy', 'items']);

        $html = view('emails.sample-rejected', ['dispatch' => $this->dispatch])->render();

        $mailer->send(
            $recipients,
            "Sample Rejected - {$this->dispatch->reference}",
            $html
        );

        Log::info('Sample rejection notification sent', [
            'reference' => $this->dispatch->reference,
            'recipients' => $recipients,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendSampleRejectionNotificationEmail failed', [
            'reference' => $this->dispatch->reference,
            'error' => $e->getMessage(),
        ]);
    }
}
