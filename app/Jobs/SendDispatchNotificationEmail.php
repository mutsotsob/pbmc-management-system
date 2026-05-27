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
        $this->dispatch->loadMissing(['items', 'dispatchedBy', 'driverUser']);

        $labRecipients = User::query()
            ->where('department', 'Laboratory')
            ->where('user_status', true)
            ->whereNotNull('email')
            ->pluck('email')
            ->toArray();

        if (!empty($labRecipients)) {
            $labHtml = view('emails.sample-dispatched', [
                'dispatch' => $this->dispatch,
                'recipientType' => 'lab',
            ])->render();

            $mailer->send(
                $labRecipients,
                "Sample Dispatched - {$this->dispatch->reference}",
                $labHtml
            );
        }

        $driverEmail = $this->dispatch->driverUser?->email;

        if (!empty($driverEmail)) {
            $driverHtml = view('emails.sample-dispatched', [
                'dispatch' => $this->dispatch,
                'recipientType' => 'driver',
            ])->render();

            $mailer->send(
                [$driverEmail],
                "Dispatch Assignment - {$this->dispatch->reference}",
                $driverHtml
            );
        }

        Log::info('Dispatch notification sent', [
            'reference' => $this->dispatch->reference,
            'lab_recipients' => $labRecipients,
            'driver_recipient' => $driverEmail,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendDispatchNotificationEmail failed', [
            'reference' => $this->dispatch->reference,
            'error' => $e->getMessage(),
        ]);
    }
}
