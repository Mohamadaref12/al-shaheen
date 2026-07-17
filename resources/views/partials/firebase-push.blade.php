{{-- Include on the public website to enable browser push --}}
@php
    $firebase = config('firebase.web');
@endphp

@if (filled($firebase['vapidKey'] ?? null))
    <script>
        window.__FIREBASE_CONFIG__ = @json(collect($firebase)->except('vapidKey'));
        window.__FIREBASE_VAPID_KEY__ = @json($firebase['vapidKey']);
    </script>
    <script src="{{ asset('js/firebase-push.js') }}?v=1" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.AlShaheenPush) return;

            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
            };

            const token = localStorage.getItem('auth_token') || localStorage.getItem('sanctum_token');
            if (token) {
                headers['Authorization'] = 'Bearer ' + token;
            }

            window.AlShaheenPush.init({
                platform: 'web',
                registerUrl: @json(url('/api/v1/devices/fcm-token')),
                vapidKey: window.__FIREBASE_VAPID_KEY__,
                locale: document.documentElement.lang || 'ar',
                headers: headers,
            }).catch(function (error) {
                console.warn('[AlShaheenPush] Website init failed', error);
            });
        });
    </script>
@endif
