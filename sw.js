/**
 * FWP Image Gallery - Service Worker
 * Provides offline support and caching for PWA
 */

const CACHE_VERSION = 'v1.0.0';
const STATIC_CACHE = `fwp-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `fwp-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `fwp-images-${CACHE_VERSION}`;

// Static assets to cache on install
const STATIC_ASSETS = [
  '/',
  '/offline',
  '/manifest.json',
  '/assets/css/app.css',
  '/assets/js/app.js',
  // Add your main CSS/JS files here
];

// Cache size limits
const MAX_DYNAMIC_CACHE = 50;
const MAX_IMAGE_CACHE = 100;

/**
 * Install event - cache static assets
 */
self.addEventListener('install', (event) => {
  console.log('[SW] Installing service worker...');

  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => {
        console.log('[SW] Caching static assets');
        return cache.addAll(STATIC_ASSETS).catch((err) => {
          console.warn('[SW] Some static assets failed to cache:', err);
        });
      })
      .then(() => self.skipWaiting())
  );
});

/**
 * Activate event - clean up old caches
 */
self.addEventListener('activate', (event) => {
  console.log('[SW] Activating service worker...');

  event.waitUntil(
    caches.keys()
      .then((cacheNames) => {
        return Promise.all(
          cacheNames
            .filter((name) => {
              return name.startsWith('fwp-') &&
                     name !== STATIC_CACHE &&
                     name !== DYNAMIC_CACHE &&
                     name !== IMAGE_CACHE;
            })
            .map((name) => {
              console.log('[SW] Deleting old cache:', name);
              return caches.delete(name);
            })
        );
      })
      .then(() => self.clients.claim())
  );
});

/**
 * Fetch event - handle requests with caching strategies
 */
self.addEventListener('fetch', (event) => {
  const { request } = event;
  const url = new URL(request.url);

  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }

  // Skip cross-origin requests (except CDN assets)
  if (url.origin !== location.origin && !isTrustedCDN(url)) {
    return;
  }

  // Skip admin and API routes
  if (url.pathname.startsWith('/admin') || url.pathname.startsWith('/api/')) {
    return;
  }

  // Image requests - Cache First with network fallback
  if (isImageRequest(request)) {
    event.respondWith(cacheFirstWithRefresh(request, IMAGE_CACHE, MAX_IMAGE_CACHE));
    return;
  }

  // Static assets (CSS, JS, fonts) - Cache First
  if (isStaticAsset(request)) {
    event.respondWith(cacheFirst(request, STATIC_CACHE));
    return;
  }

  // HTML pages - Network First with cache fallback
  if (request.headers.get('accept')?.includes('text/html')) {
    event.respondWith(networkFirstWithOffline(request));
    return;
  }

  // Everything else - Network First with cache
  event.respondWith(networkFirst(request, DYNAMIC_CACHE, MAX_DYNAMIC_CACHE));
});

/**
 * Check if request is for an image
 */
function isImageRequest(request) {
  const url = new URL(request.url);
  return url.pathname.startsWith('/uploads/') ||
         request.destination === 'image' ||
         /\.(jpg|jpeg|png|gif|webp|svg|ico)$/i.test(url.pathname);
}

/**
 * Check if request is for a static asset
 */
function isStaticAsset(request) {
  const url = new URL(request.url);
  return url.pathname.startsWith('/assets/') ||
         /\.(css|js|woff|woff2|ttf|eot)$/i.test(url.pathname);
}

/**
 * Check if URL is from a trusted CDN
 */
function isTrustedCDN(url) {
  const trustedHosts = [
    'fonts.googleapis.com',
    'fonts.gstatic.com',
    'cdnjs.cloudflare.com',
    'unpkg.com',
    'cdn.jsdelivr.net'
  ];
  return trustedHosts.some(host => url.hostname.includes(host));
}

/**
 * Cache First strategy
 */
async function cacheFirst(request, cacheName) {
  const cached = await caches.match(request);
  if (cached) {
    return cached;
  }

  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    console.error('[SW] Fetch failed:', error);
    throw error;
  }
}

