<?php

namespace App\Services;

use App\Repositories\HoardingRepository;
use App\Models\Hoarding;

class HoardingDuplicateService
{
    protected $hoardingRepository;

    public function __construct(HoardingRepository $hoardingRepository)
    {
        $this->hoardingRepository = $hoardingRepository;
    }

    /**
     * Check for duplicate hoarding based on import row data.
     * @param array $data
     * @return Hoarding|null
     */
    public function checkDuplicate(array $data): ?Hoarding
    {
        return $this->hoardingRepository->findDuplicate($data);
    }
}
