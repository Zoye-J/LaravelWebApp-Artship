<?php

namespace App\Services;

/**
 * ENCRYPTION HELPER - Person 3's Service
 * 
 * NOTE: This service uses placeholder implementations.
 * Person 1 will replace these with actual RSA/ECC implementations.
 * 
 * @person1 Implement RSAEncryptionService and ECCEncryptionService
 * @person1 Then update these methods to use those services
 */
class EncryptionHelper
{
    /**
     * Encrypt data using asymmetric encryption
     * 
     * TODO FOR PERSON 1:
     * - Replace this with actual RSA or ECC encryption
     * - Use RSA for user data, ECC for other data
     * - Example: $encrypted = RSAEncryptionService::encrypt($data, $publicKey);
     * 
     * @param string $data
     * @return string Encrypted data (base64 encoded)
     */
    public function encrypt(string $data): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary: Simple reversible encoding (NOT SECURE - just for testing)
        // Person 1: Replace with actual RSA/ECC encryption
        return base64_encode($data);
    }

    /**
     * Decrypt data using asymmetric encryption
     * 
     * TODO FOR PERSON 1:
     * - Replace this with actual RSA or ECC decryption
     * - Use RSA for user data, ECC for other data
     * - Example: $decrypted = RSAEncryptionService::decrypt($encrypted, $privateKey);
     * 
     * @param string $encryptedData Base64 encoded encrypted data
     * @return string Decrypted data
     */
    public function decrypt(string $encryptedData): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary: Reversible encoding (NOT SECURE - just for testing)
        // Person 1: Replace with actual RSA/ECC decryption
        return base64_decode($encryptedData);
    }

    /**
     * Get user's public key for encryption
     * 
     * TODO FOR PERSON 1:
     * - Implement proper key retrieval from Key model
     * - Use active keys only
     * 
     * @param int $userId
     * @return string Public key
     */
    public function getUserPublicKey(int $userId): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary placeholder key
        return 'PLACEHOLDER_PUBLIC_KEY_' . $userId;
    }

    /**
     * Get user's private key for decryption
     * 
     * TODO FOR PERSON 1:
     * - Implement proper key retrieval with password
     * - Private keys should be encrypted with user's password
     * 
     * @param int $userId
     * @param string $password User's password for decrypting private key
     * @return string Private key
     */
    public function getUserPrivateKey(int $userId, string $password): string
    {
        // ============================================
        // PLACEHOLDER - Person 1 will implement this
        // ============================================
        
        // Temporary placeholder key
        return 'PLACEHOLDER_PRIVATE_KEY_' . $userId;
    }
}