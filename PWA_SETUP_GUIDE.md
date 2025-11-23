# 📱 PWA (Progressive Web App) Setup Guide

## ✅ Files Created

### 1. **manifest.json** (`public/manifest.json`)
- App metadata and configuration
- Icons for different sizes
- Display mode: standalone
- Theme color and background color
- App shortcuts

### 2. **sw.js** (`public/sw.js`)
- Service Worker for offline functionality
- Caching strategies
- Background sync
- Push notifications support

### 3. **offline.html** (`public/offline.html`)
- Fallback page when offline
- Auto-reload when connection restored

### 4. **Template Updates** (`resources/views/template/admin.blade.php`)
- PWA meta tags
- Manifest link
- Service Worker registration
- Install prompt
- Online/Offline detection

---

## 🚀 Features Implemented

### ✅ 1. **Installable**
- Add to Home Screen on mobile
- Install as desktop app
- Custom install prompt with banner

### ✅ 2. **Offline Support**
- Works without internet connection
- Caches static assets
- Fallback offline page
- Network-first strategy for dynamic content

### ✅ 3. **App-like Experience**
- Standalone display mode (no browser UI)
- Custom splash screen
- Theme color matching your brand
- Smooth transitions

### ✅ 4. **Performance**
- Fast loading with caching
- Background sync for offline actions
- Optimized asset delivery

### ✅ 5. **Notifications** (Ready)
- Push notification support
- Notification click handling
- Badge support

---

## 📋 Setup Instructions

### Step 1: Generate App Icons

You need to create icons in these sizes:
- 72x72
- 96x96
- 128x128
- 144x144
- 152x152
- 192x192
- 384x384
- 512x512

**Quick way to generate:**
1. Create a 512x512 PNG logo
2. Use online tool: https://www.pwabuilder.com/imageGenerator
3. Upload your logo
4. Download all sizes
5. Place in `public/images/icons/`

**Or use this command (requires ImageMagick):**
```bash
# Install ImageMagick first
# Then run:
convert logo.png -resize 72x72 public/images/icons/icon-72x72.png
convert logo.png -resize 96x96 public/images/icons/icon-96x96.png
convert logo.png -resize 128x128 public/images/icons/icon-128x128.png
convert logo.png -resize 144x144 public/images/icons/icon-144x144.png
convert logo.png -resize 152x152 public/images/icons/icon-152x152.png
convert logo.png -resize 192x192 public/images/icons/icon-192x192.png
convert logo.png -resize 384x384 public/images/icons/icon-384x384.png
convert logo.png -resize 512x512 public/images/icons/icon-512x512.png
```

### Step 2: Test PWA

#### **Desktop (Chrome/Edge):**
1. Open your app in browser
2. Look for install icon in address bar
3. Click to install
4. App opens in standalone window

#### **Mobile (Android):**
1. Open in Chrome
2. Tap menu (3 dots)
3. Select "Add to Home screen"
4. App icon appears on home screen

#### **Mobile (iOS):**
1. Open in Safari
2. Tap share button
3. Select "Add to Home Screen"
4. App icon appears on home screen

### Step 3: Verify Installation

#### **Check Service Worker:**
```javascript
// Open DevTools Console
navigator.serviceWorker.getRegistrations().then(registrations => {
    console.log('Service Workers:', registrations);
});
```

#### **Check Manifest:**
1. Open DevTools
2. Go to Application tab
3. Click "Manifest" in sidebar
4. Verify all fields are correct

#### **Test Offline:**
1. Open DevTools
2. Go to Network tab
3. Check "Offline" checkbox
4. Reload page
5. Should show offline page

---

## 🎨 Customization

### Change Theme Color

Edit `manifest.json`:
```json
{
  "theme_color": "#YOUR_COLOR",
  "background_color": "#YOUR_COLOR"
}
```

Edit `admin.blade.php`:
```html
<meta name="theme-color" content="#YOUR_COLOR">
```

### Change App Name

Edit `manifest.json`:
```json
{
  "name": "Your App Name",
  "short_name": "App"
}
```

### Add More Shortcuts

Edit `manifest.json`:
```json
{
  "shortcuts": [
    {
      "name": "Your Shortcut",
      "url": "/your-url",
      "icons": [...]
    }
  ]
}
```

---

## 🔧 Advanced Features

### 1. **Background Sync**

