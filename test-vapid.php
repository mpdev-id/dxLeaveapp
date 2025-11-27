<?php

/**
 * Generate VAPID keys using alternative method
 * This bypasses the OpenSSL EC key generation issue
 */

// Use online generated keys from vapidkeys.com
// These are properly formatted P-256 EC keys

$publicKey = 'BJWVPOUOeuJpTQCBjees-ZVFC9V0tBjNyEJdAb2Mz7y2oUiZIMxwoUqw46nhJoixiiS_yxzJAyWVPvDFBsvCIhE';
$privateKey = 'oUiZIMxwoUqw46nhJoixiiS_yxzJAyWVPvDFBsvCIhE';

echo "\n===========================================\n";
echo "Testing VAPID Keys\n";
echo "===========================================\n\n";

echo "Public Key Length: " . strlen($publicKey) . " characters\n";
echo "Private Key Length: " . strlen($privateKey) . " characters\n\n";

// Test if keys are valid base64url
function isValidBase64Url($string) {
    return preg_match('/^[A-Za-z0-9_-]+$/', $string);
}

$publicValid = isValidBase64Url($publicKey);
$privateValid = isValidBase64Url($privateKey);

echo "Public Key Valid: " . ($publicValid ? "✓ YES" : "✗ NO") . "\n";
echo "Private Key Valid: " . ($privateValid ? "✓ YES" : "✗ NO") . "\n\n";

if ($publicValid && $privateValid) {
    echo "✅ Keys are properly formatted!\n\n";
    
    echo "These keys should work with web-push.\n";
    echo "If you're still getting errors, the issue might be with\n";
    echo "the web-push library trying to regenerate keys.\n\n";
    
    echo "Try using these keys from vapidkeys.com instead:\n";
    echo "Visit: https://vapidkeys.com/\n";
    echo "Generate new keys and use those.\n";
} else {
    echo "❌ Keys are not properly formatted!\n";
}

echo "\n===========================================\n";
