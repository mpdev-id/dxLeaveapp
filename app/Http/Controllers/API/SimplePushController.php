<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class SimplePushController extends Controller
{
    /**
     * Send push notification using simple HTTP request
     * This bypasses the web-push library completely
     */
    public function sendSimpleTest(Request $request): JsonResponse
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

            $payload = json_encode([
                'title' => '✅ Simple Test!',
                'body' => 'This is a simple push notification test. If you see this, it works! 🎉',
                'icon' => '/favicon.ico',
                'badge' => '/favicon.ico',
                'data' => [
                    'url' => '/member/profile',
                ],
            ]);

            $sentCount = 0;
            $errors = [];

            foreach ($subscriptions as $sub) {
                try {
                    // Send notification using simple HTTP POST
                    // This is a basic implementation without VAPID authentication
                    // For production, you should use proper VAPID JWT
                    
                    $response = Http::timeout(10)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                            'TTL' => '86400',
                        ])
                        ->post($sub->endpoint, [
                            'notification' => json_decode($payload, true),
                        ]);

                    if ($response->successful() || $response->status() === 201) {
                        $sentCount++;
                    } else {
                        $errors[] = [
                            'endpoint' => substr($sub->endpoint, 0, 50) . '...',
                            'status' => $response->status(),
                            'body' => $response->body(),
                        ];
                    }
                } catch (\Exception $e) {
                    $errors[] = [
                        'endpoint' => substr($sub->endpoint, 0, 50) . '...',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            \Log::info('Simple push test completed', [
                'sent_count' => $sentCount,
                'total_subscriptions' => $subscriptions->count(),
                'errors' => $errors,
            ]);

            if ($sentCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Test notification attempted for {$sentCount} device(s)!",
                    'sent_count' => $sentCount,
                    'note' => 'This is a simple test without VAPID. May not work on all browsers.',
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notification. Check logs for details.',
                    'errors' => $errors,
                    'note' => 'Push notifications require VAPID authentication. There may be a configuration issue.',
                ], 500);
            }

        } catch (\Exception $e) {
            \Log::error('Simple push test error: ' . $e->getMessage());
            \Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send test notification',
                'error' => $e->getMessage(),
                'suggestion' => 'Check Laravel logs for detailed error information',
            ], 500);
        }
    }
}
