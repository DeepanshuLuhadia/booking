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
    apiKey: "AIzaSyCr7XU5EysDj-D361wrTdskzZr5d5vb49A",
    authDomain: "apni-baari-6d2b0.firebaseapp.com",
    projectId: "apni-baari-6d2b0",
    storageBucket: "apni-baari-6d2b0.firebasestorage.app",
    messagingSenderId: "831015537203",
    appId: "1:831015537203:web:ccb2d4196559a23349f146"
  });

  const messaging = firebase.messaging();

  // Messages are sent data-only (see FcmService::sendToToken) precisely so that
  // nothing is auto-displayed by the browser/SDK — we own display AND click
  // navigation here, end to end, instead of depending on FCM's undocumented
  // default click behaviour (which only ever opens '/').
  messaging.onBackgroundMessage(function (payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);

    const data = payload.data || {};
    const title = data.title || 'Notification';
    const body = data.body || '';
    const url = data.url || '/';

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

    return self.registration.showNotification(title, {
      body: body,
      icon: '/favicon.ico',
      data: { url: url }
    });
  });
} catch (e) {
  console.error('[firebase-messaging-sw.js] Firebase init failed (SW still valid):', e);
}

// The single owner of "what happens when a background notification is
// tapped" — opens the page the notification is actually about (a booking,
// a vendor's queue, an admin record) instead of just focusing/opening the
// site root. Guest customers get this too: their push carries the same
// `url` (built server-side in NotificationService) as a signed-in user's.
self.addEventListener('notificationclick', function (event) {
  event.notification.close();

  const url = (event.notification.data && event.notification.data.url) || '/';

  event.waitUntil(
    self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clients) {
      for (const client of clients) {
        // Reuse an already-open tab on the same origin rather than piling up
        // new ones — most vendors keep the dashboard open all day.
        if (client.url && new URL(client.url).origin === self.location.origin && 'focus' in client) {
          client.focus();
          if ('navigate' in client) {
            return client.navigate(url);
          }
          return;
        }
      }
      return self.clients.openWindow(url);
    })
  );
});
