<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Password Was Changed')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your PBMC Portal password was just changed.')
            ->line('If you made this change, no action is needed.')
            ->line('If you did not change your password, contact your administrator immediately.')
            ->action('Log In', url('/'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'password_changed',
            'title'   => 'Password Changed',
            'message' => 'Your account password was changed. If this wasn\'t you, contact an admin.',
            'url'     => url('/settings'),
        ];
    }
}
