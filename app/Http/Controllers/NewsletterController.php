<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter
     */
    public function subscribe(Request $request)
    {
        // Validate email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('newsletter_error', 'Please enter a valid email address.');
        }

        $email = $request->email;

        try {
            // Check if email already exists
            if (NewsletterSubscriber::isSubscribed($email)) {
                return back()
                    ->with('newsletter_info', 'You are already subscribed to our newsletter!');
            }

            // Check if email was previously unsubscribed
            $existingSubscriber = NewsletterSubscriber::where('email', $email)->first();
            
            if ($existingSubscriber) {
                // Re-activate subscription
                $existingSubscriber->update([
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
            } else {
                // Create new subscriber
                NewsletterSubscriber::create([
                    'email' => $email,
                    'is_active' => true,
                    'subscribed_at' => now(),
                ]);
            }

            // Log subscription
            Log::info('Newsletter subscription', ['email' => $email]);

            // Return success message
            return back()->with('newsletter_success', 'Thank you for subscribing! You will receive our latest offers and updates.');

        } catch (\Exception $e) {
            Log::error('Newsletter subscription failed', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);

            return back()
                ->withInput()
                ->with('newsletter_error', 'Something went wrong. Please try again later.');
        }
    }

    /**
     * Unsubscribe from newsletter
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->with('error', 'Invalid email address.');
        }

        try {
            $subscriber = NewsletterSubscriber::where('email', $request->email)->first();

            if (!$subscriber) {
                return back()->with('info', 'Email address not found in our newsletter list.');
            }

            $subscriber->update([
                'is_active' => false,
                'unsubscribed_at' => now(),
            ]);

            Log::info('Newsletter unsubscription', ['email' => $request->email]);

            return back()->with('success', 'You have been successfully unsubscribed from our newsletter.');

        } catch (\Exception $e) {
            Log::error('Newsletter unsubscription failed', [
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Something went wrong. Please try again later.');
        }
    }
}
