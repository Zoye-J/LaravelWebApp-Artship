<?php

namespace App\Services;

use GMP;

class ECCEncryptionService
{
   
    private const P_HEX  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEFFFFFC2F';
    private const N_HEX  = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFEBAAEDCE6AF48A03BBFD25E8CD0364141';
    private const GX_HEX = '79BE667EF9DCBBAC55A06295CE870B07029BFCDB2DCE28D959F2815B16F81798';
    private const GY_HEX = '483ADA7726A3C4655DA4FBFC0E1108A8FD17B448A68554199C47D08FFB10D4B8';

    
    private const KOB_BLOCK_BYTES = 28;
    
    private const KOB_K = 256;
    private const KOB_DATA_BYTES = self::KOB_BLOCK_BYTES - 1;

    private GMP $p;
    private GMP $a;
    private GMP $b;
    private GMP $n;
    private array $g;

    private HashingService $hasher;

    public function __construct(HashingService $hasher)
    {
        $this->hasher = $hasher;
        $this->p = gmp_init(self::P_HEX, 16);
        $this->a = gmp_init(0);
        $this->b = gmp_init(7);
        $this->n = gmp_init(self::N_HEX, 16);
        $this->g = ['x' => gmp_init(self::GX_HEX, 16), 'y' => gmp_init(self::GY_HEX, 16)];
    }


    private function powMod(GMP $base, GMP $exp, GMP $modulus): GMP
    {
        $result = gmp_init(1);
        $base = gmp_mod($base, $modulus);
        while (gmp_cmp($exp, 0) > 0) {
            if (gmp_testbit($exp, 0)) {
                $result = gmp_mod(gmp_mul($result, $base), $modulus);
            }
            $exp = gmp_div_q($exp, 2);
            $base = gmp_mod(gmp_mul($base, $base), $modulus);
        }
        return $result;
    }

    private function extendedEuclid(GMP $a, GMP $b): array
    {
        if (gmp_cmp($b, 0) === 0) {
            return [$a, gmp_init(1), gmp_init(0)];
        }
        [$gcd, $x1, $y1] = $this->extendedEuclid($b, gmp_mod($a, $b));
        $x = $y1;
        $y = gmp_sub($x1, gmp_mul(gmp_div_q($a, $b), $y1));
        return [$gcd, $x, $y];
    }

    private function modInverse(GMP $a, GMP $m): GMP
    {
        [, $x, ] = $this->extendedEuclid(gmp_mod($a, $m), $m);
        return gmp_mod(gmp_add(gmp_mod($x, $m), $m), $m);
    }

    
    public function isOnCurve(?array $point): bool
    {
        if ($point === null) {
            return true;
        }
        $lhs = gmp_mod(gmp_mul($point['y'], $point['y']), $this->p);
        $rhs = gmp_mod(gmp_add(gmp_add(gmp_mul(gmp_mul($point['x'], $point['x']), $point['x']), gmp_mul($this->a, $point['x'])), $this->b), $this->p);
        return gmp_cmp($lhs, $rhs) === 0;
    }

    public function pointNegate(?array $point): ?array
    {
        if ($point === null) {
            return null;
        }
        return ['x' => $point['x'], 'y' => gmp_mod(gmp_sub($this->p, $point['y']), $this->p)];
    }

    public function pointAdd(?array $p1, ?array $p2): ?array
    {
        if ($p1 === null) {
            return $p2;
        }
        if ($p2 === null) {
            return $p1;
        }
        if (gmp_cmp($p1['x'], $p2['x']) === 0) {
            if (gmp_cmp(gmp_mod(gmp_add($p1['y'], $p2['y']), $this->p), 0) === 0) {
                return null; // P + (-P) = O
            }
            return $this->pointDouble($p1);
        }

        $lambda = gmp_mod(
            gmp_mul(gmp_sub($p2['y'], $p1['y']), $this->modInverse(gmp_sub($p2['x'], $p1['x']), $this->p)),
            $this->p
        );
        $rx = gmp_mod(gmp_sub(gmp_sub(gmp_mul($lambda, $lambda), $p1['x']), $p2['x']), $this->p);
        $ry = gmp_mod(gmp_sub(gmp_mul($lambda, gmp_sub($p1['x'], $rx)), $p1['y']), $this->p);

        return ['x' => $rx, 'y' => $ry];
    }

    public function pointDouble(?array $point): ?array
    {
        if ($point === null) {
            return null;
        }
        if (gmp_cmp($point['y'], 0) === 0) {
            return null;
        }

        $num = gmp_mod(gmp_add(gmp_mul(3, gmp_mul($point['x'], $point['x'])), $this->a), $this->p);
        $den = $this->modInverse(gmp_mod(gmp_mul(2, $point['y']), $this->p), $this->p);
        $lambda = gmp_mod(gmp_mul($num, $den), $this->p);

        $rx = gmp_mod(gmp_sub(gmp_mul($lambda, $lambda), gmp_mul(2, $point['x'])), $this->p);
        $ry = gmp_mod(gmp_sub(gmp_mul($lambda, gmp_sub($point['x'], $rx)), $point['y']), $this->p);

        return ['x' => $rx, 'y' => $ry];
    }

