<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PushSubscriptionController extends Controller
{
    /**
     * Store a new push subscription.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
            'keys.auth' => 'required|string',
            'keys.p256dh' => 'required|string',
        ]);

        $user = $request->user();

        // Delete existing subscriptions for this endpoint to avoid duplicates
        $user->pushSubscriptions()->where('endpoint', $validated['endpoint'])->delete();

        // Create new subscription
        $user->updatePushSubscription(
            $validated['endpoint'],
            $validated['keys']['p256dh'],
            $validated['keys']['auth']
        );

        return response()->json([
            'success' => true,
            'message' => 'Push subscription created successfully',
        ], 201);
    }

    /**
     * Delete a push subscription.
     */
    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = $request->user();
        
        $deleted = $user->pushSubscriptions()
            ->where('endpoint', $validated['endpoint'])
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Push subscription deleted successfully',
            'deleted' => $deleted,
        ]);
    }
}
