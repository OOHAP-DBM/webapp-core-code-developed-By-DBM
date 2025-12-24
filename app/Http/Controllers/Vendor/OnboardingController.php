<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CompanyDetailsRequest;
use App\Http\Requests\Vendor\BusinessInfoRequest;
use App\Http\Requests\Vendor\KYCDocumentsRequest;
use App\Http\Requests\Vendor\BankDetailsRequest;
use App\Http\Requests\Vendor\TermsAgreementRequest;
use App\Models\VendorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Http\Requests\Vendor\VendorBusinessInfoRequest;
use App\Services\VendorOnboardingService;
use Illuminate\Support\Facades\Session; // <--- Add this line
use Illuminate\Support\Facades\Redirect;
class OnboardingController extends Controller

{
    /**
     * Ensure vendor has profile
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:vendor');
    }


    /**
     * Handle submission of vendor business info (business, bank, PAN).
     * Uses VendorOnboardingService for all persistence and file upload.
     * Assigns vendor role if not already assigned, keeps previous customer role.
     * All operations are transactional for safety.
     *
     * @param VendorBusinessInfoRequest $request
     * @param VendorOnboardingService $service
     * @return \Illuminate\Http\RedirectResponse
     */
    public function submitVendorInfo(VendorBusinessInfoRequest $request, VendorOnboardingService $service)
    {
        $user = Auth::user();
        DB::beginTransaction();
        try {
            // Save business info, bank info, and PAN upload
            $profile = $service->saveBusinessInfo($user, $request->validated());
            // Onboarding step tracking
            $profile->onboarding_step = max(1, (int) $profile->onboarding_step) + 1;
            $profile->onboarding_status = 'pending_approval';
            $profile->save();
            // Assign vendor role if not already assigned
            if (!$user->hasRole('vendor')) {
                $user->assignRole('vendor');
              
            }
            $user->active_role = 'vendor';
            $user->save();
            DB::commit();
            // Redirect to vendor dashboard with flash message
            // Session::flash('success', 'Your vendor request is pending. Once approved by admin, you will be notified.');
            return Redirect::route('vendor.dashboard');
        } catch (\Exception $e) {
            DB::rollBack();
            return \Redirect::back()->withErrors(['error' => 'Failed to save vendor info. Please try again.']);
        }
    }
    /**
     * Get or create vendor profile
     */
    protected function getVendorProfile()
    {
        $user = Auth::user();
        
        if (!$user->vendorProfile) {
            return VendorProfile::create([
                'user_id' => $user->id,
                'onboarding_status' => 'draft',
                'onboarding_step' => 1,
            ]);
        }

        return $user->vendorProfile;
    }

    /**
     * Step 1: Company Details
     */
    public function showCompanyDetails()
    {
        $profile = $this->getVendorProfile();
        if ($profile->onboarding_step == 2) {
            return redirect()->route('vendor.onboarding.business-info');
        }
        if ($profile->onboarding_step >= 3) {
            return redirect()->route('vendor.dashboard');
        }
        return view('vendor.onboarding.company-details', compact('profile'));
    }

    public function storeCompanyDetails(CompanyDetailsRequest $request)
    {
        $profile = $this->getVendorProfile();

        $profile->update([
            'company_name' => $request->company_name,
            'company_registration_number' => $request->company_registration_number,
            'company_type' => $request->company_type,
            'gstin' => $request->gstin,
            'pan' => $request->pan,
            'registered_address' => $request->registered_address,
            'city' => $request->city,
            'state' => $request->state,
            'pincode' => $request->pincode,
            'website' => $request->website,
            'onboarding_step' => max($profile->onboarding_step, 2),
        ]);

        return redirect()->route('vendor.onboarding.business-info')
            ->with('success', 'Company details saved successfully!');
    }

    /**
     * Step 2: Business Information
     */
    public function showBusinessInfo()
    {
        // $profile = $this->getVendorProfile();

        // // Ensure step 1 is complete
        // if ($profile->onboarding_step < 1 || !$profile->validateCurrentStep()) {
        //     return redirect()->route('vendor.onboarding.company-details')
        //         ->with('error', 'Please complete company details first.');
        // }

        // return view('vendor.onboarding.business-info', compact('profile'));
        return view('vendor.onboarding.business-info');
    }

