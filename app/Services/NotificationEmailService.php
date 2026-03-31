<?php

namespace App\Services;

use App\Models\User;
use App\Models\VendorProfile;
use Illuminate\Support\Facades\Mail;

class NotificationEmailService
{
    /**
     * Vendor ke liye saari notification emails return karta hai.
     *
     * Logic:
     * 1. Agar user ka global notification_email = 0 → empty array (koi mail nahi)
     * 2. Primary email hamesha include hogi (agar global ON hai)
     * 3. Vendor ki additional_emails mein se jo verified=true
     *    AND notifications=true hain, wo bhi include hongi
     *
     * @param User $user
     * @return array
     */
    public function getEmailsForUser(User $user): array
    {
        // Step 1: Global email notification check
        if (!$user->notification_email) {
            return [];
        }

        $emails = [];

        // Step 2: Primary email
        if (!empty($user->email)) {
            $emails[] = $user->email;
        }

        // Step 3: Vendor additional emails (sirf vendor role ke liye)
        $vendorProfile = $this->getVendorProfile($user);

        if ($vendorProfile) {
            $additionalEmails = $vendorProfile->additional_emails ?? [];
            $emailPreferences = $vendorProfile->email_preferences ?? [];

            foreach ($additionalEmails as $additionalEmail) {
                $pref = $emailPreferences[$additionalEmail] ?? null;

                if (
                    $pref &&
                    ($pref['verified'] ?? false) === true &&
                    ($pref['notifications'] ?? false) === true
                ) {
                    $emails[] = $additionalEmail;
                }
            }
        }

        return array_unique($emails);
    }

    /**
     * Directly Mailable bhejo - saari eligible emails par
     *
     * Usage:
     *   $this->notificationEmailService->send($user, new YourMailable($data));
     *
     * @param User $user
     * @param mixed $mailable
     * @return void
     */
    public function send(User $user, mixed $mailable): void
    {
        $emails = $this->getEmailsForUser($user);

        if (empty($emails)) {
            return; // Global notification off hai, koi mail nahi
        }

        foreach ($emails as $email) {
            Mail::to($email)->send(clone $mailable);
        }
    }

    /**
     * User ka latest approved VendorProfile fetch karta hai
     *
     * @param User $user
     * @return VendorProfile|null
     */
    private function getVendorProfile(User $user): ?VendorProfile
    {
        return VendorProfile::where('user_id', $user->id)
            ->latest()
            ->first();
    }
}