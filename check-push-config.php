<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "🔍 Checking Push Notification Configuration...\n\n";

// 1. Check VAPID Keys in .env
$publicKey = config('webpush.vapid.public_key');
$privateKey = config('webpush.vapid.private_key');
$subject = config('webpush.vapid.subject');

echo "1. VAPID Configuration:\n";
echo "   Public Key: " . ($publicKey ? substr($publicKey, 0, 10) . '...' : 'MISSING ❌') . "\n";
echo "   Private Key: " . ($privateKey ? 'PRESENT ✅' : 'MISSING ❌') . "\n";
echo "   Subject: " . ($subject ? $subject : 'MISSING ❌') . "\n\n";

if (!$publicKey || !$privateKey || !$subject) {
    echo "❌ ERROR: VAPID keys are missing in .env file!\n";
    exit(1);
}

// 2. Check Database Subscriptions
echo "2. Database Subscriptions:\n";
try {
    $count = DB::table('push_subscriptions')->count();
    echo "   Total Subscriptions: $count\n";

    if ($count > 0) {
        $sub = DB::table('push_subscriptions')->first();
        echo "   Sample Subscription Endpoint: " . substr($sub->endpoint, 0, 30) . "...\n";
        echo "   Sample Public Key: " . ($sub->public_key ? 'PRESENT ✅' : 'MISSING ❌') . "\n";
        echo "   Sample Auth Token: " . ($sub->auth_token ? 'PRESENT ✅' : 'MISSING ❌') . "\n";
    } else {
        echo "   ⚠️ WARNING: No subscriptions found in database. Users need to enable notifications.\n";
    }
} catch (\Exception $e) {
    echo "❌ ERROR: Could not connect to database: " . $e->getMessage() . "\n";
}

echo "\n3. Environment Check:\n";
echo "   OS: " . PHP_OS . "\n";
echo "   PHP Version: " . phpversion() . "\n";
echo "   OpenSSL Version: " . OPENSSL_VERSION_TEXT . "\n";

if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    echo "\n⚠️ WARNING: You are running on Windows.\n";
    echo "   Push notifications often fail on Windows due to OpenSSL EC key generation issues.\n";
    echo "   If you see 'Unable to create the local key' error in logs, this is why.\n";
    echo "   Solution: Deploy to Linux server.\n";
}

echo "\n✅ Check complete.\n";
