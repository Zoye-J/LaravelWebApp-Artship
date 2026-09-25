<?php

namespace App\Traits;

use App\Services\IntegrityService;
use Illuminate\Support\Str;

trait IntegrityProtected
{

    protected function getMacProtectedFields(): array
    {
        return $this->macProtected ?? [];
    }


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


    public function generateMac(): void
    {
        $fields = $this->getMacProtectedFields();
        $service = new IntegrityService();

        foreach ($fields as $field) {
            // Get the raw value from attributes (this is the encrypted value)
            $valueToMac = $this->attributes[$field] ?? '';
            
            // Skip if empty
            if (empty($valueToMac)) {
                continue;
            }
            
            $macField = $field . '_mac';
            
            // Generate MAC using the encrypted value
            $this->attributes[$macField] = $service->generateMac($valueToMac);
        }
    }


    public function verifyMac(): void
    {
        $fields = $this->getMacProtectedFields();
        $service = new IntegrityService();

        foreach ($fields as $field) {
            $macField = $field . '_mac';
            

            if (empty($this->attributes[$macField] ?? null)) {
                continue;
            }

            // Get the stored encrypted value from attributes
            $encryptedValue = $this->getRawOriginal($field) ?? '';
            $storedMac = $this->attributes[$macField] ?? '';
            
            // Skip if no encrypted value
            if (empty($encryptedValue)) {
                continue;
            }
            
            // Verify MAC against encrypted value
            if (!$service->verifyMac($encryptedValue, $storedMac)) {
                $error = sprintf(
                    'Integrity check failed for %s::%s (ID: %s) - Data may have been tampered with',
                    get_class($this),
                    $field,
                    $this->id ?? 'null'
                );
                
                \Log::error($error);
                
                $this->integrity_failed = true;
                $this->failed_field = $field;
                
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