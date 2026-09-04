<?php

namespace App\Services;

use App\Models\TwoFactorAuth;
use App\Models\User;

/**
 * Two-Factor Authentication Service - TOTP implemented from scratch
 * 
 * No built-in TOTP libraries used
 */
class TwoFactorService
{
    private const TIME_STEP = 30; // 30 seconds
    private const DIGITS = 6;
    private const WINDOW = 1; // Allow 1 step before/after for clock skew

    private HashingService $hasher;

    public function __construct(HashingService $hasher)
    {
        $this->hasher = $hasher;
    }

    /**
     * Generate a secret key for TOTP
     * 
     * @return string
     */
    public function generateSecret(): string
    {
        return $this->base32Encode(random_bytes(20));
    }

    /**
     * Generate TOTP code based on secret
     * 
     * @param string $secret
     * @param int|null $timestamp
     * @return string
     */
    public function generateCode(string $secret, ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        $counter = floor($timestamp / self::TIME_STEP);
        
        // Decode secret from base32
        $secretBytes = $this->base32Decode($secret);
        
        // Create HMAC-SHA1
        $counterBytes = pack('J', $counter); // 64-bit big-endian
        $hmac = $this->hmacSha1($secretBytes, $counterBytes);
        
        // Dynamic truncation
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $binary = substr($hmac, $offset, 4);
        $otp = (unpack('N', $binary)[1]) & 0x7FFFFFFF;
        $otp = $otp % pow(10, self::DIGITS);
        
        return str_pad($otp, self::DIGITS, '0', STR_PAD_LEFT);
    }

    /**
     * Verify TOTP code
     * 
     * @param string $secret
     * @param string $code
     * @return bool
     */
    public function verifyCode(string $secret, string $code): bool
    {
        // Check current time
        if ($this->generateCode($secret) === $code) {
            return true;
        }
        
        // Check within window (for clock skew)
        for ($i = -self::WINDOW; $i <= self::WINDOW; $i++) {
            if ($i === 0) continue;
            $timestamp = time() + ($i * self::TIME_STEP);
            if ($this->generateCode($secret, $timestamp) === $code) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * HMAC-SHA1 implementation from scratch
     * 
     * @param string $key
     * @param string $data
     * @return string
     */
    private function hmacSha1(string $key, string $data): string
    {
        $blockSize = 64;
        
        if (strlen($key) > $blockSize) {
            $key = hex2bin($this->hasher->sha1($key));
        }
        
        $key = str_pad($key, $blockSize, "\x00");
        
        $innerPad = '';
        $outerPad = '';
        for ($i = 0; $i < $blockSize; $i++) {
            $byte = ord($key[$i]);
            $innerPad .= chr($byte ^ 0x36);
            $outerPad .= chr($byte ^ 0x5c);
        }
        
        $innerHash = $this->sha1Raw($innerPad . $data);
        $outerHash = $this->sha1Raw($outerPad . $innerHash);
        
        return $outerHash;
    }

    /**
     * SHA-1 implementation from scratch (needed for TOTP)
     * 
     * @param string $data
     * @return string
     */
    private function sha1Raw(string $data): string
    {
        // SHA-1 implementation
        $h0 = 0x67452301;
        $h1 = 0xEFCDAB89;
        $h2 = 0x98BADCFE;
        $h3 = 0x10325476;
        $h4 = 0xC3D2E1F0;
        
        $originalLength = strlen($data) * 8;
        $data .= "\x80";
        
        while ((strlen($data) % 64) !== 56) {
            $data .= "\x00";
        }
        
        $data .= pack('N', $originalLength >> 32) . pack('N', $originalLength & 0xFFFFFFFF);
        
        $chunks = str_split($data, 64);
        
        foreach ($chunks as $chunk) {
            $w = array_values(unpack('N*', $chunk));
            $w = array_pad($w, 80, 0);
            
            for ($i = 16; $i < 80; $i++) {
                $w[$i] = $this->rotateLeft($w[$i-3] ^ $w[$i-8] ^ $w[$i-14] ^ $w[$i-16], 1);
            }
            
            $a = $h0;
            $b = $h1;
            $c = $h2;
            $d = $h3;
            $e = $h4;
            
            for ($i = 0; $i < 80; $i++) {
                if ($i < 20) {
                    $f = ($b & $c) | ((~$b) & $d);
                    $k = 0x5A827999;
                } elseif ($i < 40) {
                    $f = $b ^ $c ^ $d;
                    $k = 0x6ED9EBA1;
                } elseif ($i < 60) {
                    $f = ($b & $c) | ($b & $d) | ($c & $d);
                    $k = 0x8F1BBCDC;
                } else {
                    $f = $b ^ $c ^ $d;
                    $k = 0xCA62C1D6;
                }
                
                $temp = ($this->rotateLeft($a, 5) + $f + $e + $k + $w[$i]) & 0xFFFFFFFF;
                $e = $d;
                $d = $c;
                $c = $this->rotateLeft($b, 30);
                $b = $a;
                $a = $temp;
            }
            
            $h0 = ($h0 + $a) & 0xFFFFFFFF;
            $h1 = ($h1 + $b) & 0xFFFFFFFF;
            $h2 = ($h2 + $c) & 0xFFFFFFFF;
            $h3 = ($h3 + $d) & 0xFFFFFFFF;
            $h4 = ($h4 + $e) & 0xFFFFFFFF;
        }
        
        return pack('N*', $h0, $h1, $h2, $h3, $h4);
    }

    private function rotateLeft(int $value, int $bits): int
    {
        return (($value << $bits) | ($value >> (32 - $bits))) & 0xFFFFFFFF;
    }

    /**
     * Base32 encoding (RFC 4648)
     */
    private function base32Encode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $result = '';
        $buffer = 0;
        $bits = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $buffer = ($buffer << 8) | ord($data[$i]);
            $bits += 8;
            
            while ($bits >= 5) {
                $bits -= 5;
                $result .= $alphabet[($buffer >> $bits) & 0x1F];
            }
        }
        
        if ($bits > 0) {
            $result .= $alphabet[($buffer << (5 - $bits)) & 0x1F];
        }
        
        return $result;
    }

