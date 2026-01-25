// This Service Worker is DEPRECATED.
// It will unregister itself immediately to allow worker.js to take over.

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', () => {
    self.registration.unregister()
        .then(() => console.log('Old SW unregistered itself.'))
        .catch(e => console.error('Failed to unregister old SW:', e));
});
