import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// IMPORTANT: always send cookies (session) with requests
window.axios.defaults.withCredentials = true;

// (optional but recommended) if you have a meta csrf token for web routes
const tokenEl = document.querySelector('meta[name="csrf-token"]');
if (tokenEl) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = tokenEl.getAttribute('content');
}

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import './echo';
