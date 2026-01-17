const CACHE_NAME = 'jimpitan-v7';
const urlsToCache = [
  './offline.html',
  // External CDNs removed to prevent CORS issues
];

self.addEventListener('install', event => {
  self.skipWaiting(); // Force activation
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Opened cache');
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  // Navigation requests (HTML pages) -> Network First, fall back to cache/offline
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.match('./offline.html');
        })
    );
    return;
  }

  // Other requests (Images, CSS, JS) -> Cache First, fall back to network
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(err => {
            console.log('Fetch failed:', event.request.url);
            // Optionally could return an offline placeholder image here if request was for an image
            return new Response('Network error happening', {
                status: 408,
                headers: { 'Content-Type': 'text/plain' }
            }); 
        });
      })
  );
});

self.addEventListener('activate', event => {
  const cacheWhitelist = [CACHE_NAME];
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheWhitelist.indexOf(cacheName) === -1) {
            return caches.delete(cacheName);
          }
        })
      );
    })
    .then(() => self.clients.claim()) // Claim clients immediately
  );
});
