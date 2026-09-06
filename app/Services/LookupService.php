<?php

namespace App\Services;

/**
 * Deterministic lookup hash for fields that need exact-match querying despite
 * being encrypted (e.g. finding a user by email). ECC/EC-ElGamal encryption
 * is randomized by design — same plaintext produces different ciphertext
 * every time — so `WHERE email = ?` can never match an encrypted column
 * directly. This produces a deterministic HMAC instead, stored in its own
 * indexed column, queried against in place of the encrypted field.
 *
 * Uses real HMAC-SHA256 (via MACService), not a naive SHA256(data + pepper)
 * concatenation — concatenation-based "keyed hashing" doesn't have the same
 * security properties as HMAC and is vulnerable to length-extension-style
 * issues that HMAC's construction specifically defends against.
 *
 * No silent fallback if the pepper isn't configured: an insecure default
 * that's sitting in a public repo isn't a secret, so this fails loudly
 * instead, the same way MAC_SECRET_KEY does.
 */
class LookupService
{
    public function emailLookup(string $email): string
    {
        $pepper = env('EMAIL_LOOKUP_PEPPER');

        if (empty($pepper)) {
            throw new \RuntimeException(
                'EMAIL_LOOKUP_PEPPER is not set in .env. Generate one with: ' .
                'php -r "echo bin2hex(random_bytes(32));" then add EMAIL_LOOKUP_PEPPER=<value> to .env.'
            );
        }

        $macService = app(MACService::class);

        return $macService->hmac(strtolower(trim($email)), $pepper);
    }
}
