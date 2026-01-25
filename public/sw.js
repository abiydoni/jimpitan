const CACHE_NAME = 'jimpitan-fcm-v7';
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

// Handle Background Messages - DEFENSIVE SERVER DRIVEN
self.addEventListener('push', function(event) {
  console.log('[SW] Push Received (Defensive)');
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  try {
      const rawData = event.data ? event.data.json() : {};
      const data = rawData.data || rawData || {}; // Never allow undefined

      // Ultra-Defensive Checks
      if (!data || Object.keys(data).length === 0) {
          console.warn('SW: Received Empty Data Push');
          return;
      }

      const title = data.title || 'Jimpitan App'; // Fallback to avoid crash
      const tag = data.tag || 'jimpitan-chat';
      const renotify = (data.renotify === 'true' || data.renotify === true);
      const requireInteraction = (data.require_interaction === 'true' || data.require_interaction === true); 
      
      // Auto-Close: Parse safely
      let autoCloseMs = 5000;
      if (data.auto_close) {
           autoCloseMs = parseInt(data.auto_close) || 5000;
      }

      // Assets
      const icon = data.icon || '/favicon.ico';
      const badge = data.badge || '/favicon.ico';
      const sound = data.sound || 'default';
      
      let vibratePattern = [200, 100, 200];
      try {
          if (data.vibrate) vibratePattern = JSON.parse(data.vibrate);
      } catch(e) { }

      const options = {
        body: data.body || 'Pesan Baru',
        icon: icon,
        badge: badge,
        sound: sound,
        tag: tag,     
        renotify: renotify,             
        vibrate: vibratePattern,
        requireInteraction: requireInteraction,
        data: { url: data.url || '/chat' }
      };

      const notificationPromise = self.registration.showNotification(title, options);
      
      let closePromise = Promise.resolve();
      if (autoCloseMs > 0) {
          closePromise = new Promise((resolve) => {
            setTimeout(() => {
                self.registration.getNotifications({ tag: tag })
                    .then(notifications => {
                        notifications.forEach(notification => notification.close());
                        resolve();
                    });
            }, autoCloseMs);
          });
      }

      event.waitUntil(Promise.all([notificationPromise, closePromise]));

  } catch (err) {
      console.error('SW: Fatal Crash Prevented', err);
  }
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      // Check if there is already a window open with this URL
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client.url.includes(event.notification.data.url) && 'focus' in client) {
          return client.focus();
        }
      }
      // If not, open a new window
      if (clients.openWindow) {
        return clients.openWindow(event.notification.data.url);
      }
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
          if (event.request.mode === 'navigate') {
            return caches.match('./offline.html');
          }
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