/**
 * Cache First with background refresh
 */
async function cacheFirstWithRefresh(request, cacheName, maxItems) {
  const cached = await caches.match(request);

  // Fetch in background to refresh cache
  const fetchPromise = fetch(request)
    .then(async (response) => {
      if (response.ok) {
        const cache = await caches.open(cacheName);
        cache.put(request, response.clone());
        await trimCache(cacheName, maxItems);
      }
      return response;
    })
    .catch(() => null);

  // Return cached version immediately, or wait for network
  return cached || fetchPromise;
}

/**
 * Network First strategy with cache fallback
 */
async function networkFirst(request, cacheName, maxItems) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(cacheName);
      cache.put(request, response.clone());
      await trimCache(cacheName, maxItems);
    }
    return response;
  } catch (error) {
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }
    throw error;
  }
}

/**
 * Network First with offline fallback for HTML pages
 */
async function networkFirstWithOffline(request) {
  try {
    const response = await fetch(request);
    if (response.ok) {
      const cache = await caches.open(DYNAMIC_CACHE);
      cache.put(request, response.clone());
    }
    return response;
  } catch (error) {
    // Try cache first
    const cached = await caches.match(request);
    if (cached) {
      return cached;
    }

    // Return offline page
    const offlinePage = await caches.match('/offline');
    if (offlinePage) {
      return offlinePage;
    }

    // Last resort - return a simple offline response
    return new Response(
      '<!DOCTYPE html><html><head><title>Offline</title></head><body><h1>You are offline</h1><p>Please check your internet connection.</p></body></html>',
      { headers: { 'Content-Type': 'text/html' } }
    );
  }
}

/**
 * Trim cache to max size (LRU-style)
 */
async function trimCache(cacheName, maxItems) {
  const cache = await caches.open(cacheName);
  const keys = await cache.keys();

  if (keys.length > maxItems) {
    // Delete oldest entries
    const deleteCount = keys.length - maxItems;
    for (let i = 0; i < deleteCount; i++) {
      await cache.delete(keys[i]);
    }
  }
}

/**
 * Handle background sync for offline uploads
 */
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-uploads') {
    event.waitUntil(syncUploads());
  }
});

/**
 * Sync pending uploads when back online
 */
async function syncUploads() {
  // This would handle queued uploads from IndexedDB
  // Implementation depends on your upload queue system
  console.log('[SW] Syncing pending uploads...');
}

/**
 * Handle push notifications
 */
self.addEventListener('push', (event) => {
  if (!event.data) return;

  const data = event.data.json();

  const options = {
    body: data.body || 'New notification',
    icon: '/assets/icons/icon-192x192.png',
    badge: '/assets/icons/icon-72x72.png',
    vibrate: [100, 50, 100],
    data: {
      url: data.url || '/'
    },
    actions: data.actions || []
  };

  event.waitUntil(
    self.registration.showNotification(data.title || 'FWP Gallery', options)
  );
});

/**
 * Handle notification clicks
 */
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const url = event.notification.data?.url || '/';

  event.waitUntil(
    clients.matchAll({ type: 'window' })
      .then((clientList) => {
        // Focus existing window if available
        for (const client of clientList) {
          if (client.url === url && 'focus' in client) {
            return client.focus();
          }
        }
        // Open new window
        if (clients.openWindow) {
          return clients.openWindow(url);
        }
      })
  );
});

/**
 * Handle share target (receiving shared images)
 */
self.addEventListener('fetch', (event) => {
  const url = new URL(event.request.url);

  if (url.pathname === '/upload' && event.request.method === 'POST') {
    event.respondWith(handleShare(event.request));
  }
});

async function handleShare(request) {
  const formData = await request.formData();
  const image = formData.get('image');

  if (image) {
    // Store in IndexedDB for later upload
    // Or redirect to upload page with the file
    const url = new URL('/upload', location.origin);
    url.searchParams.set('shared', '1');

    return Response.redirect(url.toString(), 303);
  }

  return fetch(request);
}

console.log('[SW] Service worker loaded');
