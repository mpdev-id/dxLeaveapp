<?php

namespace App\Channels;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Get the message payload from the notification class
        $payload = $notification->toWhatsApp($notifiable);

        // Get the recipient's phone number from the 'routeNotificationFor' method in the User model
        $to = $notifiable->routeNotificationFor('whatsapp', $notification);

        if (!$to) {
            Log::warning('Could not send WhatsApp notification. User does not have a phone number.', ['user_id' => $notifiable->id]);
            return;
        }

        $appKey = config('whatsapp.app_key');
        $authKey = config('whatsapp.auth_key');
        $url = config('whatsapp.url') . '/create-message';

        if (!$appKey || !$authKey) {
            Log::error('WhatsApp notification failed. API credentials are not set in config.');
            return;
        }

        // Build the request data
        $requestData = [
            'appkey' => $appKey,
            'authkey' => $authKey,
            'to' => $to,
        ];

        if (is_array($payload)) {
            $requestData = array_merge($requestData, $payload);
        } else {
            $requestData['message'] = $payload;
        }

        $response = Http::get($url, $requestData);

        if ($response->failed()) {
            Log::error('WhatsApp notification failed.', [
                'user_id' => $notifiable->id,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);
        } else {
            Log::info('WhatsApp notification sent successfully.', ['user_id' => $notifiable->id]);
        }
    }
}
