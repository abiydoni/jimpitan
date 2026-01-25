const CACHE_NAME = 'jimpitan-fcm-v8';
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
      console.log('SW DEBUG RAW:', JSON.stringify(rawData)); // DEBUG LOG
      
      const data = rawData.data || rawData || {}; // Never allow undefined

      // Ultra-Defensive Checks
      // Ultra-Defensive Checks
      if (!data || Object.keys(data).length === 0) {
          console.warn('SW: Received Empty Data Push');
          return;
      }
      
      // SW DRIVEN (Persistent + Clickable)
      // We removed 'hide_in_sw' check to allow SW to handle everything.

      const title = data.title || 'Jimpitan App'; // Fallback to avoid crash
      const tag = data.tag || 'jimpitan-chat';
      const renotify = (data.renotify === 'true' || data.renotify === true);
      const requireInteraction = true; // FORCE STICKY
      
      // Auto-Close: Default 0 (Never close)
      let autoCloseMs = 0; 
      if (typeof data.auto_close !== 'undefined' && data.auto_close !== null) {
           const parsed = parseInt(data.auto_close);
           if (!isNaN(parsed)) {
               autoCloseMs = parsed;
           }
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
  
  // Defensive: Get URL safely
  let targetUrl = '/'; // Default fallback
  if (event.notification.data && event.notification.data.url) {
      targetUrl = event.notification.data.url;
  }

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(windowClients => {
      // Check if there is already a window open with this URL
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client && client.url && client.url.includes(targetUrl) && 'focus' in client) {
          return client.focus();
        }
      }
      // If not, open a new window
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
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
