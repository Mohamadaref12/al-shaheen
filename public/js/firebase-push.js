/**
 * Al Shaheen 360 — Firebase web push helper
 */
(function (window) {
    'use strict';

    const DEFAULTS = {
        apiKey: 'AIzaSyB2AdcZDtUNuu-g-odJJrWuWgZw4bZ89D4',
        authDomain: 'al-shaheen-360.firebaseapp.com',
        projectId: 'al-shaheen-360',
        storageBucket: 'al-shaheen-360.firebasestorage.app',
        messagingSenderId: '26049766031',
        appId: '1:26049766031:web:a910ed87bf4d7296df86fa',
    };

    function loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.async = true;
            script.onload = () => resolve();
            script.onerror = () => reject(new Error(`Failed to load ${src}`));
            document.head.appendChild(script);
        });
    }

    function firebaseAppConfig() {
        const raw = window.__FIREBASE_CONFIG__ || DEFAULTS;
        return {
            apiKey: raw.apiKey,
            authDomain: raw.authDomain,
            projectId: raw.projectId,
            storageBucket: raw.storageBucket,
            messagingSenderId: raw.messagingSenderId,
            appId: raw.appId,
        };
    }

    async function ensureFirebase() {
        if (!window.firebase) {
            await loadScript('https://www.gstatic.com/firebasejs/11.10.0/firebase-app-compat.js');
            await loadScript('https://www.gstatic.com/firebasejs/11.10.0/firebase-messaging-compat.js');
        }

        if (!window.firebase.apps.length) {
            window.firebase.initializeApp(firebaseAppConfig());
        }

        return window.firebase.messaging();
    }

    async function registerServiceWorker() {
        if (!('serviceWorker' in navigator)) {
            throw new Error('Service workers are not supported in this browser.');
        }

        if (!window.isSecureContext) {
            throw new Error(
                'Push requires HTTPS. Open https://al-shaheen.test/admin (not http://).'
            );
        }

        return navigator.serviceWorker.register('/firebase-messaging-sw.js');
    }

    async function postToken(url, token, platform, locale, headers = {}) {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                ...headers,
            },
            body: JSON.stringify({ token, platform, locale }),
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error(`Token registration failed (${response.status}): ${text}`);
        }

        return response.json();
    }

    async function init(options = {}) {
        const config = {
            platform: 'web',
            registerUrl: '/api/v1/devices/fcm-token',
            vapidKey: window.__FIREBASE_VAPID_KEY__ || null,
            locale: document.documentElement.lang || 'ar',
            headers: {},
            ...options,
        };

        if (!config.vapidKey) {
            console.warn('[AlShaheenPush] Missing VAPID key.');
            return null;
        }

        if (!('Notification' in window)) {
            console.warn('[AlShaheenPush] Notifications API unavailable.');
            return null;
        }

        if (!window.isSecureContext) {
            console.error('[AlShaheenPush] Not a secure context. Use HTTPS.');
            throw new Error('Use HTTPS: https://al-shaheen.test/admin');
        }

        const permission = await Notification.requestPermission();

        if (permission !== 'granted') {
            console.info('[AlShaheenPush] Permission not granted:', permission);
            return null;
        }

        await registerServiceWorker();
        const messaging = await ensureFirebase();
        const registration = await navigator.serviceWorker.ready;

        const token = await messaging.getToken({
            vapidKey: config.vapidKey,
            serviceWorkerRegistration: registration,
        });

        if (!token) {
            console.warn('[AlShaheenPush] No FCM token returned.');
            return null;
        }

        await postToken(config.registerUrl, token, config.platform, config.locale, config.headers);
        console.info('[AlShaheenPush] Device registered for', config.platform);

        messaging.onMessage((payload) => {
            const title = payload.notification?.title || payload.data?.title || 'Al Shaheen 360';
            const body = payload.notification?.body || payload.data?.body || '';
            const url = payload.data?.url || payload.fcmOptions?.link;

            if (Notification.permission === 'granted') {
                const notification = new Notification(title, {
                    body,
                    icon: '/al-shaheen.png',
                    data: { url },
                });

                notification.onclick = () => {
                    if (url) {
                        window.focus();
                        window.location.href = url;
                    }
                };
            }

            window.dispatchEvent(new CustomEvent('alshaheen:push', { detail: payload }));
        });

        return token;
    }

    window.AlShaheenPush = { init };
})(window);
