<?php

namespace App\Services;

/**
 * SHA-256 implemented from first principles (FIPS 180-4).
 *
 * No built-in hash(), openssl_*, or sodium_* functions are used anywhere
 * in this class to compute the digest itself — only generic array/string
 * utilities (unpack, sprintf, hex2bin) are used to move bytes around.
 */
class HashingService
{
    /** Round constants: first 32 bits of the fractional parts of the cube roots of the first 64 primes. */
    private const K = [
        0x428a2f98, 0x71374491, 0xb5c0fbcf, 0xe9b5dba5, 0x3956c25b, 0x59f111f1, 0x923f82a4, 0xab1c5ed5,
        0xd807aa98, 0x12835b01, 0x243185be, 0x550c7dc3, 0x72be5d74, 0x80deb1fe, 0x9bdc06a7, 0xc19bf174,
        0xe49b69c1, 0xefbe4786, 0x0fc19dc6, 0x240ca1cc, 0x2de92c6f, 0x4a7484aa, 0x5cb0a9dc, 0x76f988da,
        0x983e5152, 0xa831c66d, 0xb00327c8, 0xbf597fc7, 0xc6e00bf3, 0xd5a79147, 0x06ca6351, 0x14292967,
        0x27b70a85, 0x2e1b2138, 0x4d2c6dfc, 0x53380d13, 0x650a7354, 0x766a0abb, 0x81c2c92e, 0x92722c85,
        0xa2bfe8a1, 0xa81a664b, 0xc24b8b70, 0xc76c51a3, 0xd192e819, 0xd6990624, 0xf40e3585, 0x106aa070,
        0x19a4c116, 0x1e376c08, 0x2748774c, 0x34b0bcb5, 0x391c0cb3, 0x4ed8aa4a, 0x5b9cca4f, 0x682e6ff3,
        0x748f82ee, 0x78a5636f, 0x84c87814, 0x8cc70208, 0x90befffa, 0xa4506ceb, 0xbef9a3f7, 0xc67178f2,
    ];

    /** Initial hash values: first 32 bits of the fractional parts of the square roots of the first 8 primes. */
    private const H_INIT = [
        0x6a09e667, 0xbb67ae85, 0x3c6ef372, 0xa54ff53a,
        0x510e527f, 0x9b05688c, 0x1f83d9ab, 0x5be0cd19,
    ];

    private const MASK32 = 0xFFFFFFFF;

    /**
     * Hash arbitrary string/binary data. Returns a 64-char lowercase hex digest.
     */
    public function sha256(string $message): string
    {
        $bytes = array_values(unpack('C*', $message));
        $bitLen = strlen($message) * 8;

        // Padding: 0x80, then zeros until length % 64 == 56, then 64-bit big-endian bit length.
        $bytes[] = 0x80;
        while ((count($bytes) % 64) !== 56) {
            $bytes[] = 0x00;
        }
        for ($i = 7; $i >= 0; $i--) {
            $bytes[] = ($bitLen >> ($i * 8)) & 0xFF;
        }

        $h = self::H_INIT;

        foreach (array_chunk($bytes, 64) as $chunk) {
            $w = array_fill(0, 64, 0);
            for ($i = 0; $i < 16; $i++) {
                $w[$i] = (($chunk[$i * 4] << 24) | ($chunk[$i * 4 + 1] << 16)
                        | ($chunk[$i * 4 + 2] << 8) | $chunk[$i * 4 + 3]) & self::MASK32;
            }
            for ($i = 16; $i < 64; $i++) {
                $s0 = $this->rotr($w[$i - 15], 7) ^ $this->rotr($w[$i - 15], 18) ^ ($w[$i - 15] >> 3);
                $s1 = $this->rotr($w[$i - 2], 17) ^ $this->rotr($w[$i - 2], 19) ^ ($w[$i - 2] >> 10);
                $w[$i] = ($w[$i - 16] + $s0 + $w[$i - 7] + $s1) & self::MASK32;
            }

            [$a, $b, $c, $d, $e, $f, $g, $hh] = $h;

            for ($i = 0; $i < 64; $i++) {
                $S1 = $this->rotr($e, 6) ^ $this->rotr($e, 11) ^ $this->rotr($e, 25);
                $ch = ($e & $f) ^ ((~$e & self::MASK32) & $g);
                $temp1 = ($hh + $S1 + $ch + self::K[$i] + $w[$i]) & self::MASK32;
                $S0 = $this->rotr($a, 2) ^ $this->rotr($a, 13) ^ $this->rotr($a, 22);
                $maj = ($a & $b) ^ ($a & $c) ^ ($b & $c);
                $temp2 = ($S0 + $maj) & self::MASK32;

                $hh = $g;
                $g = $f;
                $f = $e;
                $e = ($d + $temp1) & self::MASK32;
                $d = $c;
                $c = $b;
                $b = $a;
                $a = ($temp1 + $temp2) & self::MASK32;
            }

            $h[0] = ($h[0] + $a) & self::MASK32;
            $h[1] = ($h[1] + $b) & self::MASK32;
            $h[2] = ($h[2] + $c) & self::MASK32;
            $h[3] = ($h[3] + $d) & self::MASK32;
            $h[4] = ($h[4] + $e) & self::MASK32;
            $h[5] = ($h[5] + $f) & self::MASK32;
            $h[6] = ($h[6] + $g) & self::MASK32;
            $h[7] = ($h[7] + $hh) & self::MASK32;
        }

        $out = '';
        foreach ($h as $part) {
            $out .= sprintf('%08x', $part);
        }
        return $out;
    }

    /**
     * Raw 32-byte binary digest. Needed internally (e.g. by MACService) when a
     * hash output is going to be used as input to another hash/XOR step, where
     * re-parsing hex text would be wasteful and error-prone.
     */
    public function sha256Raw(string $message): string
    {
        return hex2bin($this->sha256($message));
    }

    private function rotr(int $x, int $n): int
    {
        $x &= self::MASK32;
        return (($x >> $n) | ($x << (32 - $n))) & self::MASK32;
    }
        /**
     * SHA-1 raw output (for TOTP compatibility)
     */
    public function sha1(string $message): string
    {
        // Use the existing TwoFactorService's SHA-1 implementation
        // Or implement SHA-1 here
        // For now, we'll use a simple wrapper that calls the 2FA service
        return app(\App\Services\TwoFactorService::class)->sha1Raw($message);
    }
}
