// Minimal fetch handler, registered FIRST and unconditionally. Its mere presence
// is required for the browser to treat the site as installable ("Add to Home
// Screen" / native install prompt). Registering it before any Firebase code runs
// guarantees the service worker is always valid/installable even if the Firebase
// SDK fails to load or initialise. It is a no-op — network behaviour is unchanged.
self.addEventListener('fetch', function (event) {
  // let the request proceed to the network as normal
});

// Take control of open pages as soon as the SW activates (helps the install
// heuristic and background messaging attach without a manual reload).
self.addEventListener('install', function () { self.skipWaiting(); });
self.addEventListener('activate', function (event) { event.waitUntil(self.clients.claim()); });

// Firebase background messaging. Wrapped in try/catch so any SDK/init error can
// never break the service worker (which would make the site non-installable).
try {
  importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
  importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

  firebase.initializeApp({
    apiKey: "YOUR_API_KEY",
    projectId: "ebooking-b2c07",
    messagingSenderId: "100739474622",
    appId: "YOUR_APP_ID"
  });

  const messaging = firebase.messaging();

  messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const title = (payload.notification && payload.notification.title) || (payload.data && payload.data.title) || 'Notification';
    const body = (payload.notification && payload.notification.body) || (payload.data && payload.data.body) || '';

    // Play sound in open clients if there are any
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clients) {
      clients.forEach(function (client) {
        client.postMessage({
          type: 'PLAY_NOTIFICATION_SOUND',
          title: title,
          body: body
        });
      });
    });

    // Note: We do NOT call self.registration.showNotification here because the Firebase SDK
    // automatically detects the 'notification' payload in the message and displays the
    // notification in the background to prevent duplicates.
  });
} catch (e) {
  console.error('[firebase-messaging-sw.js] Firebase init failed (SW still valid):', e);
}
