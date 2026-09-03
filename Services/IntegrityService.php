<?php

namespace App\Services;


class IntegrityService
{
    private MACService $mac;

    public function __construct(?MACService $mac = null)
    {
        
        $this->mac = $mac ?? app(MACService::class);
    }

    public function generateMac(string $data): string
    {
        return $this->mac->generate($data, $this->getMacKey());
    }

    public function verifyMac(string $data, string $mac): bool
    {
        return $this->mac->verify($data, $this->getMacKey(), $mac);
    }

        public function getMacKey(): string
    {
        $key = env('MAC_SECRET_KEY');

        if (empty($key)) {
            throw new \RuntimeException(
                'MAC_SECRET_KEY is not set in .env. Generate one with: ' .
                'php -r "echo bin2hex(random_bytes(32));" then add MAC_SECRET_KEY=<value> to .env.'
            );
        }

        return $key;
    }
}
