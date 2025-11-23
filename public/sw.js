const CACHE_NAME = 'dxleave-v1.0.0';
const STATIC_CACHE = 'dxleave-static-v1.0.0';
const DYNAMIC_CACHE = 'dxleave-dynamic-v1.0.0';

// Assets to cache on install
const STATIC_ASSETS = [
    '/',
    '/css/app.css',
    '/js/app.js',
    '/manifest.json',
    '/offline.html',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installing...');
    event.waitUntil(
        caches.open(STATIC_CACHE)
            .then((cache) => {
                console.log('[Service Worker] Caching static assets');
                return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
            })
            .catch((error) => {
                console.error('[Service Worker] Cache failed:', error);
            })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activating...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE) {
                        console.log('[Service Worker] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip cross-origin requests
    if (url.origin !== location.origin) {
        return;
    }

    // Skip API requests (always fetch fresh)
    if (url.pathname.startsWith('/api/')) {
        event.respondWith(
            fetch(request)
                .catch(() => {
                    return new Response(
                        JSON.stringify({
                            success: false,
                            message: 'You are offline. Please check your internet connection.'
                        }),
                        {
                            headers: { 'Content-Type': 'application/json' },
                            status: 503
                        }
                    );
                })
        );
        return;
    }

    // Network first, fallback to cache strategy
    event.respondWith(
        fetch(request)
            .then((response) => {
                // Clone the response
                const responseClone = response.clone();

                // Cache successful responses
                if (response.status === 200) {
                    caches.open(DYNAMIC_CACHE).then((cache) => {
                        cache.put(request, responseClone);
                    });
                }

                return response;
            })
            .catch(() => {
                // Try to get from cache
                return caches.match(request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }

                    // If HTML page and not in cache, show offline page
                    if (request.headers.get('accept').includes('text/html')) {
                        return caches.match('/offline.html');
                    }

                    // Return a generic offline response
                    return new Response('Offline', {
                        status: 503,
                        statusText: 'Service Unavailable'
                    });
                });
            })
    );
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    console.log('[Service Worker] Background sync:', event.tag);

    if (event.tag === 'sync-leave-requests') {
        event.waitUntil(syncLeaveRequests());
    }
});

// Push notification
self.addEventListener('push', (event) => {
    console.log('[Service Worker] Push received');

    const data = event.data ? event.data.json() : {};
    const title = data.title || 'DXLeave Notification';
    const options = {
        body: data.body || 'You have a new notification',
        icon: '/images/icons/icon-192x192.png',
        badge: '/images/icons/icon-72x72.png',
        vibrate: [200, 100, 200],
        data: data.data || {},
        actions: data.actions || []
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Notification clicked');

    event.notification.close();

    const urlToOpen = event.notification.data.url || '/';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Check if there's already a window open
                for (let client of clientList) {
                    if (client.url === urlToOpen && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Open new window
                if (clients.openWindow) {
                    return clients.openWindow(urlToOpen);
                }
            })
    );
});

// Helper function for syncing leave requests
async function syncLeaveRequests() {
    try {
        // Get pending requests from IndexedDB
        const db = await openDB();
        const pendingRequests = await db.getAll('pending-requests');

        // Sync each request
        for (const request of pendingRequests) {
            try {
                const response = await fetch(request.url, {
                    method: request.method,
                    headers: request.headers,
                    body: request.body
                });

                if (response.ok) {
                    // Remove from pending
                    await db.delete('pending-requests', request.id);
                }
            } catch (error) {
                console.error('[Service Worker] Sync failed for request:', error);
            }
        }
    } catch (error) {
        console.error('[Service Worker] Sync error:', error);
    }
}

// Helper to open IndexedDB
function openDB() {
    return new Promise((resolve, reject) => {
        const request = indexedDB.open('dxleave-db', 1);

        request.onerror = () => reject(request.error);
        request.onsuccess = () => resolve(request.result);

        request.onupgradeneeded = (event) => {
            const db = event.target.result;
            if (!db.objectStoreNames.contains('pending-requests')) {
                db.createObjectStore('pending-requests', { keyPath: 'id', autoIncrement: true });
            }
        };
    });
}
