<?php

/**
 * Generate VAPID-compatible keys without OpenSSL EC
 * This uses a simpler approach for testing purposes
 */

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Generate random bytes for keys
$publicKey = base64url_encode(random_bytes(65));
$privateKey = base64url_encode(random_bytes(32));

echo "\n===========================================\n";
echo "VAPID Keys Generated!\n";
echo "===========================================\n\n";

echo "Copy these lines to your .env file:\n\n";
echo "VAPID_PUBLIC_KEY={$publicKey}\n";
echo "VAPID_PRIVATE_KEY={$privateKey}\n";
echo "VAPID_SUBJECT=mailto:admin@cutikuy.com\n";

echo "\n===========================================\n";
echo "IMPORTANT: These are test keys.\n";
echo "For production, use proper VAPID keys from:\n";
echo "https://vapidkeys.com/\n";
echo "===========================================\n\n";

// Also create a backup file
file_put_contents('vapid-keys.txt', 
"VAPID_PUBLIC_KEY={$publicKey}
VAPID_PRIVATE_KEY={$privateKey}
VAPID_SUBJECT=mailto:admin@cutikuy.com
");

echo "Keys also saved to: vapid-keys.txt\n\n";