    public function storeBusinessInfo(BusinessInfoRequest $request)
    {
        $profile = $this->getVendorProfile();

        $profile->update([
            'year_established' => $request->year_established,
            'total_hoardings' => $request->total_hoardings,
            'service_cities' => $request->service_cities,
            'hoarding_types' => $request->hoarding_types,
            'business_description' => $request->business_description,
            'contact_person_name' => $request->contact_person_name,
            'contact_person_designation' => $request->contact_person_designation,
            'contact_person_phone' => $request->contact_person_phone,
            'contact_person_email' => $request->contact_person_email,
            'onboarding_step' => max($profile->onboarding_step, 3),
        ]);

        return redirect()->route('vendor.onboarding.kyc-documents')
            ->with('success', 'Business information saved successfully!');
    }

    /**
     * Step 3: KYC / Document Upload
     */
    public function showKYCDocuments()
    {
        $profile = $this->getVendorProfile();

        if ($profile->onboarding_step < 2) {
            return redirect()->route('vendor.onboarding.business-info')
                ->with('error', 'Please complete business information first.');
        }

        return view('vendor.onboarding.kyc-documents', compact('profile'));
    }

    public function storeKYCDocuments(KYCDocumentsRequest $request)
    {
        $profile = $this->getVendorProfile();

        $documents = [];

        // Upload each document
        if ($request->hasFile('pan_card_document')) {
            $documents['pan_card_document'] = $request->file('pan_card_document')->store('vendor/kyc', 'public');
        }

        if ($request->hasFile('gst_certificate')) {
            $documents['gst_certificate'] = $request->file('gst_certificate')->store('vendor/kyc', 'public');
        }

        if ($request->hasFile('company_registration_certificate')) {
            $documents['company_registration_certificate'] = $request->file('company_registration_certificate')->store('vendor/kyc', 'public');
        }

        if ($request->hasFile('address_proof')) {
            $documents['address_proof'] = $request->file('address_proof')->store('vendor/kyc', 'public');
        }

        if ($request->hasFile('cancelled_cheque')) {
            $documents['cancelled_cheque'] = $request->file('cancelled_cheque')->store('vendor/kyc', 'public');
        }

        if ($request->hasFile('owner_id_proof')) {
            $documents['owner_id_proof'] = $request->file('owner_id_proof')->store('vendor/kyc', 'public');
        }

        // Handle other documents (multiple files)
        if ($request->hasFile('other_documents')) {
            $otherDocs = [];
            foreach ($request->file('other_documents') as $file) {
                $otherDocs[] = $file->store('vendor/kyc', 'public');
            }
            $documents['other_documents'] = json_encode($otherDocs);
        }

        $documents['onboarding_step'] = max($profile->onboarding_step, 4);

        $profile->update($documents);

        return redirect()->route('vendor.onboarding.bank-details')
            ->with('success', 'Documents uploaded successfully!');
    }

    /**
     * Step 4: Bank Details
     */
    public function showBankDetails()
    {
        $profile = $this->getVendorProfile();

        if ($profile->onboarding_step < 3) {
            return redirect()->route('vendor.onboarding.kyc-documents')
                ->with('error', 'Please upload KYC documents first.');
        }

        return view('vendor.onboarding.bank-details', compact('profile'));
    }

    public function storeBankDetails(BankDetailsRequest $request)
    {
        $profile = $this->getVendorProfile();

        $profile->update([
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number' => $request->account_number,
            'ifsc_code' => $request->ifsc_code,
            'branch_name' => $request->branch_name,
            'account_type' => $request->account_type,
            'onboarding_step' => max($profile->onboarding_step, 5),
        ]);

        return redirect()->route('vendor.onboarding.terms-agreement')
            ->with('success', 'Bank details saved successfully!');
    }

    /**
     * Step 5: Terms & Agreement
     */
    public function showTermsAgreement()
    {
        $profile = $this->getVendorProfile();

        if ($profile->onboarding_step < 4) {
            return redirect()->route('vendor.onboarding.bank-details')
                ->with('error', 'Please complete bank details first.');
        }

        return view('vendor.onboarding.terms-agreement', compact('profile'));
    }

