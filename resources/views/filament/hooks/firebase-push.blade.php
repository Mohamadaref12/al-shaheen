@php
    $firebase = config('firebase.web');
    $clientConfig = collect($firebase)->except('vapidKey')->all();
@endphp

@if (config('firebase.enabled') && filled($firebase['vapidKey'] ?? null))
    <script>
        window.__FIREBASE_CONFIG__ = @json($clientConfig);
        window.__FIREBASE_VAPID_KEY__ = @json($firebase['vapidKey']);
    </script>
    <script src="{{ asset('js/firebase-push.js') }}?v=2"></script>
    <script>
        (function () {
            function csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.content
                    || @json(csrf_token());
            }

            function bootPush() {
                if (!window.AlShaheenPush || window.__AL_SHAHEEN_PUSH_BOOTED__) {
                    return;
                }

                window.__AL_SHAHEEN_PUSH_BOOTED__ = true;

                window.AlShaheenPush.init({
                    platform: 'dashboard',
                    registerUrl: @json(route('admin.fcm.register')),
                    vapidKey: window.__FIREBASE_VAPID_KEY__,
                    locale: document.documentElement.lang || 'en',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken(),
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }).then(function (token) {
                    if (token) {
                        console.info('[AlShaheenPush] Dashboard device ready');
                    }
                }).catch(function (error) {
                    console.warn('[AlShaheenPush] Dashboard init failed', error);
                    window.__AL_SHAHEEN_PUSH_BOOTED__ = false;
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootPush);
            } else {
                bootPush();
            }

            document.addEventListener('livewire:navigated', function () {
                window.__AL_SHAHEEN_PUSH_BOOTED__ = false;
                bootPush();
            });
        })();
    </script>
@endif
