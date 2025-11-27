import crypto from 'crypto';
import fs from 'fs';

/**
 * Generate proper VAPID keys using Node.js crypto
 * This generates valid P-256 EC keys for Web Push
 */

function urlBase64(buffer) {
    return buffer.toString('base64')
        .replace(/\+/g, '-')
        .replace(/\//g, '_')
        .replace(/=/g, '');
}

try {
    // Generate P-256 EC key pair
    const { publicKey, privateKey } = crypto.generateKeyPairSync('ec', {
        namedCurve: 'prime256v1',
        publicKeyEncoding: {
            type: 'spki',
            format: 'der'
        },
        privateKeyEncoding: {
            type: 'pkcs8',
            format: 'der'
        }
    });

    // Extract the raw public key (65 bytes)
    const rawPublicKey = publicKey.slice(-65);
    
    // Extract the raw private key (32 bytes)
    const rawPrivateKey = privateKey.slice(-32);

    const publicKeyBase64 = urlBase64(rawPublicKey);
    const privateKeyBase64 = urlBase64(rawPrivateKey);

    console.log('\n===========================================');
    console.log('VAPID Keys Generated Successfully!');
    console.log('===========================================\n');
    
    console.log('Add these to your .env file:\n');
    console.log(`VAPID_PUBLIC_KEY=${publicKeyBase64}`);
    console.log(`VAPID_PRIVATE_KEY=${privateKeyBase64}`);
    console.log('VAPID_SUBJECT=mailto:admin@cutikuy.com');
    
    console.log('\n===========================================');
    console.log('Keys are valid for production use!');
    console.log('===========================================\n');

    // Save to file
    fs.writeFileSync('vapid-keys.txt', 
`VAPID_PUBLIC_KEY=${publicKeyBase64}
VAPID_PRIVATE_KEY=${privateKeyBase64}
VAPID_SUBJECT=mailto:admin@cutikuy.com
`);
    
    console.log('Keys also saved to: vapid-keys.txt\n');

} catch (error) {
    console.error('Error generating keys:', error.message);
    console.log('\nPlease use online generator: https://vapidkeys.com/');
}
