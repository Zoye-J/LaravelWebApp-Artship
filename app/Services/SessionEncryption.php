<?php

namespace App\Services;

use App\Models\Key;

/**
 * Secure Session Management - Encrypts session data with RSA/ECC
 */
class SessionEncryptionService
{
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
     * Encrypt session data
     */
    public function encryptSession(array $data): string
    {
        $json = json_encode($data);
        
        // Encrypt with ECC
        $encrypted = $this->encryptionHelper->encrypt($json);
        
        // Add HMAC for integrity
        $mac = $this->integrityService->generateMac($encrypted);
        
        return base64_encode(json_encode([
            'data' => $encrypted,
            'mac' => $mac,
            'timestamp' => time()
        ]));
    }

    /**
     * Decrypt session data
     */
    public function decryptSession(string $encryptedData): array
    {
        try {
            $payload = json_decode(base64_decode($encryptedData), true);
            
            if (!isset($payload['data'], $payload['mac'])) {
                return [];
            }
            
            // Verify integrity
            if (!$this->integrityService->verifyMac($payload['data'], $payload['mac'])) {
                \Log::warning('Session integrity check failed - possible tampering');
                return [];
            }
            
            // Decrypt
            $decrypted = $this->encryptionHelper->decrypt($payload['data']);
            
            return json_decode($decrypted, true) ?? [];
            
        } catch (\Exception $e) {
            \Log::error('Session decryption failed: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate secure session token
     */
    public function generateSessionToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Validate session token
     */
    public function validateSessionToken(string $token, string $ip, string $userAgent): bool
    {
        // Decrypt token data
        $data = $this->decryptSession($token);
        
        if (empty($data)) {
            return false;
        }
        
        // Check expiration (1 hour)
        if (isset($data['timestamp']) && (time() - $data['timestamp'] > 3600)) {
            return false;
        }
        
        // Check IP and User-Agent
        if (isset($data['ip']) && $data['ip'] !== $ip) {
            \Log::warning('Session IP mismatch', ['expected' => $data['ip'], 'actual' => $ip]);
            return false;
        }
        
        if (isset($data['user_agent']) && $data['user_agent'] !== $userAgent) {
            \Log::warning('Session User-Agent mismatch', [
                'expected' => $data['user_agent'],
                'actual' => $userAgent
            ]);
            return false;
        }
        
        return true;
    }
}