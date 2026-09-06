<?php

namespace App\Traits;

use App\Services\EncryptionHelper;

trait EncryptableFields
{
    /**
     * Define which fields should be encrypted
     * Override this in your model
     */
    protected function getEncryptableFields(): array
    {
        return $this->encryptable ?? [];
    }

    /**
     * Boot the trait
     */
    protected static function bootEncryptableFields()
    {
        static::creating(function ($model) {
            $model->encryptFields();
        });

        static::updating(function ($model) {
            $model->encryptFields();
        });

        static::retrieved(function ($model) {
            $model->decryptFields();
        });
    }

    /**
     * Encrypt all encryptable fields
     * Stores encrypted values in the model's attributes
     */
    public function encryptFields(): void
    {
        $fields = $this->getEncryptableFields();
        $helper = new EncryptionHelper();

        foreach ($fields as $field) {
            if (!empty($this->attributes[$field])) {
                // Encrypt the field and store in attributes
                $plaintext = $this->attributes[$field];
                $this->attributes[$field] = $helper->encrypt($plaintext);
            }
        }
    }

    /**
     * Decrypt all encryptable fields
     * Decrypts values when retrieving from database
     */
    public function decryptFields(): void
    {
        $fields = $this->getEncryptableFields();
        $helper = new EncryptionHelper();

        foreach ($fields as $field) {
            if (!empty($this->attributes[$field])) {
                try {
                    $encrypted = $this->attributes[$field];
                    $this->attributes[$field] = $helper->decrypt($encrypted);
                } catch (\Exception $e) {
                    \Log::error('Decryption failed for ' . get_class($this) . '::' . $field, [
                        'id' => $this->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                    $this->attributes[$field] = '[DECRYPTION FAILED]';
                }
            }
        }
    }

    /**
     * Get the raw encrypted value of a field
     * Used for MAC verification
     */
    public function getEncryptedValue(string $field): ?string
    {
        // First check if we have the original encrypted value
        if (isset($this->original[$field])) {
            return $this->original[$field];
        }
        
        // Otherwise check attributes
        return $this->attributes[$field] ?? null;
    }

    /**
     * Check if a field is encryptable (PUBLIC method for views)
     */
    public function isFieldEncryptable(string $field): bool
    {
        return in_array($field, $this->getEncryptableFields());
    }
}