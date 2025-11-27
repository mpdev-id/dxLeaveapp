<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushMessage;
use NotificationChannels\WebPush\WebPushChannel;

class WelcomePushNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return [WebPushChannel::class];
    }

    /**
     * Get the web push representation of the notification.
     */
    public function toWebPush($notifiable, $notification): WebPushMessage
    {
        return (new WebPushMessage)
            ->title('🎉 Push Notifications Enabled!')
            ->icon('/favicon.ico')
            ->body('You will now receive notifications from Cutikuy. This is a test notification to confirm everything is working!')
            ->action('View Profile', 'view_profile')
            ->data(['url' => '/member/profile'])
            ->badge('/favicon.ico');
    }
}