        public function scalarMultiply(GMP $k, ?array $point): ?array
    {
        $result = null;
        $addend = $point;
        $k = gmp_mod($k, $this->n);

        while (gmp_cmp($k, 0) > 0) {
            if (gmp_testbit($k, 0)) {
                $result = $this->pointAdd($result, $addend);
            }
            $addend = $this->pointDouble($addend);
            $k = gmp_div_q($k, 2);
        }

        return $result;
    }

   

    public function generateKeyPair(): array
    {
        do {
            $d = $this->randomBelow($this->n);
        } while (gmp_cmp($d, 1) < 0);

        $q = $this->scalarMultiply($d, $this->g);

        return [
            'private' => gmp_strval($d),
            'public' => ['x' => gmp_strval($q['x']), 'y' => gmp_strval($q['y'])],
        ];
    }

    private function randomBelow(GMP $bound): GMP
    {
        $bits = strlen(gmp_strval($bound, 2));
        do {
            $candidate = gmp_random_bits($bits);
        } while (gmp_cmp($candidate, $bound) >= 0);
        return $candidate;
    }

    public function exportPublicKey(array $keyPair): string
    {
        return json_encode(['x' => $keyPair['public']['x'], 'y' => $keyPair['public']['y']]);
    }

    public function exportPrivateKey(array $keyPair): string
    {
        return json_encode(['d' => $keyPair['private']]);
    }

    private function loadPoint(string $publicKeyJson): array
    {
        $k = json_decode($publicKeyJson, true);
        return ['x' => gmp_init($k['x']), 'y' => gmp_init($k['y'])];
    }

    
    public function ecdh(string $ourPrivateKeyJson, string $theirPublicKeyJson): string
    {
        $d = gmp_init(json_decode($ourPrivateKeyJson, true)['d']);
        $theirQ = $this->loadPoint($theirPublicKeyJson);

        $shared = $this->scalarMultiply($d, $theirQ);
        $xBytes = $this->gmpToFixedBytes($shared['x'], 32);

        return $this->hasher->sha256($xBytes);
    }

    
    public function encrypt(string $plaintext, string $publicKeyJson): string
    {
        $q = $this->loadPoint($publicKeyJson);
        $out = '';

        $chunks = $plaintext === '' ? [''] : str_split($plaintext, self::KOB_DATA_BYTES);

        foreach ($chunks as $chunk) {
            $raw = chr(strlen($chunk)) . str_pad($chunk, self::KOB_DATA_BYTES, "\x00");
            $m = $this->encodeToPoint($raw);

            do {
                $k = $this->randomBelow($this->n);
            } while (gmp_cmp($k, 1) < 0);

            $c1 = $this->scalarMultiply($k, $this->g);
            $c2 = $this->pointAdd($m, $this->scalarMultiply($k, $q));

            $out .= $this->gmpToFixedBytes($c1['x'], 32) . $this->gmpToFixedBytes($c1['y'], 32)
                  . $this->gmpToFixedBytes($c2['x'], 32) . $this->gmpToFixedBytes($c2['y'], 32);
        }

        return base64_encode($out);
    }

    public function decrypt(string $ciphertextB64, string $privateKeyJson): string
    {
        $d = gmp_init(json_decode($privateKeyJson, true)['d']);
        $raw = base64_decode($ciphertextB64);

        $plaintext = '';
        foreach (str_split($raw, 128) as $block) {
            $c1 = ['x' => gmp_import(substr($block, 0, 32)), 'y' => gmp_import(substr($block, 32, 32))];
            $c2 = ['x' => gmp_import(substr($block, 64, 32)), 'y' => gmp_import(substr($block, 96, 32))];

            $s = $this->scalarMultiply($d, $c1);
            $m = $this->pointAdd($c2, $this->pointNegate($s));

            $decoded = $this->decodeFromPoint($m);
            $len = ord($decoded[0]);
            $plaintext .= substr($decoded, 1, $len);
        }

        return $plaintext;
    }

    
    private function encodeToPoint(string $block): array
    {
        $m = gmp_import($block);
        $exp = gmp_div_q(gmp_add($this->p, 1), 4);

        for ($j = 0; $j < self::KOB_K; $j++) {
            $x = gmp_add(gmp_mul($m, self::KOB_K), $j);
            $rhs = gmp_mod(gmp_add(gmp_add(gmp_mul(gmp_mul($x, $x), $x), gmp_mul($this->a, $x)), $this->b), $this->p);

            $y = $this->powMod($rhs, $exp, $this->p);
            if (gmp_cmp(gmp_mod(gmp_mul($y, $y), $this->p), $rhs) === 0) {
                return ['x' => $x, 'y' => $y];
            }
        }

        throw new \RuntimeException('Koblitz encoding failed to find a valid curve point (statistically near-impossible).');
    }

    private function decodeFromPoint(array $point): string
    {
        $m = gmp_div_q($point['x'], self::KOB_K);
        return $this->gmpToFixedBytes($m, self::KOB_BLOCK_BYTES);
    }

    private function gmpToFixedBytes(GMP $value, int $length): string
    {
        $bytes = gmp_export($value);
        if ($bytes === '') {
            $bytes = "\x00";
        }
        $pad = $length - strlen($bytes);
        return $pad > 0 ? str_repeat("\x00", $pad) . $bytes : $bytes;
    }
}
