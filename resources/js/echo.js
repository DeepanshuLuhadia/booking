import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

/*
 * Realtime transport — Laravel Reverb, self-hosted (php artisan reverb:start).
 *
 * Everything below degrades quietly. If the socket server is not running, or
 * this bundle fails to load, Echo simply never connects: pages must not depend
 * on it for correctness. That is why each screen keeps a slow poll as a
 * fallback and only steps it down while `Realtime.connected()` is true.
 */
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});

const pending = new Map();

window.Realtime = {
    /** Is the socket actually up? Drives whether a page still needs to poll. */
    connected() {
        return window.Echo?.connector?.pusher?.connection?.state === 'connected';
    },

    /**
     * Re-render a server-rendered region in place.
     *
     * The dashboards are Blade, not client-side templates, so rather than
     * duplicating their markup in JavaScript we re-fetch the page and swap the
     * one element that changed. The server stays the single source of truth for
     * how a queue looks, and Alpine re-initialises the new nodes by itself via
     * its MutationObserver.
     *
     * Calls are debounced: one action commonly emits both a BookingChanged and
     * a QueueUpdated, and that should cost one fetch, not two.
     */
    refresh(selector, delay = 250) {
        clearTimeout(pending.get(selector));

        pending.set(selector, setTimeout(async () => {
            const target = document.querySelector(selector);
            if (!target) return;

            try {
                const res = await fetch(window.location.href, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin',
                });
                if (!res.ok) return;

                const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
                const fresh = doc.querySelector(selector);

                // Still there on re-render, and the user is not mid-interaction
                // inside it — swapping the node under an open <select> or a
                // focused input would throw away what they were doing.
                if (!fresh) return;
                if (target.contains(document.activeElement) && document.activeElement !== document.body) return;

                target.replaceWith(fresh);
            } catch (e) {
                console.error('Live refresh failed', e);
            }
        }, delay));
    },

    /** Small toast, reusing the one the app layout already listens for. */
    toast(message, type = 'info') {
        window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
    },
};
