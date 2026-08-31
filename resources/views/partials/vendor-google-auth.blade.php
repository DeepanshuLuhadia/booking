{{--
| The browser half of "Continue with Google" for business accounts.
|
| One component drives both pages, because the two flows differ only at the
| end: the login page spends the credential immediately, the register page
| holds it while a modal collects the phone number an admin needs in order to
| call the business back.
|
| Included once per page, after the markup that uses it — the x-data expression
| is evaluated when Alpine starts (DOMContentLoaded), by which point this
| script has been parsed.
--}}
<script src="https://accounts.google.com/gsi/client" async defer></script>

<script>
    /**
     * @param {object} config
     *   mode            'login' | 'register'
     *   clientId        Google Web OAuth client id, '' when unconfigured
     *   endpoint        the POST url for this mode
     *   defaultCategory the business category the sign-up modal starts on
     *   referral        referral code carried in on the ?ref= link, if any
     */
    function vendorGoogleAuth(config) {
        return {
            mode: config.mode,
            clientId: config.clientId || '',
            endpoint: config.endpoint,

            // Which half of the card is showing. The email form is the default
            // on both pages: it is the one every existing vendor already knows.
            tab: 'email',

            rendered: false,
            busy: false,
            error: '',

            // The verified-looking bits of the credential, used only to show
            // the visitor which account they picked. Nothing here is trusted —
            // the server re-verifies the credential against Google's keys.
            account: null,
            credential: null,

            // Register only: the "one last thing" modal.
            showDetails: false,
            details: {
                business_name: '',
                vendor_type: config.defaultCategory || '',
                mobile: '',
                referral_code: config.referral || '',
                terms: false,
            },
            fieldErrors: {},

            init() {
                // The Google button is rendered lazily: drawing it into a
                // hidden panel gives it a zero width, and it never recovers.
                this.$watch('tab', (value) => {
                    if (value === 'google') this.$nextTick(() => this.renderButton());
                });
            },

            renderButton() {
                if (this.rendered || !this.clientId) return;

                const draw = () => {
                    if (!window.google?.accounts?.id || !this.$refs.googleBtn) return false;

                    window.google.accounts.id.initialize({
                        client_id: this.clientId,
                        callback: (response) => this.onCredential(response),
                        /*
                        | Fires when Google refuses before any credential exists
                        | — most often because this page's origin is not on the
                        | OAuth client's authorised list. Without it the only
                        | sign of trouble is a popup that closes again: nothing
                        | reaches our server, so nothing reaches our logs.
                        */
                        error_callback: (err) => this.onGoogleError(err),
                    });

                    window.google.accounts.id.renderButton(this.$refs.googleBtn, {
                        theme: 'filled_blue',
                        size: 'large',
                        shape: 'pill',
                        text: this.mode === 'register' ? 'signup_with' : 'continue_with',
                        width: 280,
                    });

                    this.rendered = true;
                    return true;
                };

                if (draw()) return;

                // The GIS script is async — it may still be loading.
                let tries = 0;
                const timer = setInterval(() => {
                    if (draw() || ++tries > 40) clearInterval(timer);
                }, 150);
            },

            /*
            | The visitor gets a plain "not available": the cause is a
            | server-side configuration matter that means nothing to them, and
            | the email form beside it still works. The precise diagnosis, with
            | the exact origin to authorise, goes to the console.
            */
            onGoogleError(err) {
                if ((err?.type || '') === 'unregistered_origin') {
                    console.error(
                        'GOOGLE SIGN-IN: this origin is not authorised for the OAuth client.\n' +
                        'Add exactly this to the client\'s "Authorized JavaScript origins":\n  ' +
                        window.location.origin + '\n' +
                        'Client ID: ' + this.clientId + '\n' +
                        'Note: scheme, host and port must all match, with no trailing slash, and ' +
                        'Google only permits http:// for localhost / 127.0.0.1 — every other origin must be https.'
                    );
                } else {
                    console.error('GOOGLE SIGN-IN: Google declined the request', err);
                }

                this.busy = false;
                this.error = 'Google sign-in is not available here right now. '
                    + (this.mode === 'register'
                        ? 'You can still register with the form.'
                        : 'You can still sign in with your email and password.');
            },

            onCredential(response) {
                this.error = '';
                this.fieldErrors = {};
                this.credential = response.credential;

                const payload = this.decodeJwt(response.credential);
                this.account = payload
                    ? { name: payload.name || payload.email, email: payload.email, picture: payload.picture }
                    : null;

                // Signing in is one step; signing up needs the phone number
                // first, so the credential waits in the modal.
                if (this.mode === 'register') {
                    this.showDetails = true;
                    this.$nextTick(() => this.$refs.mobileInput?.focus());
                    return;
                }

                this.submit();
            },

            async submit() {
                if (!this.credential || this.busy) return;

                this.busy = true;
                this.error = '';
                this.fieldErrors = {};

                const body = {
                    credential: this.credential,
                    // This device's push address, when it already has one, so
                    // the platform's notifications reach the phone in the
                    // owner's hand from the very first booking.
                    fcm_token: window.__fcmToken || null,
                };

                if (this.mode === 'register') {
                    body.business_name = this.details.business_name;
                    body.vendor_type   = this.details.vendor_type;
                    body.mobile        = this.details.mobile;
                    body.referral_code = this.details.referral_code || null;
                    body.terms         = this.details.terms ? 1 : 0;
                }

                try {
                    const res = await fetch(this.endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': this.csrfToken(),
                        },
                        body: JSON.stringify(body),
                    });

                    /*
                    | Read the body as text first. A 419 (expired session) or a
                    | 500 comes back as an HTML error page and res.json() throws
                    | on it — which would otherwise surface as the same vague
                    | message as everything else, hiding the status that
                    | actually explains it.
                    */
                    const raw = await res.text();
                    let data = null;
                    try { data = JSON.parse(raw); } catch (e) { /* handled below */ }

                    if (!data) {
                        console.error('GOOGLE SIGN-IN: non-JSON response', res.status, raw.slice(0, 500));
                        this.error = res.status === 419
                            ? 'Your session expired. Please refresh the page and try again.'
                            : 'Sign-in failed (error ' + res.status + '). Please try again.';
                        this.busy = false;
                        return;
                    }

                    // Laravel's validation shape: {message, errors:{field:[..]}}
                    if (res.status === 422 && data.errors) {
                        this.fieldErrors = data.errors;
                        this.error = data.message || 'Please check the details below.';
                        this.busy = false;
                        return;
                    }

                    if (!res.ok || !data.success) {
                        this.error = data.message || 'We could not sign you in. Please try again.';
                        /*
                        | Some refusals come with somewhere better to be — an
                        | address that already has an account is sent to the
                        | login page, one with none to the register page. The
                        | message is left on screen for a moment first, or the
                        | new page arrives with no explanation of why.
                        */
                        if (data.redirect) {
                            setTimeout(() => { window.location.href = data.redirect; }, 2600);
                            return;
                        }
                        this.busy = false;
                        return;
                    }

                    window.location.href = data.redirect || '/';
                } catch (e) {
                    console.error('GOOGLE SIGN-IN: request failed', e);
                    this.error = 'Network error. Please check your connection and try again.';
                    this.busy = false;
                }
            },

            /** Start over: drop the credential and put the button back. */
            cancelDetails() {
                this.showDetails = false;
                this.credential = null;
                this.account = null;
                this.error = '';
                this.fieldErrors = {};
            },

            fieldError(field) {
                const messages = this.fieldErrors[field];
                return Array.isArray(messages) ? messages[0] : (messages || '');
            },

            /**
             * Read the display fields out of the ID token WITHOUT trusting it.
             * Used only to show the visitor which account they picked; every
             * decision is made server-side off a verified copy.
             */
            decodeJwt(token) {
                try {
                    const part = String(token).split('.')[1];
                    const json = decodeURIComponent(
                        atob(part.replace(/-/g, '+').replace(/_/g, '/'))
                            .split('')
                            .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
                            .join('')
                    );
                    return JSON.parse(json);
                } catch (e) {
                    return null;
                }
            },

            csrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            },
        };
    }
</script>
