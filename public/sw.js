const CACHE_NAME = 'jimpitan-fcm-v1';
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
messaging.setBackgroundMessageHandler(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);
  // Customize notification here
  const notificationTitle = payload.notification.title || 'Pesan Baru';
  const notificationOptions = {
    body: payload.notification.body,
    icon: '/favicon.ico',
    tag: 'jimpitan-global',
    data: payload.data
  };

  return self.registration.showNotification(notificationTitle,
    notificationOptions);
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

// -- PUSH NOTIFICATION HANDLERS --

self.addEventListener('push', function(event) {
  if (!(self.Notification && self.Notification.permission === 'granted')) {
    return;
  }

  const rawData = event.data ? event.data.json() : {};
  console.log('Push Received (fcm-v1):', rawData);

  // Normalize data (FCM puts fields inside 'data' property if sent as data message)
  const data = rawData.data ? rawData.data : rawData;

  const title = data.title || 'Pesan Baru';
  const options = {
    body: data.body || 'Anda memiliki pesan baru.',
    icon: self.location.origin + '/favicon.ico',
    badge: self.location.origin + '/favicon.ico',
    vibrate: [200, 100, 200, 100, 200], 
    tag: 'jimpitan-global',     // Stable tag
    renotify: true,             // Force popup on every push
    silent: false,
    timestamp: Date.now(),
    actions: [
        { action: 'open_chat', title: 'Buka Chat' }
    ],
    data: {
        url: data.url || '/chat'
    }
  };

  event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function(event) {
  event.notification.close();
  event.waitUntil(
    clients.matchAll({ type: 'window' }).then(windowClients => {
      // Check if there is already a window open with this URL
      for (var i = 0; i < windowClients.length; i++) {
        var client = windowClients[i];
        if (client.url === event.notification.data.url && 'focus' in client) {
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
