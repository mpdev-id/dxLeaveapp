import crypto from 'crypto';
import fs from 'fs';

/**
 * Generate PROPER VAPID keys for Web Push
 * Using correct P-256 EC key generation
 */

function urlBase64(buffer) {
    return buffer.toString('base64')
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

try {
    console.log('\n🔑 Generating VAPID Keys...\n');

    // Generate P-256 EC key pair
    const { publicKey, privateKey } = crypto.generateKeyPairSync('ec', {
        namedCurve: 'prime256v1', // P-256
        publicKeyEncoding: {
            type: 'spki',
            format: 'der'
        },
        privateKeyEncoding: {
            type: 'sec1', // Use SEC1 format for EC private keys
            format: 'der'
        }
    });

    // For public key: Extract last 65 bytes (uncompressed point)
    const rawPublicKey = publicKey.slice(-65);
    
    // For private key: Extract the actual key bytes (should be 32 bytes)
    // SEC1 format: skip the header bytes
    let rawPrivateKey;
    if (privateKey.length > 32) {
        // Find the 32-byte private key in the DER structure
        rawPrivateKey = privateKey.slice(-32);
    } else {
        rawPrivateKey = privateKey;
    }

    const publicKeyBase64 = urlBase64(rawPublicKey);
    const privateKeyBase64 = urlBase64(rawPrivateKey);

    console.log('===========================================');
    console.log('✅ VAPID Keys Generated Successfully!');
    console.log('===========================================\n');
    
    console.log(`Public Key:  ${publicKeyBase64}`);
    console.log(`Length: ${publicKeyBase64.length} chars (should be ~88)\n`);
    
    console.log(`Private Key: ${privateKeyBase64}`);
    console.log(`Length: ${privateKeyBase64.length} chars (should be ~43)\n`);
    
    console.log('===========================================');
    console.log('📋 Add these to your .env file:');
    console.log('===========================================\n');
    
    console.log(`VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log(`VAPID_PRIVATE_KEY=${privateKeyBase64}`);
    console.log('VAPID_SUBJECT=mailto:admin@cutikuy.com\n');

    // Save to file
    const envContent = `VAPID_PUBLIC_KEY=${publicKeyBase64}
VAPID_PRIVATE_KEY=${privateKeyBase64}
VAPID_SUBJECT=mailto:admin@cutikuy.com
`;

    fs.writeFileSync('vapid-keys-new.txt', envContent);
    
    console.log('✅ Keys saved to: vapid-keys-new.txt\n');
    
    // Verify lengths
    if (publicKeyBase64.length >= 85 && publicKeyBase64.length <= 90 &&
        privateKeyBase64.length >= 40 && privateKeyBase64.length <= 45) {
        console.log('✅ Key lengths are correct!');
        console.log('✅ These keys should work with web-push!\n');
    } else {
        console.log('⚠️  Warning: Key lengths might be incorrect');
        console.log('   Please verify or use keys from https://vapidkeys.com/\n');
    }

} catch (error) {
    console.error('❌ Error generating keys:', error.message);
    console.log('\n📌 Alternative: Use https://vapidkeys.com/ to generate keys\n');
}
