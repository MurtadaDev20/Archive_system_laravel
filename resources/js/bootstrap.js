import 'bootstrap';

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Echo + Laravel WebSockets (Pusher محلي عبر PHP على 127.0.0.1:6001)
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const scheme = import.meta.env.VITE_PUSHER_SCHEME ?? 'http';
const host = import.meta.env.VITE_PUSHER_HOST ?? '127.0.0.1';
const port = Number(import.meta.env.VITE_PUSHER_PORT ?? 6001);
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: host,
    wsPort: port,
    wssPort: port,
    forceTLS: scheme === 'https',
    encrypted: scheme === 'https',
    disableStats: true,
    enabledTransports: ['ws', 'wss'],
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            Accept: 'application/json',
        },
    },
});
