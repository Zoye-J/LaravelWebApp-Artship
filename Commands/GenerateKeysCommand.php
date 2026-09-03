<?php

namespace App\Console\Commands;

use App\Models\Key;
use App\Services\ECCEncryptionService;
use App\Services\HashingService;
use App\Services\RSAEncryptionService;
use Illuminate\Console\Command;

class GenerateKeysCommand extends Command
{
    protected $signature = 'keys:generate
                            {algorithm : rsa or ecc}
                            {--purpose=encryption : What the key is used for, e.g. encryption, signing}
                            {--bits=2048 : RSA key size in bits (ignored for ecc)}
                            {--force : Generate and activate a new key even if one is already active}';

    protected $description = 'Generate an RSA or ECC key pair and store it as the active key for its algorithm+purpose.';

    public function handle(RSAEncryptionService $rsa, ECCEncryptionService $ecc, HashingService $hasher): int
    {
        $algorithm = strtolower((string) $this->argument('algorithm'));
        $purpose = (string) $this->option('purpose');
        $bits = (int) $this->option('bits');

        if (!in_array($algorithm, ['rsa', 'ecc'], true)) {
            $this->error("Algorithm must be 'rsa' or 'ecc'.");
            return self::FAILURE;
        }

        $existing = Key::forPurpose($algorithm, $purpose)->active()->first();
        if ($existing && !$this->option('force')) {
            $this->warn("An active {$algorithm} key already exists for purpose '{$purpose}' (id={$existing->id}). Use --force to generate a new one anyway.");
            return self::SUCCESS;
        }

        $this->info("Generating {$algorithm} key pair for purpose '{$purpose}'" . ($algorithm === 'rsa' ? " ({$bits} bits)..." : '...'));
        $start = microtime(true);

        if ($algorithm === 'rsa') {
            $pair = $rsa->generateKeyPair($bits);
            $publicJson = $rsa->exportPublicKey($pair);
            $privateJson = $rsa->exportPrivateKey($pair);
            $keySize = $bits;
        } else {
            $pair = $ecc->generateKeyPair();
            $publicJson = $ecc->exportPublicKey($pair);
            $privateJson = $ecc->exportPrivateKey($pair);
            $keySize = null;
        }

        if ($existing) {
            $existing->update(['status' => 'rotated', 'rotated_at' => now()]);
        }

        $previousVersion = Key::forPurpose($algorithm, $purpose)->max('version') ?? 0;

        $key = Key::create([
            'algorithm' => $algorithm,
            'purpose' => $purpose,
            'key_size' => $keySize,
            'public_key' => $publicJson,
            'private_key' => $privateJson,
            'fingerprint' => $hasher->sha256($publicJson),
            'version' => $previousVersion + 1,
            'status' => 'active',
            'rotated_from_id' => $existing?->id,
            'generated_by' => null, 
        ]);

        $elapsed = round(microtime(true) - $start, 2);
        $this->info("Done in {$elapsed}s. Key id={$key->id}, fingerprint={$key->fingerprint}");

        return self::SUCCESS;
    }
}
