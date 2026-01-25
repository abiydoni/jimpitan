const CACHE_NAME = 'jimpitan-fcm-v2';
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

const urlsToCache = [
  './offline.html',
];

// Initialize Firebase in SW (Required for background handler)
firebase.initializeApp({
    apiKey: "AIzaSyCMO1z8UGvFNyOnzAV-dsx1VLjOtCAjtdc",
    authDomain: "jimpitan-app-a7by777.firebaseapp.com",
    projectId: "jimpitan-app-a7by777",
    storageBucket: "jimpitan-app-a7by777.firebasestorage.app",
    messagingSenderId: "53228839762",
    appId: "1:53228839762:web:ae75cb6fc64b9441ac108b",
    measurementId: "G-XG704TQRJ2"
});

const messaging = firebase.messaging();

// Handle Background Messages explicitly
// Handle Background Messages via 'push' event for granular control (Anti-Duplicate)
self.addEventListener('push', function(event) {
  console.log('[SW] Push Received');
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  const rawData = event.data ? event.data.json() : {};
  // Normalize: data-only payload puts everything in rawData.data or rawData direct
  const data = rawData.data || rawData;

  const title = data.title || 'Pesan Baru';
  const options = {
    body: data.body || 'Anda memiliki pesan baru.',
    icon: '/favicon.ico',
    badge: '/favicon.ico',
    tag: 'jimpitan-chat',     
    renotify: true,             
    vibrate: [200, 100, 200],
    data: { url: data.url || '/chat' }
  };

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      // 1. CHECK VISIBILITY
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client.visibilityState === 'visible') {
            console.log('[SW] App is visible. Suppressing background notification.');
            return; // EXIT: Let the foreground app handle it!
        }
      }

      // 2. SHOW NOTIFICATION (Only if app is background/closed)
      return self.registration.showNotification(title, options);
    })
  );
});

self.addEventListener('install', event => {
  self.skipWaiting();
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache);
      })
  );
});

self.addEventListener('fetch', event => {
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          return caches.match('./offline.html');
        })
    );
    return;
  }

  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        return fetch(event.request).catch(err => {
          // Fallback silently for resource fetch failures (like favicon or local dev issues)
          console.log('SW: Fetch failed for:', event.request.url);
          if (event.request.mode === 'navigate') {
            return caches.match('./offline.html');
          }
          // For other assets, if it's not critical, just let it fail
          return new Response('', { status: 404, statusText: 'Not Found' });
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
    .then(() => self.clients.claim())
  );
});

// Manual 'push' listener removed to avoid conflict with Firebase SDK (setBackgroundMessageHandler)
// Firebase SDK handles the notification display automatically for 'notification' triggers.

