<?php

namespace App\Services;

/**
 * INTEGRITY SERVICE - Person 3's Service
 * 
 * NOTE: This service uses placeholder implementations.
 * Person 1 will replace these with actual HMAC/CBC-MAC implementations.
 * 
 * @person1 Implement HashingService and MACService
 * @person1 Then update these methods to use actual hashing/MAC
 */
class IntegrityService
{
    /**
     * Generate MAC for data
     * 
     * TODO FOR PERSON 1:
     * - Replace with actual HMAC implementation
     * - Use SHA-256 from scratch
     * - Example: $mac = MACService::generateHMAC($data, $key);
     * 
     * @param string $data
     * @return string MAC (hex encoded)
     */
    public function generateMac(string $data): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary: Simple hash (NOT SECURE - just for testing)
        // Person 1: Replace with actual HMAC implementation
        return hash('sha256', $data . '_MAC_KEY_PLACEHOLDER');
    }

    /**
     * Verify MAC against data
     * 
     * TODO FOR PERSON 1:
     * - Replace with actual MAC verification
     * - Use constant-time comparison to prevent timing attacks
     * - Example: return MACService::verify($data, $mac, $key);
     * 
     * @param string $data
     * @param string $mac
     * @return bool
     */
    public function verifyMac(string $data, string $mac): bool
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary: Simple verification (NOT SECURE - just for testing)
        // Person 1: Replace with actual MAC verification
        $expectedMac = $this->generateMac($data);
        return hash_equals($expectedMac, $mac);
    }

    /**
     * Get MAC key
     * 
     * TODO FOR PERSON 1:
     * - Implement proper key management
     * - Key should be stored securely
     * 
     * @return string MAC key
     */
    public function getMacKey(): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary placeholder key
        return 'MAC_KEY_PLACEHOLDER_' . env('APP_KEY', 'default');
    }
}