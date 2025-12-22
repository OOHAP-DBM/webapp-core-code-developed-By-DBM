<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorProfile;
use App\Models\VendorKyc;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Exception;

class VendorOnboardingService
{
    /**
     * Save business, bank info, and PAN document for vendor onboarding.
     * All operations are transactional for safety.
     *
     * @param User $user
     * @param array $data
     * @return VendorProfile
     * @throws Exception
     */
    public function saveBusinessInfo(User $user, array $data): VendorProfile
    {
        return DB::transaction(function () use ($user, $data) {
            // Create vendor profile if not exists
            $profile = $user->vendorProfile ?: new VendorProfile(['user_id' => $user->id]);

            // Save business and bank info
            $profile->fill([
                'gstin' => $data['gstin'],
                'company_type' => $data['business_type'],
                'company_name' => $data['business_name'],
                'registered_address' => $data['registered_address'],
                'city' => $data['city'],
                'state' => $data['state'],
                'pincode' => $data['pincode'],
                'bank_name' => $data['bank_name'],
                'account_number' => $data['account_number'],
                'ifsc_code' => $data['ifsc_code'],
                'account_holder_name' => $data['account_holder_name'],
                'pan' => $data['pan_number'],
                'onboarding_status' => 'draft',
            ]);
            // Handle PAN document upload
            if (isset($data['pan_card_document']) && $data['pan_card_document'] instanceof UploadedFile) {
                $profile->pan_card_document = $this->storePanDocument($user, $data['pan_card_document']);
            }

            // Onboarding step tracking
            $profile->onboarding_step = max(1, (int) $profile->onboarding_step);
            $profile->save();
         

            // Save/Update KYC if needed (optional, extend as needed)
            // if (isset($data['pan_number'])) {
            //     VendorKyc::updateOrCreate(
            //         ['vendor_id' => $profile->id],
            //         ['pan_number' => $data['pan_number']]
            //     );
            // }
            // dd($data);
            return $profile;
        });
    }

    /**
     * Store PAN document securely and return storage path.
     */
    protected function storePanDocument(User $user, UploadedFile $file): string
    {
        $shard = sprintf('%02d/%02d', floor($user->id / 100), $user->id % 100);
        $path = "media/vendors/documents/{$shard}";
        $filename = 'pan_' . time() . '.' . $file->getClientOriginalExtension();
        return $file->storeAs($path, $filename);
    }
}
