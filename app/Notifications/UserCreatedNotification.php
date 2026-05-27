<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class UserCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $plainPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Welcome to Samples Management System')
            ->greeting("Hello {$notifiable->name},")
            ->line('Your account has been created on the Samples Management System.')
            ->line('**Email:** ' . $notifiable->email)
            ->line('**Temporary Password:** ' . $this->plainPassword)
            ->line('Please log in and change your password immediately.')
            ->action('Log In', url('/'))
            ->line('If you did not expect this account, please contact your administrator.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'user_created',
            'title'   => 'Account Created',
            'message' => 'Your PBMC Portal account has been created. Please log in and change your password.',
            'url'     => url('/'),
        ];
    }
}
