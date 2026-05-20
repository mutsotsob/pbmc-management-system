<?php

namespace App\Jobs;

use App\Models\SampleDispatch;
use App\Models\User;
use App\Services\MicrosoftGraphMailer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendDispatchNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 30;

    public function __construct(public readonly SampleDispatch $dispatch) {}

    public function handle(MicrosoftGraphMailer $mailer): void
    {
        $recipients = User::where('department', 'Laboratory')
            ->where('user_status', true)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        if (empty($recipients)) {
            Log::info('SendDispatchNotificationEmail: no active Laboratory users found, skipping.');
            return;
        }

        $this->dispatch->loadMissing(['dispatchedBy', 'driverUser']);

        $html = view('emails.sample-dispatched', ['dispatch' => $this->dispatch])->render();

        $mailer->send(
            $recipients,
            "Sample Dispatched – {$this->dispatch->reference}",
            $html
        );

        Log::info('Dispatch notification sent', [
            'reference'  => $this->dispatch->reference,
            'recipients' => $recipients,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendDispatchNotificationEmail failed', [
            'reference' => $this->dispatch->reference,
            'error'     => $e->getMessage(),
        ]);
    }
}
