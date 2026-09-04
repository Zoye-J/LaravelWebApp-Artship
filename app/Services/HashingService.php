<?php

namespace App\Services;

/**
 * SHA-256 Implementation from scratch - OPTIMIZED FOR PHP
 * Uses only bitwise operations and array manipulation
 * NO built-in hash functions used
 */
class HashingService
{
    private static array $cache = [];

    // Round constants
    private const K = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5,
        0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3,
        0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc,
        0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7,
        0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13,
        0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3,
        0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5,
        0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208,
        0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2,
    ];

    // Initial hash values
    private const H0 = [
        0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a,
        0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19,
    ];

    public function sha256(string $message): string
    {
        // Check cache
        if (isset(self::$cache[$message])) {
            return self::$cache[$message];
        }

        $result = $this->compute($message);
        self::$cache[$message] = $result;
        
        return $result;
    }

    public function sha256Raw(string $message): string
    {
        return hex2bin($this->sha256($message));
    }

    private function compute(string $message): string
    {
        // Get binary data
        $data = $message;
        $originalLength = strlen($data) * 8;

        // Step 1: Padding
        $data .= "\x80";
        
        while ((strlen($data) % 64) !== 56) {
            $data .= "\x00";
        }

        // Append original length in bits (64-bit big-endian)
        $data .= pack('J', $originalLength);

        // Step 2: Initialize hash values
        $h = self::H0;

        // Step 3: Process each 64-byte chunk
        $chunks = str_split($data, 64);

        foreach ($chunks as $chunk) {
            // Step 4: Prepare message schedule (64 words)
            $w = array_fill(0, 64, 0);
            
            // Copy first 16 words from chunk
            for ($i = 0; $i < 16; $i++) {
                $w[$i] = ord($chunk[$i * 4]) << 24 
                        | ord($chunk[$i * 4 + 1]) << 16 
                        | ord($chunk[$i * 4 + 2]) << 8 
                        | ord($chunk[$i * 4 + 3]);
            }

            // Extend to 64 words
            for ($i = 16; $i < 64; $i++) {
                $word = $w[$i - 15];
                $s0 = ($this->rightRotate($word, 7) ^ $this->rightRotate($word, 18) ^ ($word >> 3));
                
                $word = $w[$i - 2];
                $s1 = ($this->rightRotate($word, 17) ^ $this->rightRotate($word, 19) ^ ($word >> 10));
                
                $w[$i] = ($w[$i - 16] + $s0 + $w[$i - 7] + $s1) & 0xFFFFFFFF;
            }

            // Step 5: Initialize working variables
            list($a, $b, $c, $d, $e, $f, $g, $h_) = $h;

            // Step 6: Main loop
            for ($i = 0; $i < 64; $i++) {
                $S1 = $this->rightRotate($e, 6) ^ $this->rightRotate($e, 11) ^ $this->rightRotate($e, 25);
                $ch = ($e & $f) ^ ((~$e) & $g);
                $temp1 = ($h_ + $S1 + $ch + self::K[$i] + $w[$i]) & 0xFFFFFFFF;
                
                $S0 = $this->rightRotate($a, 2) ^ $this->rightRotate($a, 13) ^ $this->rightRotate($a, 22);
                $maj = ($a & $b) ^ ($a & $c) ^ ($b & $c);
                $temp2 = ($S0 + $maj) & 0xFFFFFFFF;

                $h_ = $g;
                $g = $f;
                $f = $e;
                $e = ($d + $temp1) & 0xFFFFFFFF;
                $d = $c;
                $c = $b;
                $b = $a;
                $a = ($temp1 + $temp2) & 0xFFFFFFFF;
            }

            // Step 7: Add compressed chunk to current hash value
            $h[0] = ($h[0] + $a) & 0xFFFFFFFF;
            $h[1] = ($h[1] + $b) & 0xFFFFFFFF;
            $h[2] = ($h[2] + $c) & 0xFFFFFFFF;
            $h[3] = ($h[3] + $d) & 0xFFFFFFFF;
            $h[4] = ($h[4] + $e) & 0xFFFFFFFF;
            $h[5] = ($h[5] + $f) & 0xFFFFFFFF;
            $h[6] = ($h[6] + $g) & 0xFFFFFFFF;
            $h[7] = ($h[7] + $h_) & 0xFFFFFFFF;
        }

        // Step 8: Produce final hash (hex string)
        return sprintf(
            '%08x%08x%08x%08x%08x%08x%08x%08x',
            $h[0], $h[1], $h[2], $h[3], $h[4], $h[5], $h[6], $h[7]
        );
    }

    private function rightRotate(int $value, int $bits): int
    {
        return (($value >> $bits) | ($value << (32 - $bits))) & 0xFFFFFFFF;
    }

    public static function clearCache(): void
    {
        self::$cache = [];
    }
}