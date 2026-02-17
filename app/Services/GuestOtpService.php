<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Twilio\Rest\Client as TwilioClient;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Modules\Enquiries\Mail\OTPEmail;



class GuestOtpService
{
    protected TwilioClient $twilio;

    public function __construct()
    {
        $this->twilio = new TwilioClient(
            config('services.twilio.sid'),
            config('services.twilio.token')
        );
    }

    /**
     * Generate and send OTP
     * No user creation - works with just phone/email identifier
     */
    public function generate(string $identifier, string $purpose = 'verification')
    {
        // Delete old OTPs for this identifier and purpose
        DB::table('guest_user_otps')
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->delete();

        // Generate 4-digit OTP
        $otp = rand(1000, 9999);
        
        // Store in database (no user_id required)
        DB::table('guest_user_otps')->insert([
            'identifier' => $identifier,
            'otp' => $otp,
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Send SMS or Email
        $this->sendOTP($identifier, $otp);
        
        Log::info('OTP generated', [
            'identifier' => substr($identifier, 0, 2) . '****' . substr($identifier, -2),
            'purpose' => $purpose,
            'otp' => $otp
        ]);
        
        return true;
    }
    
    /**
     * Verify OTP
     */
    public function verify(string $identifier, string $otp, string $purpose = 'verification'): bool
    {
        $record = DB::table('guest_user_otps')
            ->where('identifier', $identifier)
            ->where('otp', $otp)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->whereNull('verified_at')
            ->first();
        
        if (!$record) {
            return false;
        }
        
        // Mark as verified
        DB::table('guest_user_otps')
            ->where('id', $record->id)
            ->update(['verified_at' => now()]);
        
        Log::info('OTP verified', [
            'identifier' => substr($identifier, 0, 2) . '****' . substr($identifier, -2),
            'purpose' => $purpose
        ]);
        
        return true;
    }
    
    /**
     * Check if identifier was recently verified
     */
    public function isRecentlyVerified(string $identifier, string $purpose = 'verification', int $minutes = 10): bool
    {
        return DB::table('guest_user_otps')
            ->where('identifier', $identifier)
            ->where('purpose', $purpose)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>', now()->subMinutes($minutes))
            ->exists();
    }
    
    /**
     * Clean up old OTP records (run this in scheduled job)
     */
    public function cleanup(int $daysOld = 7): int
    {
        return DB::table('guest_user_otps')
            ->where('created_at', '<', now()->subDays($daysOld))
            ->delete();
    }
    
    /**
     * Send SMS (integrate your SMS provider here)
     */
    // private function sendSMS(string $phone, int $otp): void
    // {
    //     // ==========================================
    //     // INTEGRATE YOUR SMS PROVIDER HERE
    //     // ==========================================
        
    //     $message = "Your OTP for hoarding enquiry is: {$otp}. Valid for 10 minutes. Do not share with anyone.";
        
    //     // Example 1: MSG91
    //     /*
    //     $curl = curl_init();
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://api.msg91.com/api/v5/flow/",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => json_encode([
    //             "flow_id" => "your_flow_id",
    //             "sender" => "your_sender_id",
    //             "mobiles" => "91{$phone}",
    //             "OTP" => $otp
    //         ]),
    //         CURLOPT_HTTPHEADER => [
    //             "authkey: your_auth_key",
    //             "content-type: application/json"
    //         ],
    //     ]);
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     */
        
    //     // Example 2: Twilio
    //     /*
    //     $client = new \Twilio\Rest\Client(
    //         config('services.twilio.sid'),
    //         config('services.twilio.token')
    //     );
        
    //     $client->messages->create(
    //         "+91{$phone}",
    //         [
    //             'from' => config('services.twilio.from'),
    //             'body' => $message
    //         ]
    //     );
    //     */
        
    //     // Example 3: Fast2SMS
    //     /*
    //     $curl = curl_init();
    //     curl_setopt_array($curl, [
    //         CURLOPT_URL => "https://www.fast2sms.com/dev/bulkV2",
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_POST => true,
    //         CURLOPT_POSTFIELDS => http_build_query([
    //             'variables_values' => $otp,
    //             'route' => 'otp',
    //             'numbers' => $phone,
    //         ]),
    //         CURLOPT_HTTPHEADER => [
    //             "authorization: your_api_key"
    //         ],
    //     ]);
    //     $response = curl_exec($curl);
    //     curl_close($curl);
    //     */
        
    //     // For development/testing - LOG IT (REMOVE IN PRODUCTION!)
    //     Log::info('SMS OTP (DEV ONLY - REMOVE IN PRODUCTION)', [
    //         'phone' => $phone,
    //         'otp' => $otp,
    //         'message' => $message
    //     ]);
        
    //     // TODO: Remove the Log::info above and uncomment your SMS provider code
    // }

     /**
     * Send OTP via SMS or Email
     */
    protected function sendOTP(string $identifier, int $otp): void
    {
        if ($this->isPhoneNumber($identifier)) {
            // Send via Twilio SMS
            try {
                $this->twilio->messages->create($this->formatPhoneNumber($identifier), [
                    'from' => config('services.twilio.from'),
                    'body' => "Your OOHAPP OTP is: {$otp}"
                ]);
            } catch (\Exception $e) {
                Log::error("OTP SMS sending failed: " . $e->getMessage());
            }
        } else {
            // Send via Email
            try {
                Mail::to($identifier)->send(new OTPEmail($otp));
            } catch (\Exception $e) {
                Log::error("OTP Email sending failed: " . $e->getMessage());
            }
        }
    }

    /**
     * Check if identifier is a phone number
     */
    protected function isPhoneNumber(string $identifier): bool
    {
        return preg_match('/^[0-9]{10,15}$/', $identifier);
    }
    protected function formatPhoneNumber(string $number): string
    {
        if (strlen($number) == 10) {
            return '+91'.$number; // Assuming India
        }
        return $number;
    }

}
