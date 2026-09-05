<?php

namespace App\Services;

/**
 * Custom Password Hashing Service - Implemented from scratch
 * Uses: SHA-256, random salt, PBKDF2-like key derivation
 * 
 * No built-in password_hash(), password_verify(), or Hash::make() used
 */
class CustomHashService
{
    private const ITERATIONS =3000;
    private const SALT_LENGTH = 32;
    private const HASH_LENGTH = 32;
    private const ALGORITHM = 'sha256';

    private HashingService $hasher;

    public function __construct(HashingService $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * Generate a salted password hash from scratch
     * 
     * @param string $password
     * @return string Format: "iterations:hash:salt" (base64 encoded)
     */
    public function make(string $password): string
    {
        // Generate random salt
        $salt = $this->generateSalt();
        
        // Hash password with salt
        $hash = $this->pbkdf2($password, $salt, self::ITERATIONS, self::HASH_LENGTH);
        
        // Encode for storage
        return base64_encode(json_encode([
            'iterations' => self::ITERATIONS,
            'hash' => base64_encode($hash),
            'salt' => base64_encode($salt)
        ]));
    }

    /**
     * Verify a password against a stored hash
     * 
     * @param string $password
     * @param string $storedHash
     * @return bool
     */
    public function check(string $password, string $storedHash): bool
    {
        try {
            $data = json_decode(base64_decode($storedHash), true);
            
            if (!isset($data['hash'], $data['salt'], $data['iterations'])) {
                return false;
            }
            
            $hash = base64_decode($data['hash']);
            $salt = base64_decode($data['salt']);
            $iterations = (int) $data['iterations'];
            
            // Compute hash using same parameters
            $computedHash = $this->pbkdf2($password, $salt, $iterations, strlen($hash));
            
            // Constant-time comparison
            return $this->hashEquals($hash, $computedHash);
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * PBKDF2 key derivation function - implemented from scratch
     * 
     * @param string $password
     * @param string $salt
     * @param int $iterations
     * @param int $length
     * @return string
     */
    private function pbkdf2(string $password, string $salt, int $iterations, int $length): string
    {
        $hashLength = 32; // SHA-256 output length
        $blocks = ceil($length / $hashLength);
        $result = '';
        
        for ($block = 1; $block <= $blocks; $block++) {
            // First iteration: HMAC(password, salt || block_index)
            $current = $this->hmac($password, $salt . pack('N', $block));
            $start = $current;
            
            // Additional iterations
            for ($i = 1; $i < $iterations; $i++) {
                $current = $this->hmac($password, $current);
                // XOR the result
                $start = $this->xorStrings($start, $current);
            }
            
            $result .= $start;
        }
        
        return substr($result, 0, $length);
    }

    /**
     * HMAC-SHA256 - implemented from scratch
     * 
     * @param string $key
     * @param string $data
     * @return string
     */
    private function hmac(string $key, string $data): string
    {
        $blockSize = 64;
        
        // If key is longer than block size, hash it
        if (strlen($key) > $blockSize) {
            $key = hex2bin($this->hasher->sha256($key));
        }
        
        // Pad key to block size
        $key = str_pad($key, $blockSize, "\x00");
        
        // Create inner and outer pads
        $innerPad = '';
        $outerPad = '';
        for ($i = 0; $i < $blockSize; $i++) {
            $byte = ord($key[$i]);
            $innerPad .= chr($byte ^ 0x36);
            $outerPad .= chr($byte ^ 0x5c);
        }
        
        // Inner hash: SHA-256(innerPad || data)
        $innerHash = $this->hasher->sha256($innerPad . $data);
        
        // Outer hash: SHA-256(outerPad || innerHash)
        return hex2bin($this->hasher->sha256($outerPad . $innerHash));
    }

    /**
     * Generate cryptographically secure random salt
     * 
     * @return string
     */
    private function generateSalt(): string
    {
        return random_bytes(self::SALT_LENGTH);
    }

    /**
     * Constant-time string comparison
     * 
     * @param string $a
     * @param string $b
     * @return bool
     */
    private function hashEquals(string $a, string $b): bool
    {
        if (strlen($a) !== strlen($b)) {
            return false;
        }
        
        $diff = 0;
        for ($i = 0; $i < strlen($a); $i++) {
            $diff |= ord($a[$i]) ^ ord($b[$i]);
        }
        
        return $diff === 0;
    }

    /**
     * XOR two strings
     * 
     * @param string $a
     * @param string $b
     * @return string
     */
    private function xorStrings(string $a, string $b): string
    {
        $length = min(strlen($a), strlen($b));
        $result = '';
        for ($i = 0; $i < $length; $i++) {
            $result .= chr(ord($a[$i]) ^ ord($b[$i]));
        }
        return $result;
    }

    /**
     * Check if password needs rehash (for future upgrades)
     * 
     * @param string $hash
     * @return bool
     */
    public function needsRehash(string $hash): bool
    {
        try {
            $data = json_decode(base64_decode($hash), true);
            return !isset($data['iterations']) || $data['iterations'] < self::ITERATIONS;
        } catch (\Exception $e) {
            return true;
        }
    }
}