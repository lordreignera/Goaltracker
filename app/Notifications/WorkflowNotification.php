<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WorkflowNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $title,
        public readonly string $message,
        public readonly string $url,
        public readonly string $level = 'info',
        public readonly array $context = [],
    ) {}

    public function via(object $notifiable): array
    {
        return config('workflow.notification_channels', ['database']);
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject($this->title)
            ->greeting('Hello '.$notifiable->name.',')
            ->line($this->message)
            ->action('Open in Smart Goals Tracker', $this->url)
            ->line('This is an automated workflow notification.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'level' => $this->level,
            'context' => $this->context,
        ];
    }
}
