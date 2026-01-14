<?php

namespace App\Services;

use App\Models\Hoarding;
use Illuminate\Support\Arr;

class HoardingCompletionService
{
    /**
     * Calculate the completion percentage of a hoarding listing.
     *
     * @param Hoarding $hoarding
     * @return int Completion percentage (0-100)
     */
    public function calculateCompletion(Hoarding $hoarding): int
    {
        $fields = $this->getAllFields($hoarding);
        $completed = 0;
        $total = count($fields);
        if ($total === 0) return 0;
        foreach ($fields as $value) {
            if ($this->isCompleted($value)) {
                $completed++;
            }
        }
        return (int) round(($completed / $total) * 100);
    }

    /**
     * Recursively get all fields from Hoarding and its children.
     *
     * @param Hoarding $hoarding
     * @return array
     */
    protected function getAllFields(Hoarding $hoarding): array
    {
        $fields = $this->filterAttributes($hoarding->getAttributes());
        // OOH child
        if ($hoarding->ooh) {
            $fields = array_merge($fields, $this->filterAttributes($hoarding->ooh->getAttributes()));
            // Related packages
            foreach ($hoarding->ooh->packages ?? [] as $package) {
                $fields = array_merge($fields, $this->filterAttributes($package->getAttributes()));
            }
            // Related brand logos
            foreach ($hoarding->ooh->brandLogos ?? [] as $logo) {
                $fields = array_merge($fields, $this->filterAttributes($logo->getAttributes()));
            }
        }
        // DOOH child
        if ($hoarding->doohScreen) {
            $fields = array_merge($fields, $this->filterAttributes($hoarding->doohScreen->getAttributes()));
            foreach ($hoarding->doohScreen->packages ?? [] as $package) {
                $fields = array_merge($fields, $this->filterAttributes($package->getAttributes()));
            }
            foreach ($hoarding->doohScreen->brandLogos ?? [] as $logo) {
                $fields = array_merge($fields, $this->filterAttributes($logo->getAttributes()));
            }
        }
        return $fields;
    }

    /**
     * Filter out non-user fields (ids, timestamps, foreign keys, etc).
     *
     * @param array $attributes
     * @return array
     */
    protected function filterAttributes(array $attributes): array
    {
        $exclude = ['id', 'created_at', 'updated_at', 'deleted_at', 'hoarding_id', 'vendor_id', 'user_id', 'pivot'];
        return Arr::except($attributes, $exclude);
    }

    /**
     * Determine if a field value is considered completed.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isCompleted($value): bool
    {
        if (is_array($value)) {
            return !empty($value);
        }
        if (is_string($value)) {
            return trim($value) !== '';
        }
        return !is_null($value);
    }
}
