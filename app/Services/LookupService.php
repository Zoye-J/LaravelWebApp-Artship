<?php

namespace App\Services;

class LookupService
{
    private static array $cache = [];

    public function emailLookup(string $email): string
    {
        if (isset(self::$cache[$email])) {
            return self::$cache[$email];
        }
        $pepper = env('EMAIL_LOOKUP_PEPPER', 'default_pepper');
        $hash = app(HashingService::class)->sha256($email . $pepper);
        self::$cache[$email] = $hash;
        return $hash;
    }

    /**
     * Simple hash function for lookups
     * Implemented from scratch - no built-in hash functions
     */
    private function simpleHash(string $data): string
    {
        $hash = 0;
        $len = strlen($data);
        
        for ($i = 0; $i < $len; $i++) {
            $char = ord($data[$i]);
            $hash = (($hash << 5) - $hash) + $char;
            $hash = $hash & 0xFFFFFFFF; // 32-bit
        }
        
        // Convert to hex
        return sprintf('%08x', $hash);
    }
}