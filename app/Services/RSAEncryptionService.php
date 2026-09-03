<?php

namespace App\Services;

use GMP;

class RSAEncryptionService
{
    /** Fixed public exponent (standard choice: Fermat prime F4). */
    private const PUBLIC_EXPONENT = 65537;

    /** Miller-Rabin rounds — 40 gives a false-positive probability of ~4^-40. */
    private const MR_ROUNDS = 40;

    private HashingService $hasher;

    public function __construct(HashingService $hasher)
    {
        $this->hasher = $hasher;
    }

    
    public function powMod(GMP $base, GMP $exp, GMP $modulus): GMP
    {
        if (gmp_cmp($modulus, 1) === 0) {
            return gmp_init(0);
        }

        $result = gmp_init(1);
        $base = gmp_mod($base, $modulus);

        while (gmp_cmp($exp, 0) > 0) {
            // if exp is odd
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

    
    public function modInverse(GMP $a, GMP $m): ?GMP
    {
        [$gcd, $x, ] = $this->extendedEuclid($a, $m);

        if (gmp_cmp($gcd, 1) !== 0) {
            return null;
        }

        return gmp_mod(gmp_add(gmp_mod($x, $m), $m), $m);
    }

    
    public function isProbablePrime(GMP $n, int $rounds = self::MR_ROUNDS): bool
    {
        if (gmp_cmp($n, 2) < 0) {
            return false;
        }
        if (gmp_cmp($n, 2) === 0 || gmp_cmp($n, 3) === 0) {
            return true;
        }
        if (gmp_testbit($n, 0) === false) { // even
            return false;
        }

        
        $d = gmp_sub($n, 1);
        $r = 0;
        while (gmp_testbit($d, 0) === false) {
            $d = gmp_div_q($d, 2);
            $r++;
        }

        $nMinus1 = gmp_sub($n, 1);
        $nMinus3 = gmp_sub($n, 3);

        for ($i = 0; $i < $rounds; $i++) {
          
            $a = gmp_add($this->randomBelow($nMinus3), 2);

            $x = $this->powMod($a, $d, $n);

            if (gmp_cmp($x, 1) === 0 || gmp_cmp($x, $nMinus1) === 0) {
                continue;
            }

            $composite = true;
            for ($j = 0; $j < $r - 1; $j++) {
                $x = $this->powMod($x, gmp_init(2), $n);
                if (gmp_cmp($x, $nMinus1) === 0) {
                    $composite = false;
                    break;
                }
            }

            if ($composite) {
                return false;
            }
        }

        return true;
    }

    
    private function randomBelow(GMP $bound): GMP
    {
        $bits = strlen(gmp_strval($bound, 2));
        do {
            $candidate = gmp_random_bits($bits);
        } while (gmp_cmp($candidate, $bound) >= 0);

        return $candidate;
    }

    private function randomBits(int $bits): GMP
    {
        return gmp_random_bits($bits);
    }

    
    private function generateLargePrime(int $bits): GMP
    {
        do {
            $candidate = gmp_random_bits($bits);
            gmp_setbit($candidate, $bits - 1);  
            gmp_setbit($candidate, 0);         
        } while (!$this->isProbablePrime($candidate));

        return $candidate;
    }

        public function generateKeyPair(int $bits = 2048): array
    {
        $half = intdiv($bits, 2);
        $e = gmp_init(self::PUBLIC_EXPONENT);

        do {
            $p = $this->generateLargePrime($half);
            $q = $this->generateLargePrime($bits - $half);

            if (gmp_cmp($p, $q) === 0) {
                continue;
            }

            $n = gmp_mul($p, $q);
            $phi = gmp_mul(gmp_sub($p, 1), gmp_sub($q, 1));

            $d = $this->modInverse($e, $phi);
        } while ($d === null || gmp_cmp(gmp_abs(gmp_sub($p, $q)), gmp_init(1)) === 0);

        return [
            'public' => [
                'n' => gmp_strval($n),
                'e' => gmp_strval($e),
            ],
            'private' => [
                'n' => gmp_strval($n),
                'd' => gmp_strval($d),
            ],
        ];
    }

    public function exportPublicKey(array $keyPair): string
    {
        return json_encode(['n' => $keyPair['public']['n'], 'e' => $keyPair['public']['e']]);
    }

    public function exportPrivateKey(array $keyPair): string
    {
        return json_encode(['n' => $keyPair['private']['n'], 'd' => $keyPair['private']['d']]);
    }

    
    public function encrypt(string $plaintext, string $publicKeyJson): string
    {
        $key = json_decode($publicKeyJson, true);
        $n = gmp_init($key['n']);
        $e = gmp_init($key['e']);
        $keyBytes = $this->keyByteLength($n);
        $maxData = $keyBytes - 11;

        $blocks = str_split($plaintext, max(1, $maxData));
        $out = '';

        foreach ($blocks as $block) {
            $padded = $this->pkcs1PadType2($block, $keyBytes);
            $m = gmp_import($padded);
            $c = $this->powMod($m, $e, $n);
            $out .= $this->gmpToFixedBytes($c, $keyBytes);
        }

        return base64_encode($out);
    }

    public function decrypt(string $ciphertextB64, string $privateKeyJson): string
    {
        $key = json_decode($privateKeyJson, true);
        $n = gmp_init($key['n']);
        $d = gmp_init($key['d']);
        $keyBytes = $this->keyByteLength($n);

        $raw = base64_decode($ciphertextB64);
        $plaintext = '';

        foreach (str_split($raw, $keyBytes) as $block) {
            $c = gmp_import($block);
            $m = $this->powMod($c, $d, $n);
            $padded = $this->gmpToFixedBytes($m, $keyBytes);
            $plaintext .= $this->pkcs1UnpadType2($padded);
        }

        return $plaintext;
    }

    

    public function sign(string $message, string $privateKeyJson): string
    {
        $key = json_decode($privateKeyJson, true);
        $n = gmp_init($key['n']);
        $d = gmp_init($key['d']);
        $keyBytes = $this->keyByteLength($n);

        $digest = $this->hasher->sha256Raw($message); // 32 raw bytes
        $padded = $this->pkcs1PadType1($digest, $keyBytes);

        $m = gmp_import($padded);
        $s = $this->powMod($m, $d, $n);

        return base64_encode($this->gmpToFixedBytes($s, $keyBytes));
    }

    public function verify(string $message, string $signatureB64, string $publicKeyJson): bool
    {
        $key = json_decode($publicKeyJson, true);
        $n = gmp_init($key['n']);
        $e = gmp_init($key['e']);
        $keyBytes = $this->keyByteLength($n);

        $sigBytes = base64_decode($signatureB64);
        if (strlen($sigBytes) !== $keyBytes) {
            return false;
        }

        $s = gmp_import($sigBytes);
        $m = $this->powMod($s, $e, $n);
        $padded = $this->gmpToFixedBytes($m, $keyBytes);

        $recoveredDigest = $this->pkcs1UnpadType1($padded);
        if ($recoveredDigest === null) {
            return false;
        }

        $expectedDigest = $this->hasher->sha256Raw($message);

        // constant-time compare
        if (strlen($recoveredDigest) !== strlen($expectedDigest)) {
            return false;
        }
        $diff = 0;
        for ($i = 0, $len = strlen($expectedDigest); $i < $len; $i++) {
            $diff |= ord($recoveredDigest[$i]) ^ ord($expectedDigest[$i]);
        }

        return $diff === 0;
    }

   
    private function keyByteLength(GMP $n): int
    {
        return (int) ceil(strlen(gmp_strval($n, 2)) / 8);
    }

    private function gmpToFixedBytes(GMP $value, int $length): string
    {
        $bytes = gmp_export($value);
        if ($bytes === '' ) {
            $bytes = "\x00";
        }
        $pad = $length - strlen($bytes);
        return $pad > 0 ? str_repeat("\x00", $pad) . $bytes : $bytes;
    }

    
    private function pkcs1PadType2(string $data, int $keyBytes): string
    {
        $psLen = $keyBytes - strlen($data) - 3;
        if ($psLen < 8) {
            throw new \InvalidArgumentException('Data too long for this RSA key size.');
        }

        $ps = '';
        while (strlen($ps) < $psLen) {
            $byte = random_bytes(1);
            if ($byte !== "\x00") {
                $ps .= $byte;
            }
        }

        return "\x00\x02" . $ps . "\x00" . $data;
    }

    private function pkcs1UnpadType2(string $padded): string
    {
        if (strlen($padded) < 11 || $padded[0] !== "\x00" || $padded[1] !== "\x02") {
            throw new \RuntimeException('Invalid RSA padding.');
        }
        $sep = strpos($padded, "\x00", 2);
        if ($sep === false) {
            throw new \RuntimeException('Invalid RSA padding.');
        }
        return substr($padded, $sep + 1);
    }

    
    private function pkcs1PadType1(string $digest, int $keyBytes): string
    {
        $psLen = $keyBytes - strlen($digest) - 3;
        if ($psLen < 8) {
            throw new \InvalidArgumentException('Key too small for this digest size.');
        }
        return "\x00\x01" . str_repeat("\xFF", $psLen) . "\x00" . $digest;
    }

    private function pkcs1UnpadType1(string $padded): ?string
    {
        if (strlen($padded) < 11 || $padded[0] !== "\x00" || $padded[1] !== "\x01") {
            return null;
        }
        $sep = strpos($padded, "\x00", 2);
        if ($sep === false) {
            return null;
        }
        // Everything between byte 2 and $sep must be 0xFF
        $ps = substr($padded, 2, $sep - 2);
        if (strlen(str_replace("\xFF", '', $ps)) !== 0) {
            return null;
        }
        return substr($padded, $sep + 1);
    }
}
