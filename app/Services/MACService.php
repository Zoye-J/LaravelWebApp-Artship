<?php

namespace App\Services;


class MACService
{
    private const BLOCK_SIZE = 64; // SHA-256 block size in bytes
    private const IPAD = 0x36;
    private const OPAD = 0x5c;

    private HashingService $hasher;

    public function __construct(HashingService $hasher)
    {
        $this->hasher = $hasher;
    }

    
    public function hmac(string $message, string $key): string
    {
        if (strlen($key) > self::BLOCK_SIZE) {
            $key = $this->hasher->sha256Raw($key);
        }
        $key = str_pad($key, self::BLOCK_SIZE, "\x00");

        $oKeyPad = '';
        $iKeyPad = '';
        for ($i = 0; $i < self::BLOCK_SIZE; $i++) {
            $byte = ord($key[$i]);
            $oKeyPad .= chr($byte ^ self::OPAD);
            $iKeyPad .= chr($byte ^ self::IPAD);
        }

        $inner = $this->hasher->sha256Raw($iKeyPad . $message);
        return $this->hasher->sha256($oKeyPad . $inner);
    }

  
    public function generate(string $data, string $key): string
    {
        return $this->hmac($data, $key);
    }

   
    public function verify(string $message, string $key, string $providedMac): bool
    {
        $expected = $this->hmac($message, $key);

        if (strlen($expected) !== strlen($providedMac)) {
            return false;
        }

        $diff = 0;
        for ($i = 0, $len = strlen($expected); $i < $len; $i++) {
            $diff |= ord($expected[$i]) ^ ord($providedMac[$i]);
        }

        return $diff === 0;
    }
}
