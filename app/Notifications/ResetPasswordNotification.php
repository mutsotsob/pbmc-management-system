<?php

namespace App\Notifications;

use App\Services\MicrosoftGraphMailer;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class ResetPasswordNotification extends Notification
{
    public function __construct(public readonly string $token) {}

    public function via(object $notifiable): array
    {
        return [];
    }

    public function send(object $notifiable): void
    {
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $html = view('emails.password-reset', [
            'resetUrl'  => $resetUrl,
            'name'      => $notifiable->name,
            'expiresIn' => config('auth.passwords.users.expire', 60),
        ])->render();

        try {
            app(MicrosoftGraphMailer::class)->send(
                [$notifiable->getEmailForPasswordReset()],
                'Reset Your Password – PBMC Portal',
                $html
            );
        } catch (\Throwable $e) {
            Log::error('ResetPasswordNotification failed', [
                'email' => $notifiable->getEmailForPasswordReset(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
