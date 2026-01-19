<?php

namespace Modules\Enquiries\Services;

use Modules\Enquiries\Repositories\EnquiryItemRepository;
use App\Models\Hoarding;
use Carbon\Carbon;
use Modules\Hoardings\Models\HoardingPackage;   

class EnquiryItemService
{
    public function __construct(
    protected EnquiryItemRepository $itemRepo,
    protected ServiceBuilderService $serviceBuilder
    ) {
       
    }

    public function handle($enquiry, array $hoardingIds, array $data): array
    {
        $vendorGroups = [];

        foreach ($hoardingIds as $index => $hoardingId) {
            $hoarding = Hoarding::with(['vendor', 'doohScreen'])->findOrFail($hoardingId);
            $startDate = Carbon::parse($data['preferred_start_date']);

            // Determine package_id for this hoarding
            $packageId = $data['package_id'][$index] ?? null;
            $package = null;
            $packageType = 'base';
            $months = null;

            // Validate and fetch package if selected
            if ($packageId) {
                if ($hoarding->hoarding_type === 'dooh') {
                    // For DOOH, get the doohScreen first with proper null check
                    $doohScreen = $hoarding->doohScreen;
                    if (!$doohScreen) {
                        throw new \Exception('DOOH hoarding does not have a screen configured.');
                    }
                    $package = \Modules\DOOH\Models\DOOHPackage::where('id', $packageId)
                        ->where('dooh_screen_id', $doohScreen->id)
                        ->first();
                } else {
                    $package = \Modules\Hoardings\Models\HoardingPackage::where('id', $packageId)
                        ->where('hoarding_id', $hoarding->id)
                        ->first();
                }
                if (!$package) {
                    throw new \Exception('Selected package does not belong to the selected hoarding.');
                }
                $packageType = 'package';
                // Get months from package's min_booking_duration
                $months = $package->min_booking_duration ?? 1;
                if (!$months || $months < 1) {
                    $months = 1; // Default to 1 month if not set
                }
            } else {
                // No package selected, months must be provided by user
                $months = $data['months'][$index] ?? null;
                if (!$months || !is_numeric($months) || $months < 1) {
                    throw new \Exception('Months is required when no package is selected.');
                }
            }

            // Calculate end date
            $monthsInt = is_array($months) ? (int)($months[$index] ?? $months[0]) : (int)$months;
            $endDate = (clone $startDate)->addMonths($monthsInt);

            // Build services
            if ($package) {
                // Handle both OOH and DOOH package services
                if ($hoarding->hoarding_type === 'dooh') {
                    // For DOOH packages, build services differently or use base
                    $services = $this->serviceBuilder->buildBaseOOHServices($hoarding);
                } else {
                    // For OOH packages, use package services
                    $services = $this->serviceBuilder->buildPackageServices($package);
                }
            } else {
                $services = $this->serviceBuilder->buildBaseOOHServices($hoarding);
            }

            $item = $this->itemRepo->create(
                $enquiry,
                $hoarding,
                $startDate,
                $endDate,
                $services,
                $packageType,
                $this->buildMeta($data, $index, $hoarding, $startDate, $endDate, $package, $months),
                $packageId,  // PASS PACKAGE ID
                $monthsInt  // PASS MONTHS FOR DURATION DISPLAY
            );

            if ($hoarding->vendor_id) {
                $vendorGroups[$hoarding->vendor_id][] = $item;
            }
        }

        return $vendorGroups;
    }

    private function resolvePricing(Hoarding $hoarding, array $data, int $index, Carbon $startDate): array
    {
        // PACKAGE PRICING
        if (!empty($data['package_id'][$index])) {
            $package = HoardingPackage::findOrFail($data['package_id'][$index]);

            return [
                'end_date' => (clone $startDate)->addMonths($package->min_booking_duration),

                'services' => $this->serviceBuilder
                    ->buildPackageServices($package),

                'pricing_type' => 'package',
            ];
        }

        // BASE PRICING
        return [
            'end_date' => !empty($data['preferred_end_date'])
                ? Carbon::parse($data['preferred_end_date'])
                : (clone $startDate)->addMonth(),

            'services' => $this->serviceBuilder
                ->buildBaseOOHServices($hoarding),

            'pricing_type' => 'base',
        ];
    }

