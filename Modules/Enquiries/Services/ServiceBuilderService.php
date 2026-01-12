<?php

namespace Modules\Enquiries\Services;

use App\Models\Hoarding;
use Modules\Hoardings\Models\HoardingPackage;

class ServiceBuilderService
{
    /**
     * Base pricing services (OOH / DOOH)
     */
    public function buildBaseOOHServices(Hoarding $hoarding): array
    {
        $services = [];

        // Graphics
        if ((int) $hoarding->graphics_included === 1) {
            $services[] = [
                'name'  => 'graphics',
                'price' => 0,
                'type'  => 'free',
            ];
        } elseif (!empty($hoarding->graphics_charge) && $hoarding->graphics_charge > 0) {
            $services[] = [
                'name'  => 'graphics',
                'price' => (int) $hoarding->graphics_charge,
                'type'  => 'paid',
            ];
        }

        // Survey
        if (!empty($hoarding->survey_charge) && $hoarding->survey_charge > 0) {
            $services[] = [
                'name'  => 'survey',
                'price' => (int) $hoarding->survey_charge,
                'type'  => 'paid',
            ];
        }

        return $services;
    }

    /**
     * Package pricing services
     */
    public function buildPackageServices(HoardingPackage $package): array
    {
        $services = [];

        /**
         * Example: package_services column
         * Can be:
         *  - json ["graphics","survey"]
         *  - csv "graphics,survey"
         */
        $packageServices = $package->services ?? [];

        if (is_string($packageServices)) {
            $packageServices = array_filter(
                array_map('trim', explode(',', $packageServices))
            );
        }

        foreach ($packageServices as $service) {
            $services[] = [
                'name'  => $service,
                'price' => 0,       // included in package
                'type'  => 'free',
            ];
        }

        return $services;
    }
}
