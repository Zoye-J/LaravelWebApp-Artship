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
     */
    public function encryptFields(): void
    {
        $fields = $this->getEncryptableFields();
        $helper = new EncryptionHelper();

        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                // Store original in temporary attribute for MAC generation
                $originalValue = $this->{$field};
            
                // Encrypt the field
                $this->{$field} = $helper->encrypt($originalValue);
                

            }
        }
    }

    /**
     * Decrypt all encryptable fields
     */
    public function decryptFields(): void
    {
        $fields = $this->getEncryptableFields();
        $helper = new EncryptionHelper();

        foreach ($fields as $field) {
            if (!empty($this->{$field})) {
                try {
                    $this->{$field} = $helper->decrypt($this->{$field});
                } catch (\Exception $e) {
                    // Log decryption error but don't crash
                    \Log::error('Decryption failed for ' . get_class($this) . '::' . $field, [
                        'id' => $this->id ?? null,
                        'error' => $e->getMessage()
                    ]);
                    $this->{$field} = '[DECRYPTION FAILED]';
                }
            }
        }
    }

    /**
     * Get original encrypted value (for MAC verification)
     */
    public function getEncryptedValue(string $field): ?string
    {
        $encryptedField = $field;
        return $this->attributes[$encryptedField] ?? null;
    }
}