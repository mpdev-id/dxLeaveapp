<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class TestPushNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $title;
    public string $body;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $title = 'Test Notification', string $body = 'This is a test push notification from Cutikuy! 🦆')
    {
        $this->title = $title;
        $this->body = $body;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the Web Push representation of the notification.
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title($this->title)
            ->icon('/images/icons/icon-192x192.png')
            ->body($this->body)
            ->action('View', 'view_notification')
            ->data(['url' => '/member/profile']);
    }
}
