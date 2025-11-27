<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class TestPushController extends Controller
{
    /**
     * Send a test push notification directly using WebPush library
     */
    public function sendTest(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            // Get user's push subscriptions
            $subscriptions = $user->pushSubscriptions;
            
            if ($subscriptions->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No push subscriptions found. Please enable push notifications first.',
                ], 404);
            }

            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ]
            ];

            $webPush = new WebPush($auth);
            $webPush->setAutomaticPadding(false); // Disable padding to avoid issues

            $payload = json_encode([
                'title' => '🎉 Test Notification!',
                'body' => 'This is a test push notification from Cutikuy! If you see this, push notifications are working! 🦆',
                'icon' => '/images/icons/icon-192x192.png',
                'badge' => '/images/icons/icon-72x72.png',
                'data' => [
                    'url' => '/member/profile',
                    'timestamp' => now()->toIso8601String(),
                ],
                'actions' => [
                    [
                        'action' => 'view_profile',
                        'title' => 'View Profile'
                    ]
                ]
            ]);

            $sentCount = 0;
            $errors = [];

            foreach ($subscriptions as $sub) {
                $subscription = Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                ]);

                try {
                    $report = $webPush->sendOneNotification($subscription, $payload);
                    
                    if ($report->isSuccess()) {
                        $sentCount++;
                    } else {
                        $errors[] = [
                            'endpoint' => substr($sub->endpoint, 0, 50) . '...',
                            'reason' => $report->getReason(),
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'endpoint' => substr($sub->endpoint, 0, 50) . '...',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            if ($sentCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Test notification sent to {$sentCount} device(s)!",
                    'sent_count' => $sentCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification',
                    'errors' => $errors,
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Test push notification error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