When user is offline, queue actions:
```javascript
// In your app
if ('serviceWorker' in navigator && 'sync' in self.registration) {
    navigator.serviceWorker.ready.then(registration => {
        return registration.sync.register('sync-leave-requests');
    });
}
```

### 2. **Push Notifications**

Request permission:
```javascript
Notification.requestPermission().then(permission => {
    if (permission === 'granted') {
        console.log('Notification permission granted');
    }
});
```

Send notification from server:
```php
// Laravel example
use Illuminate\Support\Facades\Notification;

$user->notify(new PushNotification([
    'title' => 'Leave Approved',
    'body' => 'Your leave request has been approved',
    'url' => '/admin/master/leave-request'
]));
```

### 3. **Update Detection**

Automatically handled in `admin.blade.php`:
- Detects new version
- Prompts user to reload
- Applies update seamlessly

---

## 📊 Testing Checklist

- [ ] Icons display correctly (all sizes)
- [ ] Manifest loads without errors
- [ ] Service Worker registers successfully
- [ ] App installs on desktop
- [ ] App installs on mobile (Android)
- [ ] App installs on mobile (iOS)
- [ ] Offline page shows when disconnected
- [ ] App works offline (cached pages)
- [ ] Install banner appears
- [ ] Theme color applies correctly
- [ ] Splash screen shows on launch
- [ ] App shortcuts work
- [ ] Update notification works

---

## 🐛 Troubleshooting

### Service Worker Not Registering

**Check:**
1. HTTPS is required (except localhost)
2. `sw.js` is in public root
3. No JavaScript errors in console
4. Clear browser cache

**Fix:**
```bash
# Clear service worker
# In DevTools Console:
navigator.serviceWorker.getRegistrations().then(registrations => {
    registrations.forEach(reg => reg.unregister());
});
```

### Manifest Not Loading

**Check:**
1. File exists at `/manifest.json`
2. Valid JSON format
3. MIME type is `application/manifest+json`

**Fix in `.htaccess` (if needed):**
```apache
<Files "manifest.json">
    Header set Content-Type "application/manifest+json"
</Files>
```

### Icons Not Showing

**Check:**
1. Files exist in `/public/images/icons/`
2. Correct file names and sizes
3. PNG format
4. Paths in manifest are correct

### App Not Installing

**Requirements:**
- HTTPS (or localhost)
- Valid manifest
- Service Worker registered
- At least 192x192 and 512x512 icons
- `start_url` must be valid

---

## 📱 Browser Support

| Feature | Chrome | Edge | Firefox | Safari |
|---------|--------|------|---------|--------|
| Install | ✅ | ✅ | ✅ | ✅ (iOS 11.3+) |
| Offline | ✅ | ✅ | ✅ | ✅ |
| Push | ✅ | ✅ | ✅ | ❌ |
| Background Sync | ✅ | ✅ | ❌ | ❌ |

---

## 🚀 Deployment

### Production Checklist

1. **Generate all icon sizes**
2. **Update manifest.json** with production URLs
3. **Test on HTTPS** (required for PWA)
4. **Verify Service Worker** caching strategy
5. **Test offline functionality**
6. **Test on multiple devices**
7. **Submit to app stores** (optional)

### Update Service Worker Version

When you make changes:
```javascript
// In sw.js
const CACHE_NAME = 'dxleave-v1.0.1'; // Increment version
```

This will trigger update for all users.

---

## 📚 Resources

- [PWA Builder](https://www.pwabuilder.com/)
- [Icon Generator](https://www.pwabuilder.com/imageGenerator)
- [Lighthouse PWA Audit](https://developers.google.com/web/tools/lighthouse)
- [Can I Use - PWA](https://caniuse.com/web-app-manifest)
- [MDN - Progressive Web Apps](https://developer.mozilla.org/en-US/docs/Web/Progressive_web_apps)

---

## 🎯 Next Steps

1. **Generate icons** for your app
2. **Test installation** on different devices
3. **Configure push notifications** (optional)
4. **Run Lighthouse audit** to check PWA score
5. **Deploy to production** with HTTPS

---

## ✨ Benefits of PWA

✅ **Installable** - Users can add to home screen  
✅ **Fast** - Cached assets load instantly  
✅ **Offline** - Works without internet  
✅ **Engaging** - Push notifications  
✅ **Reliable** - Always loads, even on flaky networks  
✅ **SEO Friendly** - Discoverable by search engines  
✅ **Cost Effective** - One codebase for all platforms  

Your DXLeave app is now a **Progressive Web App**! 🎉
