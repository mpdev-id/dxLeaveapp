<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\TestPushNotification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Notification;

class PushNotificationTestController extends Controller
{
    /**
     * Get list of users with push subscriptions.
     */
    public function getSubscribedUsers(): JsonResponse
    {
        $users = User::whereHas('pushSubscriptions')
            ->select('id', 'name', 'email', 'employee_code', 'department_id')
            ->with('department:id,name')
            ->withCount('pushSubscriptions')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
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

        // Send notification to each user
        foreach ($users as $user) {
            $user->notify(new TestPushNotification($title, $body));
        }

        return response()->json([
            'success' => true,
            'message' => "Test notification sent to {$users->count()} user(s).",
            'sent_to' => $users->pluck('name'),
        ]);
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

        $title = $validated['title'] ?? 'Test Notification';
        $body = $validated['body'] ?? 'This is a test push notification from Cutikuy! 🦆';

        $users = User::whereHas('pushSubscriptions')->get();

        if ($users->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No users with push subscriptions found.',
            ], 404);
        }

        // Send notification to all subscribed users
        Notification::send($users, new TestPushNotification($title, $body));

        return response()->json([
            'success' => true,
            'message' => "Test notification sent to all {$users->count()} subscribed user(s).",
            'sent_to' => $users->pluck('name'),
        ]);
    }
}
