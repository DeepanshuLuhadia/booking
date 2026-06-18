importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js');

// Initialize the Firebase app in the service worker by passing in the
// messagingSenderId.
// Update these variables based on the generated config from Firebase Console.
// For this application, we are relying on a generated FCM sender id.
firebase.initializeApp({
  apiKey: "YOUR_API_KEY",
  projectId: "ebooking-b2c07",
  messagingSenderId: "100739474622",
  appId: "YOUR_APP_ID"
});

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.onBackgroundMessage(function(payload) {
  console.log('[firebase-messaging-sw.js] Received background message ', payload);

  const title = (payload.notification && payload.notification.title) || (payload.data && payload.data.title) || 'Notification';
  const body = (payload.notification && payload.notification.body) || (payload.data && payload.data.body) || '';

  // Play sound in open clients if there are any
  self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function(clients) {
    clients.forEach(function(client) {
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
