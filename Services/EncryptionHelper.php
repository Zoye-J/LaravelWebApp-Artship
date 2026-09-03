<?php

namespace App\Services;

use App\Models\Key;


class EncryptionHelper
{
    private const FIELD_PURPOSE = 'field-encryption';
    private const SIGNING_PURPOSE = 'signing';

    private RSAEncryptionService $rsa;
    private ECCEncryptionService $ecc;

    public function __construct(?RSAEncryptionService $rsa = null, ?ECCEncryptionService $ecc = null)
    {
        $this->rsa = $rsa ?? app(RSAEncryptionService::class);
        $this->ecc = $ecc ?? app(ECCEncryptionService::class);
    }

    
    public function encrypt(string $data): string
    {
        $key = $this->activeKeyOrFail('ecc', self::FIELD_PURPOSE);

        $ciphertext = $this->ecc->encrypt($data, $key->public_key);

        return $this->wrapEnvelope('ecc', $key->id, $ciphertext);
    }

    
    public function decrypt(string $encryptedData): string
    {
        $envelope = $this->unwrapEnvelope($encryptedData);

        $key = Key::find($envelope['key_id']);
        if (!$key) {
            throw new \RuntimeException("Cannot decrypt: key id {$envelope['key_id']} no longer exists.");
        }

        return match ($envelope['alg']) {
            'rsa' => $this->rsa->decrypt($envelope['ct'], $key->private_key),
            'ecc' => $this->ecc->decrypt($envelope['ct'], $key->private_key),
            default => throw new \RuntimeException("Unknown algorithm '{$envelope['alg']}' in encryption envelope."),
        };
    }

    
    public function sign(string $data): string
    {
        $key = $this->activeKeyOrFail('rsa', self::SIGNING_PURPOSE);

        return $this->wrapEnvelope('rsa', $key->id, $this->rsa->sign($data, $key->private_key));
    }

    public function verifySignature(string $data, string $signatureEnvelope): bool
    {
        try {
            $envelope = $this->unwrapEnvelope($signatureEnvelope);
        } catch (\RuntimeException) {
            return false;
        }

        if ($envelope['alg'] !== 'rsa') {
            return false;
        }

        $key = Key::find($envelope['key_id']);
        if (!$key) {
            return false;
        }

        return $this->rsa->verify($data, $envelope['ct'], $key->public_key);
    }

    
    public function getUserPublicKey(int $userId): string
    {
        return $this->activeKeyOrFail('ecc', self::FIELD_PURPOSE)->public_key;
    }

    public function getUserPrivateKey(int $userId, string $password): string
    {
        return $this->activeKeyOrFail('ecc', self::FIELD_PURPOSE)->private_key;
    }

    private function activeKeyOrFail(string $algorithm, string $purpose): Key
    {
        $key = Key::forPurpose($algorithm, $purpose)->active()->first();

        if (!$key) {
            throw new \RuntimeException(
                "No active {$algorithm} key for purpose '{$purpose}'. Run: " .
                "php artisan keys:generate {$algorithm} --purpose={$purpose}"
            );
        }

        return $key;
    }

    private function wrapEnvelope(string $alg, int $keyId, string $ciphertext): string
    {
        return base64_encode(json_encode([
            'alg' => $alg,
            'key_id' => $keyId,
            'ct' => $ciphertext,
        ]));
    }

    private function unwrapEnvelope(string $encoded): array
    {
        $decoded = json_decode(base64_decode($encoded, true) ?: '', true);

        if (!is_array($decoded) || !isset($decoded['alg'], $decoded['key_id'], $decoded['ct'])) {
            throw new \RuntimeException('Malformed encryption envelope — cannot decrypt.');
        }

        return $decoded;
    }
}
