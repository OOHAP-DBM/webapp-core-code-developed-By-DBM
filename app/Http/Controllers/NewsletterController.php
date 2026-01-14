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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a valid email address.'
            ], 422);
        }

        $email = $request->email;

        try {

            if (NewsletterSubscriber::isSubscribed($email)) {
                return response()->json([
                    'status' => 'info',
                    'message' => 'You are already subscribed to our newsletter!'
                ]);
            }

            $subscriber = NewsletterSubscriber::where('email', $email)->first();

            if ($subscriber) {
                $subscriber->update([
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                ]);
            } else {
                NewsletterSubscriber::create([
                    'email' => $email,
                    'is_active' => true,
                    'subscribed_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Thank you for subscribing..!'
            ]);

        } catch (\Exception $e) {

            \Log::error($e);

            return response()->json([
                'status' => 'error',
                'message' => 'Something went wrong. Please try again later.'
            ], 500);
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
