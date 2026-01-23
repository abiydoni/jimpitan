const CACHE_NAME = 'jimpitan-v14';
const urlsToCache = [
  './offline.html',
];

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
        return fetch(event.request);
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

  const data = event.data ? event.data.json() : {};
  console.log('Push Received:', data);

  const title = data.title || 'Pesan Baru';
  const options = {
    body: data.body || 'Anda memiliki pesan baru.',
    icon: self.location.origin + '/favicon.ico',
    badge: self.location.origin + '/favicon.ico',
    image: self.location.origin + '/favicon.ico',
    vibrate: [500, 110, 500, 110, 450, 110, 200, 110], // Long aggressive vibration
    // NO fixed tag - making every message unique to force banner/popup
    renotify: false,
    requireInteraction: true,
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
