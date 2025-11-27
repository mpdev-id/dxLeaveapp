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
            ->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users with push subscriptions found.',
            ], 404);
        }

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        // Send notification to each user
        foreach ($users as $user) {
            try {
                $user->notify(new TestPushNotification($title, $body));
                $sentCount++;
                
                Log::info('Push notification sent', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'title' => $title,
                ]);
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'user' => $user->name,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Failed to send push notification', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Determine response based on results
        if ($sentCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "Notification sent to {$sentCount} user(s)" . 
                            ($failedCount > 0 ? ", {$failedCount} failed" : ""),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'sent_to' => $users->pluck('name'),
                'errors' => $errors,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notifications to all users',
                'sent_count' => 0,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'note' => 'This may be due to OpenSSL EC key issue on Windows. Deploy to Linux server for production use.',
            ], 500);
        }
    }

    /**
     * Send test notification to all subscribed users.
     */
    public function sendToAll(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:100',
            'body' => 'nullable|string|max:255',
        ]);

        $title = $validated['title'] ?? 'Broadcast Notification';
        $body = $validated['body'] ?? 'This is a broadcast notification from Cutikuy! 🦆';

        $users = User::whereHas('pushSubscriptions')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users with push subscriptions found.',
            ], 404);
        }

        $sentCount = 0;
        $failedCount = 0;
        $errors = [];

        // Send notification to all subscribed users
        foreach ($users as $user) {
            try {
                $user->notify(new TestPushNotification($title, $body));
                $sentCount++;
            } catch (\Exception $e) {
                $failedCount++;
                $errors[] = [
                    'user' => $user->name,
                    'error' => $e->getMessage(),
                ];
                
                Log::error('Failed to send broadcast notification', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Broadcast notification sent', [
            'title' => $title,
            'total_users' => $users->count(),
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
        ]);

        if ($sentCount > 0) {
            return response()->json([
                'success' => true,
                'message' => "Broadcast sent to {$sentCount} user(s)" . 
                            ($failedCount > 0 ? ", {$failedCount} failed" : ""),
                'total_users' => $users->count(),
                'sent_count' => $sentCount,
                'failed_count' => $failedCount,
                'errors' => $errors,
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send broadcast to all users',
                'total_users' => $users->count(),
                'sent_count' => 0,
                'failed_count' => $failedCount,
                'errors' => $errors,
                'note' => 'This may be due to OpenSSL EC key issue on Windows. Deploy to Linux server for production use.',
            ], 500);
        }
    }
}
