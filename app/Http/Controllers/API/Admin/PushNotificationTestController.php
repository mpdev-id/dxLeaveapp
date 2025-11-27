<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestPushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class PushNotificationTestController extends Controller
{
    /**
     * Get list of users with push subscriptions.
     */
    public function getSubscribedUsers(): JsonResponse
    {
        try {
            $users = User::whereHas('pushSubscriptions')
                ->select('id', 'name', 'email', 'employee_code', 'department_id')
                ->with('department:id,name')
                ->withCount('pushSubscriptions')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $users,
                'total' => $users->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to fetch subscribed users: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch subscribed users',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send test notification to specific user(s).
     */
    /**
     * Send test notification to specific user(s) using WebPush directly.
     */
    public function sendTest(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'title' => 'nullable|string|max:100',
            'body' => 'nullable|string|max:255',
        ]);

        $title = $validated['title'] ?? 'Test Notification';
        $body = $validated['body'] ?? 'This is a test push notification from Cutikuy! 🦆';

        $users = User::whereIn('id', $validated['user_ids'])
            ->whereHas('pushSubscriptions')
            ->with('pushSubscriptions')
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users with push subscriptions found.',
            ], 404);
        }

        return $this->sendDirectPush($users, $title, $body);
    }

    /**
     * Send test notification to all subscribed users using WebPush directly.
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'body' => 'nullable|string|max:255',
        ]);

        $title = $validated['title'] ?? 'Broadcast Notification';
        $body = $validated['body'] ?? 'This is a broadcast notification from Cutikuy! 🦆';

        $users = User::whereHas('pushSubscriptions')
            ->with('pushSubscriptions')
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users with push subscriptions found.',
            ], 404);
        }

        return $this->sendDirectPush($users, $title, $body);
    }

    /**
     * Helper to send push notifications directly using WebPush library
     */
    private function sendDirectPush($users, $title, $body): JsonResponse
    {
        try {
            $auth = [
                'VAPID' => [
                    'subject' => config('webpush.vapid.subject'),
                    'publicKey' => config('webpush.vapid.public_key'),
                    'privateKey' => config('webpush.vapid.private_key'),
                ]
            ];

            $webPush = new \Minishlink\WebPush\WebPush($auth);
            $webPush->setAutomaticPadding(false); // Important: Match successful implementation

            $payload = json_encode([
                'title' => $title,
                'body' => $body,
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
            $failedCount = 0;
            $errors = [];

            foreach ($users as $user) {
                foreach ($user->pushSubscriptions as $sub) {
                    try {
                        $subscription = \Minishlink\WebPush\Subscription::create([
                            'endpoint' => $sub->endpoint,
                            'publicKey' => $sub->public_key,
                            'authToken' => $sub->auth_token,
                        ]);

                        $report = $webPush->sendOneNotification($subscription, $payload);

                        if ($report->isSuccess()) {
                            $sentCount++;
                            Log::info('Push sent successfully', ['user_id' => $user->id]);
                        } else {
                            $failedCount++;
                            $errors[] = [
                                'user' => $user->name,
                                'reason' => $report->getReason(),
                            ];
                            Log::error('Push failed', ['user_id' => $user->id, 'reason' => $report->getReason()]);
                        }
                    } catch (\Exception $e) {
                        $failedCount++;
                        $errorMessage = $e->getMessage();
                        
                        if (str_contains($errorMessage, 'Unable to create the local key')) {
                            $errorMessage .= " (Windows OpenSSL Issue)";
                        }

                        $errors[] = [
                            'user' => $user->name,
                            'error' => $errorMessage,
                        ];
                        Log::error('Push exception', ['user_id' => $user->id, 'error' => $errorMessage]);
                    }
                }
            }

            if ($sentCount > 0) {
                return response()->json([
                    'success' => true,
                    'message' => "Notification sent to {$sentCount} device(s)" . 
                                ($failedCount > 0 ? ", {$failedCount} failed" : ""),
                    'sent_count' => $sentCount,
                    'failed_count' => $failedCount,
                    'errors' => $errors,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send notifications',
                    'sent_count' => 0,
                    'failed_count' => $failedCount,
                    'errors' => $errors,
                    'note' => 'If on Windows, this might be an OpenSSL issue.',
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('Direct push error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'System error sending notifications',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
