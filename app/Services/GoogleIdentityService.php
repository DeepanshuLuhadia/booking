<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

/**
 * One place that turns a Google Identity Services credential (the ID token the
 * browser button hands back) into a trusted set of profile details.
 *
 * Both callers go through here so the verification rules can never drift
 * between them:
 *  - ReviewController — badging a review as posted by a verified person.
 *  - Auth\GoogleAuthController — signing that person into an account.
 */
class GoogleIdentityService
{
    /**
     * Is "Sign in with Google" switched on for this install? With no client id
     * configured every credential is rejected, so callers hide the button.
     */
    public function isConfigured(): bool
    {
        return (bool) config('services.google.client_id');
    }

    /**
     * Verify a Google ID token (JWT credential) server-side.
     *
     * Returns a normalised profile array, or null when the token is invalid,
     * expired, issued to a different client, or Google sign-in is unconfigured.
     * Never trust anything the browser sends alongside it — only what comes
     * back from here has actually been checked against Google's signing keys.
     *
     * @return array{sub:string,email:?string,email_verified:bool,name:?string,given_name:?string,family_name:?string,picture:?string,locale:?string}|null
     */
    public function verify(string $credential, string $context = 'google'): ?array
    {
        $clientId = config('services.google.client_id');
        if (! $clientId) {
            Log::warning("[{$context}] Google sign-in is unconfigured (no services.google.client_id).");

            return null;
        }

        try {
            $client  = new \Google\Client(['client_id' => $clientId]);
            $payload = $client->verifyIdToken($credential);

            // verifyIdToken returns false on failure, or the payload array.
            if (! is_array($payload) || empty($payload['sub'])) {
                Log::warning("[{$context}] Google rejected the credential (no payload returned).", [
                    'segments' => substr_count($credential, '.') + 1,
                    'length'   => strlen($credential),
                ]);

                return null;
            }

            return [
                'sub'            => (string) $payload['sub'],
                'email'          => $payload['email'] ?? null,
                // Google sends this as a bool or the string "true" depending on
                // the token; normalise so callers can just check it.
                'email_verified' => filter_var($payload['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'name'           => $payload['name'] ?? null,
                'given_name'     => $payload['given_name'] ?? null,
                'family_name'    => $payload['family_name'] ?? null,
                'picture'        => $payload['picture'] ?? null,
                'locale'         => $payload['locale'] ?? null,
            ];
        } catch (\Throwable $e) {
            /*
            | Describe the SHAPE of what arrived, never its contents — this is a
            | live credential. Segment count is the one that matters: a Google ID
            | token is always three dot-separated parts, so "Wrong number of
            | segments" against a count of 3 means the token was well-formed and
            | genuinely failed verification, while any other count means
            | something other than a token reached us.
            */
            Log::warning("[{$context}] Google credential verification failed: " . $e->getMessage(), [
                'exception' => $e::class,
                'segments'  => substr_count($credential, '.') + 1,
                'length'    => strlen($credential),
                'client_id' => substr((string) $clientId, 0, 12) . '...',
            ]);

            return null;
        }
    }

    /**
     * The best display name the token can give us, falling back through the
     * name parts to the local part of the address. Google always sends at
     * least one of these for a consumer account.
     */
    public function displayName(array $payload): string
    {
        $name = trim((string) ($payload['name'] ?? ''));

        if ($name === '') {
            $name = trim(($payload['given_name'] ?? '') . ' ' . ($payload['family_name'] ?? ''));
        }

        if ($name === '' && ! empty($payload['email'])) {
            $name = (string) strstr($payload['email'], '@', true);
        }

        return $name !== '' ? $name : 'Google User';
    }
}
