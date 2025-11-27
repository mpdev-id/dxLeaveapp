<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class LoginNotification extends Notification
{
    use Queueable;

    public string $ipAddress;
    public string $userAgent;
    public string $loginTime;
    public string $device;
    public string $browser;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        string $ipAddress,
        string $userAgent,
        string $loginTime,
        string $device = 'Unknown Device',
        string $browser = 'Unknown Browser'
    ) {
        $this->ipAddress = $ipAddress;
        $this->userAgent = $userAgent;
        $this->loginTime = $loginTime;
        $this->device = $device;
        $this->browser = $browser;
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
        $title = '🔐 New Login Detected';
        $body = "Login from {$this->device} via {$this->browser} at {$this->loginTime}";

        return (new WebPushMessage)
            ->title($title)
            ->icon('/images/icons/icon-192x192.png')
            ->body($body)
            ->badge('/images/icons/icon-72x72.png')
            ->data([
                'url' => '/member/profile',
                'ip' => $this->ipAddress,
                'device' => $this->device,
                'browser' => $this->browser,
                'time' => $this->loginTime,
            ])
            ->action('View Profile', 'view_profile');
    }
}