    private function buildMeta($data, $index, $hoarding, $startDate, $endDate, $package = null, $months = null): array
    {
        $meta = [
            'package_label'   => $package ? $package->package_name : ($data['package_label'][$index] ?? 'Base Price'),
            'amount'          => $data['amount'][$index] ?? 0,
            'discount_percent' => $data['amount'][$index] ?? 0,  // Save discount_percent explicitly
            'duration_type'   => $data['duration_type'],
            'customer_name'   => $data['customer_name'],
            'customer_email'  => $data['customer_email'] ?? null,
            'customer_mobile' => $data['customer_mobile'] ?? null,
            'months'          => $months,
        ];

        if ($hoarding->hoarding_type === 'dooh') {
            // Get DOOH fields - CRITICAL: Only use direct access, do NOT use indexed access
            // Indexed access like $data['video_duration'][$index] would corrupt values!
            $videoDuration = $data['video_duration'] ?? 15;
            $slotsCount = $data['slots_count'] ?? 120;
            
            // Convert to integers to ensure proper type
            $videoDuration = (int)$videoDuration;
            $slotsCount = (int)$slotsCount;
            
            // CRITICAL VALIDATION: Verify values are within allowed ranges
            // video_duration MUST be 15 or 30
            if (!in_array($videoDuration, [15, 30])) {
                \Log::error('[DOOH ENQUIRY] VALIDATION FAILED - Invalid video_duration received: ' . $videoDuration . ' (must be 15 or 30)', [
                    'received_value' => $videoDuration,
                    'hoarding_id' => $hoarding->id,
                    'index' => $index,
                ]);
                throw new \Exception('Invalid video_duration: ' . $videoDuration . '. Only 15 or 30 seconds allowed.');
            }
            
            // slots_count MUST be >= 1
            if ($slotsCount < 1) {
                \Log::error('[DOOH ENQUIRY] VALIDATION FAILED - Invalid slots_count received: ' . $slotsCount . ' (must be >= 1)', [
                    'received_value' => $slotsCount,
                    'hoarding_id' => $hoarding->id,
                    'index' => $index,
                ]);
                throw new \Exception('Invalid slots_count: ' . $slotsCount . '. Minimum 1 slot required.');
            }
            
            // Log the actual values received - ONLY AFTER VALIDATION
            \Log::info('[DOOH ENQUIRY] ✅ VALIDATED - video_duration: ' . $videoDuration . ', slots_count: ' . $slotsCount, [
                'video_duration' => $videoDuration,
                'slots_count' => $slotsCount,
                'hoarding_id' => $hoarding->id,
                'index' => $index,
                'data_keys' => array_keys($data),
            ]);
            
            $meta['dooh_specs'] = [
                'video_duration' => $videoDuration,           // Validated integer
                'slots_per_day'  => $slotsCount,             // Validated integer
                'loop_interval'  => $data['slot'] ?? 'Standard',
                'total_days'     => $startDate->diffInDays($endDate),
            ];
        }

        // Pricing display logic for frontend
        if ($package) {
            $meta['pricing_display'] = [
                'type' => 'package',
                'price' => $package->price_per_month ?? $package->price_per_day ?? 0,
                'text' => 'Package price',
            ];
        } else {
            if ($hoarding->monthly_price) {
                $meta['pricing_display'] = [
                    'type' => 'monthly',
                    'price' => $hoarding->monthly_price,
                    'text' => 'Monthly price',
                ];
            } elseif ($hoarding->base_monthly_price) {
                $meta['pricing_display'] = [
                    'type' => 'base_monthly',
                    'price' => $hoarding->base_monthly_price,
                    'text' => 'Base monthly price',
                ];
            } elseif ($hoarding->doohScreen && $hoarding->doohScreen->price_per_10_sec_slot) {
                $meta['pricing_display'] = [
                    'type' => 'slot',
                    'price' => $hoarding->doohScreen->price_per_10_sec_slot,
                    'text' => 'This is price per 10-second slot. Final price depends on slot duration and loop.',
                ];
            } else {
                $meta['pricing_display'] = [
                    'type' => 'unknown',
                    'price' => 0,
                    'text' => 'Pricing not available',
                ];
            }
        }

        return $meta;
    }
}