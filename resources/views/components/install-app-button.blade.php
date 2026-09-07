{{--
    "Install App" nav item — reused in the vendor, admin and employee sidebars
    (both the desktop <aside> and the mobile slide-in menu use the same markup
    already, so one component covers both via $variant), and in the public
    site's guest/customer hamburger menu ($variant="guest") — that menu has no
    persistent sidebar of its own, so guests would otherwise never see this.

    Relies on globals already set up in app-layout.blade.php's <head>:
      - window.__deferredInstallPrompt — the captured `beforeinstallprompt`
        event, or null.
      - window.__isStandalone() — true once the site is running installed.
    Nothing here re-registers those listeners; it only reads them and reacts
    to the `installprompt-available` / `appinstalled` events they dispatch.

    The click ALWAYS aims for the browser's own install dialog. The written
    steps are a last resort, not a first answer: on a first visit the service
    worker only registers on `load`, so Chrome commonly decides the site is
    installable a second or two AFTER the page looks ready. Tapping in that
    window used to drop the visitor straight onto "find your browser menu" —
    the one thing this button exists to spare them. So a tap with no prompt
    in hand waits briefly for one and fires it the moment it lands.
--}}
@props(['variant' => 'desktop'])
@php
    $itemClass = match ($variant) {
        'mobile' => 'flex items-center gap-4 px-6 py-4 rounded-2xl transition-all text-slate-300 hover:bg-white/5/50 w-full text-left disabled:opacity-50',
        'guest'  => 'flex items-center gap-4 px-6 py-4 rounded-2xl bg-white/5 text-white font-black italic uppercase tracking-widest text-[11px] shadow-sm w-full text-left disabled:opacity-50',
        default  => 'flex items-center gap-4 p-4 rounded-2xl transition-all duration-300 text-slate-400 hover:bg-white/5 hover:translate-x-1 w-full text-left disabled:opacity-50',
    };
    $iconClass = $variant === 'desktop' ? 'h-5 w-5' : 'h-5 w-5 text-slate-400';
    $labelClass = match ($variant) {
        'guest'  => '',
        'mobile' => 'font-black italic uppercase tracking-widest text-[11px] whitespace-nowrap',
        default  => 'font-black italic uppercase tracking-widest text-[10px] whitespace-nowrap',
    };
@endphp
<div
    x-data="{
        installed: !!(window.__isStandalone && window.__isStandalone()),
        busy: false,

        init() {
            /*
             * Installed, but opened in an ordinary browser tab rather than
             * from the home-screen icon. __isStandalone() cannot see that —
             * it only knows how THIS window was opened — so without this the
             * button stays on offer to someone who installed us weeks ago,
             * and Chrome (which never re-offers an installed app) sends every
             * one of those taps to the written steps.
             */
            if (navigator.getInstalledRelatedApps) {
                navigator.getInstalledRelatedApps()
                    .then((apps) => { if (apps && apps.length) this.installed = true; })
                    .catch(() => {});
            }

            window.addEventListener('appinstalled', () => {
                this.installed = true;
                this.busy = false;
            });
        },

        platform() {
            const ua = navigator.userAgent || '';
            if (/iphone|ipad|ipod/i.test(ua)) return 'ios';
            // Facebook/Instagram in-app browsers cannot install anything, and
            // a lot of our traffic arrives through a shared link.
            if (/FBAN|FBAV|Instagram|Line\//i.test(ua)) return 'inapp';
            if (/android/i.test(ua)) return 'android';
            return 'desktop';
        },

        /* Resolves true once a prompt is in hand, or false after `ms`. */
        waitForPrompt(ms) {
            return new Promise((resolve) => {
                if (window.__deferredInstallPrompt) return resolve(true);

                let settled = false;
                const finish = (value) => {
                    if (settled) return;
                    settled = true;
                    window.removeEventListener('installprompt-available', onArrival);
                    resolve(value);
                };
                const onArrival = () => finish(true);

                window.addEventListener('installprompt-available', onArrival);
                setTimeout(() => finish(!!window.__deferredInstallPrompt), ms);
            });
        },

        async fire() {
            const evt = window.__deferredInstallPrompt;
            if (!evt) return false;

            try {
                evt.prompt();
                const choice = await evt.userChoice;
                if (choice && choice.outcome === 'accepted') {
                    this.installed = true;
                }
            } catch (e) {
                // Already used, or refused by the browser. Nothing to
                // recover — the caller falls back to the written steps.
                return false;
            } finally {
                // A captured prompt is single-use, accepted or not — drop it
                // so a stale one is never replayed.
                window.__deferredInstallPrompt = null;
            }

            return true;
        },

        async install() {
            if (this.busy || this.installed) return;

            // iOS has no install API at all — no amount of waiting produces
            // one, so go straight to the Share-sheet steps.
            if (this.platform() === 'ios') {
                this.showSteps();
                return;
            }

            this.busy = true;
            try {
                if (await this.waitForPrompt(6000)) {
                    if (await this.fire()) return;
                }
                this.showSteps();
            } finally {
                this.busy = false;
            }
        },

        showSteps() {
            window.dispatchEvent(new CustomEvent('install-help', {
                detail: { platform: this.platform() },
            }));
        },
    }"
    x-show="!installed"
    x-cloak
>
    <button type="button" @click="install()" :disabled="busy" class="{{ $itemClass }}">
        <svg class="{{ $iconClass }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-show="!busy">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        <svg class="{{ $iconClass }} animate-spin" fill="none" viewBox="0 0 24 24" x-show="busy" x-cloak>
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
        <span class="{{ $labelClass }}" x-text="busy ? 'Preparing…' : 'Install App'"></span>
    </button>
</div>
