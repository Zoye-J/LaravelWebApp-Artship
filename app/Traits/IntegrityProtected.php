<?php

namespace App\Traits;

use App\Services\IntegrityService;
use Illuminate\Support\Str;

trait IntegrityProtected
{
    /**
     * Define which fields need MAC verification
     * Override this in your model
     */
    protected function getMacProtectedFields(): array
    {
        return $this->macProtected ?? [];
    }

    /**
     * Boot the trait
     */
    protected static function bootIntegrityProtected()
    {
        static::creating(function ($model) {
            $model->generateMac();
        });

        static::updating(function ($model) {
            $model->generateMac();
        });

        static::retrieved(function ($model) {
            $model->verifyMac();
        });
    }

    /**
     * Generate MAC for protected fields
     */
    public function generateMac(): void
    {
        $fields = $this->getMacProtectedFields();
        $service = new IntegrityService();

        foreach ($fields as $field) {
            $value = $this->{$field} ?? '';
            $macField = $field . '_mac';
            
            // Generate MAC using the encrypted value if available
            $valueToMac = $this->getEncryptedValue($field) ?? $value;
            $this->{$macField} = $service->generateMac($valueToMac);
        }
    }

    /**
     * Verify MAC for all protected fields
     * 
     * @throws \Exception
     */
    public function verifyMac(): void
    {
        $fields = $this->getMacProtectedFields();
        $service = new IntegrityService();

        foreach ($fields as $field) {
            $macField = $field . '_mac';
            
            // Skip if no MAC stored
            if (empty($this->{$macField})) {
                continue;
            }

            $currentValue = $this->getRawOriginal($field) ?? '';
            $storedMac = $this->{$macField};
            
            // Verify MAC
            if (!$service->verifyMac($currentValue, $storedMac)) {
                $error = sprintf(
                    'Integrity check failed for %s::%s (ID: %s) - Data may have been tampered with',
                    get_class($this),
                    $field,
                    $this->id ?? 'null'
                );
                
                \Log::error($error);
                
                // Set a flag so controllers can handle it
                $this->integrity_failed = true;
                $this->failed_field = $field;
                
                // Throw exception if strict mode is enabled
                if (config('app.debug', false)) {
                    throw new \Exception($error);
                }
            }
        }
    }

    /**
     * Check if integrity check failed
     */
    public function hasIntegrityFailed(): bool
    {
        return $this->integrity_failed ?? false;
    }

    /**
     * Get the field that failed integrity check
     */
    public function getFailedField(): ?string
    {
        return $this->failed_field ?? null;
    }
}