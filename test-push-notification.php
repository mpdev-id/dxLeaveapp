<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get the authenticated user (ganti dengan user ID Anda)
$userId = (int)readline("Enter your User ID: ");
$user = App\Models\User::find($userId);

if (!$user) {
    echo "User not found!\n";
    exit(1);
}

echo "Sending test notification to: {$user->name} ({$user->email})\n";

// Create a dummy leave request for testing
$leaveRequest = new App\Models\LeaveRequest();
$leaveRequest->user_id = $user->id;
$leaveRequest->current_status = 'Approved';
$leaveRequest->start_date = now();
$leaveRequest->end_date = now()->addDays(3);
$leaveRequest->leave_type_id = 1; // Adjust if needed

// Load the leave type relationship
$leaveRequest->setRelation('leaveType', (object)['name' => 'Annual Leave']);

// Send notification
try {
    $user->notify(new App\Notifications\LeaveRequestStatusUpdated($leaveRequest));
    echo "✅ Push notification sent successfully!\n";
    echo "Check your browser for the notification.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
