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
            // Get the raw encrypted value from database
            $valueToMac = $this->getOriginal($field) ?? $this->attributes[$field] ?? '';
            $macField = $field . '_mac';
            
            // Generate MAC using the encrypted value
            $this->{$macField} = $service->generateMac($valueToMac);
        }
    }

    /**
     * Verify MAC for all protected fields
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

            // Get the raw encrypted value
            $currentValue = $this->getOriginal($field) ?? $this->attributes[$field] ?? '';
            $storedMac = $this->{$macField};
            
            // Verify MAC against encrypted value
            if (!$service->verifyMac($currentValue, $storedMac)) {
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