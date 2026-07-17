/* Al Shaheen 360 — Firebase Cloud Messaging service worker */
/* global importScripts, firebase */

importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js');

firebase.initializeApp({
    apiKey: 'AIzaSyB2AdcZDtUNuu-g-odJJrWuWgZw4bZ89D4',
    authDomain: 'al-shaheen-360.firebaseapp.com',
    projectId: 'al-shaheen-360',
    storageBucket: 'al-shaheen-360.firebasestorage.app',
    messagingSenderId: '26049766031',
    appId: '1:26049766031:web:a910ed87bf4d7296df86fa',
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage((payload) => {
    const title = payload.notification?.title || payload.data?.title || 'Al Shaheen 360';
    const options = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: payload.notification?.icon || '/al-shaheen.png',
        badge: '/al-shaheen.png',
        data: {
            url: payload.data?.url || payload.fcmOptions?.link || '/admin',
            ...(payload.data || {}),
        },
        requireInteraction: true,
    };

    return self.registration.showNotification(title, options);
});

self.addEventListener('push', (event) => {
    if (!event.data) {
        return;
    }

    let payload = {};
    try {
        payload = event.data.json();
    } catch (e) {
        payload = { data: { body: event.data.text() } };
    }

    const title = payload.notification?.title || payload.data?.title || 'Al Shaheen 360';
    const options = {
        body: payload.notification?.body || payload.data?.body || '',
        icon: '/al-shaheen.png',
        data: payload.data || {},
        requireInteraction: true,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const url = event.notification.data?.url || '/admin';

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(url);
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