    public function storeTermsAgreement(TermsAgreementRequest $request)
    {
        $profile = $this->getVendorProfile();

        $profile->update([
            'terms_accepted' => true,
            'terms_accepted_at' => now(),
            'terms_ip_address' => $request->ip(),
            'commission_agreement_accepted' => true,
            'commission_percentage' => 10.00, // Default
            'onboarding_step' => 5,
        ]);

        // Mark onboarding as complete and submit for approval
        $profile->completeOnboarding();

        return redirect()->route('vendor.onboarding.waiting')
            ->with('success', 'Onboarding completed! Your application is under review.');
    }

    /**
     * Waiting screen (pending approval)
     */
    public function showWaitingScreen()
    {
        $profile = $this->getVendorProfile();

        if ($profile->onboarding_status !== 'pending_approval') {
            return redirect()->route('vendor.dashboard');
        }

        return view('vendor.onboarding.waiting', compact('profile'));
    }

    /**
     * Rejection screen
     */
    public function showRejectionScreen()
    {
        $profile = $this->getVendorProfile();

        if ($profile->onboarding_status !== 'rejected') {
            return redirect()->route('vendor.dashboard');
        }

        return view('vendor.onboarding.rejected', compact('profile'));
    }
    //only  for verifcation vendor @aviral
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        $user = auth()->user();

        // Already verified
        if ($user->email_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Email already verified'
            ], 422);
        }

        // Email already used by another account
        if (User::where('email', $request->email)
            ->where('id', '!=', $user->id)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already in use'
            ], 422);
        }

        $otp = 1234; // demo

        Cache::put(
            'vendor_email_otp_' . $user->id,
            ['otp' => $otp, 'email' => $request->email],
            now()->addMinutes(10)
        );

        Mail::raw("Your OOHAPP verification code is: $otp", function ($m) use ($request) {
            $m->to($request->email)->subject('OOHAPP Email Verification');
        });

        return response()->json(['success' => true]);
    }
    //only  for verifcation vendor @aviral
    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4'
        ]);

        $user = auth()->user();

        $cached = Cache::get('vendor_email_otp_' . $user->id);

        if (!$cached || $cached['otp'] != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }

        Cache::forget('vendor_email_otp_' . $user->id);

        $user->update([
            'email' => $cached['email'],
            'email_verified_at' => now()
        ]);

        // Increase onboarding_step if needed
        $profile = $user->vendorProfile;
        if ($profile && $profile->onboarding_step < 2) {
            $profile->onboarding_step = 2;
            $profile->save();
        }

        return response()->json(['success' => true]);
    }
    //only  for verifcation vendor @aviral
    public function sendPhoneOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|digits:10'
        ]);

        $user = auth()->user();

        // Already verified
        if ($user->phone_verified_at) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number already verified'
            ], 422);
        }

        // Phone already used by another account
        if (User::where('phone', $request->phone)
            ->where('id', '!=', $user->id)
            ->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number already in use'
            ], 422);
        }

        $otp = 1234; // demo, later SMS gateway

        Cache::put(
            'vendor_phone_otp_' . $user->id,
            [
                'otp'   => $otp,
                'phone' => $request->phone
            ],
            now()->addMinutes(10)
        );

        // TODO: SMS Gateway integration
        // sendSms($request->phone, "Your OTP is $otp");

        return response()->json(['success' => true]);
    }
    //only  for verifcation vendor @aviral
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:4'
        ]);

        $user = auth()->user();

        $cached = Cache::get('vendor_phone_otp_' . $user->id);

        if (!$cached || $cached['otp'] != $request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP'
            ], 422);
        }

        Cache::forget('vendor_phone_otp_' . $user->id);

        $user->update([
            'phone' => $cached['phone'],
            'phone_verified_at' => now()
        ]);

        // Increase onboarding_step if needed
        $profile = $user->vendorProfile;
        if ($profile && $profile->onboarding_step < 2) {
            $profile->onboarding_step = 2;
            $profile->save();
        }

        return response()->json(['success' => true]);
    }
}