    /**
     * Base32 decoding (RFC 4648)
     */
    private function base32Decode(string $data): string
    {
        $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $map = array_flip(str_split($alphabet));
        
        $data = strtoupper($data);
        $result = '';
        $buffer = 0;
        $bits = 0;
        
        for ($i = 0; $i < strlen($data); $i++) {
            $char = $data[$i];
            if (!isset($map[$char])) {
                continue;
            }
            
            $buffer = ($buffer << 5) | $map[$char];
            $bits += 5;
            
            if ($bits >= 8) {
                $bits -= 8;
                $result .= chr(($buffer >> $bits) & 0xFF);
            }
        }
        
        return $result;
    }

    /**
     * Generate backup codes
     */
    public function generateBackupCodes(int $count = 10): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(substr(bin2hex(random_bytes(5)), 0, 10));
        }
        return $codes;
    }

    /**
     * Get user's 2FA status
     */
    public function isTwoFactorEnabled(User $user): bool
    {
        return TwoFactorAuth::where('user_id', $user->id)->where('enabled', true)->exists();
    }

    /**
     * Enable 2FA for user
     */
    public function enableTwoFactor(User $user, string $secret): bool
    {
        return TwoFactorAuth::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $secret,
                'enabled' => true,
                'backup_codes' => json_encode($this->generateBackupCodes())
            ]
        )->wasRecentlyCreated;
    }

    /**
     * Disable 2FA for user
     */
    public function disableTwoFactor(User $user): bool
    {
        return TwoFactorAuth::where('user_id', $user->id)->update(['enabled' => false]) > 0;
    }
}