<?php

namespace App\Services;

/**
 * Secure Session Management — session tokens bound to IP/User-Agent, with a
 * timeout, encrypted at rest and integrity-protected.
 *
 * generateSessionToken() and validateSessionToken() are two ends of the same
 * pipe: whatever generate() puts in, validate() must be able to get back out.
 * (Previously they didn't agree — generate() returned a bare random hex
 * string, while validate() tried to decrypt its input as a full encrypted
 * envelope. That's fixed here: generate() now produces exactly what
 * validate() expects.)
 */
class SessionEncryptionService
{
    private const SESSION_TIMEOUT_SECONDS = 3600; // 1 hour

    private EncryptionHelper $encryptionHelper;
    private IntegrityService $integrityService;

    public function __construct(
        EncryptionHelper $encryptionHelper,
        IntegrityService $integrityService
    ) {
        $this->encryptionHelper = $encryptionHelper;
        $this->integrityService = $integrityService;
    }

    /**
     * Encrypt arbitrary session data into a single opaque, integrity-protected string.
     */
    public function encryptSession(array $data): string
    {
        $json = json_encode($data);

        $encrypted = $this->encryptionHelper->encrypt($json);
        $mac = $this->integrityService->generateMac($encrypted);

        return base64_encode(json_encode([
            'data' => $encrypted,
            'mac' => $mac,
        ]));
    }

    /**
     * Decrypt a value produced by encryptSession(). Returns [] on any
     * failure (malformed input, tampered MAC, decryption error) — callers
     * should treat an empty array as "not valid," not as "empty but trusted."
     */
    public function decryptSession(string $encryptedData): array
    {
        try {
            $payload = json_decode(base64_decode($encryptedData), true);

            if (!isset($payload['data'], $payload['mac'])) {
                return [];
            }

            if (!$this->integrityService->verifyMac($payload['data'], $payload['mac'])) {
                \Log::warning('Session integrity check failed - possible tampering');
                return [];
            }

            $decrypted = $this->encryptionHelper->decrypt($payload['data']);

            return json_decode($decrypted, true) ?? [];
        } catch (\Exception $e) {
            \Log::error('Session decryption failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Issue a session token bound to a specific user, IP, and User-Agent,
     * timestamped for expiry checking. This IS the encrypted+MAC'd envelope
     * (see encryptSession) — not a separate opaque random string — so
     * validateSessionToken() can actually decrypt and check it.
     */
    public function generateSessionToken(int $userId, string $ip, string $userAgent): string
    {
        return $this->encryptSession([
            'user_id' => $userId,
            'ip' => $ip,
            'user_agent' => $userAgent,
            'timestamp' => time(),
        ]);
    }

    /**
     * Validate a token against the current request's IP/User-Agent and check
     * it hasn't expired. Returns false on anything suspicious — expired,
     * tampered (caught inside decryptSession via MAC), IP/UA mismatch, or malformed.
     */
    public function validateSessionToken(string $token, string $ip, string $userAgent): bool
    {
        $data = $this->decryptSession($token);

        if (empty($data)) {
            return false;
        }

        if (!isset($data['timestamp']) || (time() - $data['timestamp'] > self::SESSION_TIMEOUT_SECONDS)) {
            return false;
        }

        if (!isset($data['ip']) || $data['ip'] !== $ip) {
            \Log::warning('Session IP mismatch', ['expected' => $data['ip'] ?? null, 'actual' => $ip]);
            return false;
        }

        if (!isset($data['user_agent']) || $data['user_agent'] !== $userAgent) {
            \Log::warning('Session User-Agent mismatch', [
                'expected' => $data['user_agent'] ?? null,
                'actual' => $userAgent,
            ]);
            return false;
        }

        return true;
    }
}
