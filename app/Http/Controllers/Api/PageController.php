<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AboutPage;
use App\Models\AboutLeader;
use App\Models\Faq;
use App\Models\TermsAndCondition;
use App\Models\Disclaimer;
use App\Models\PrivacyPolicy;
use App\Models\CancellationRefundPolicy;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\AboutPageResource;
use App\Http\Resources\AboutLeaderResource;
use App\Http\Resources\TermsResource;


class PageController extends Controller
{
   public function about(): JsonResponse
    {
        $about = AboutPage::first();

        if (!$about) {
            return response()->json([
                'status' => false,
                'message' => 'About page not found',
                'data' => null
            ], 404);
        }

        $leaders = AboutLeader::orderBy('sort_order')->get();

        return response()->json([
            'status' => true,
            'message' => 'About page fetched successfully',
            'data' => [
                'about' => new AboutPageResource($about),
                'leaders' => AboutLeaderResource::collection($leaders),
            ]
        ]);
    }


    public function faqs(): JsonResponse
    {
        $faqs = Faq::where('is_active', 1)
                    ->orderBy('sort_order')
                    ->get()
                    ->map(function ($faq) {
                        return [
                            'question' => $faq->question,
                            'answer' => $faq->answer, // or strip_tags($faq->answer)
                        ];
                    });

        return response()->json([
            'status' => true,
            'message' => 'FAQs fetched successfully',
            'data' => $faqs
        ]);
    }

    public function terms(): JsonResponse
    {
        $terms = TermsAndCondition::where('is_active', 1)
                    ->orderBy('sort_order')
                    ->first();

        if (!$terms) {
            return response()->json([
                'status' => false,
                'message' => 'Terms not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Terms fetched successfully',
            'data' => new TermsResource($terms)
        ]);
    }
    public function disclaimer(): JsonResponse
    {
        $disclaimer = Disclaimer::where('is_active', 1)->first();

        if (!$disclaimer) {
            return response()->json([
                'status' => false,
                'message' => 'Disclaimer not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $disclaimer
        ]);
    }


   public function privacy(): JsonResponse
    {
        $privacy = PrivacyPolicy::where('is_active', 1)->first();

        if (!$privacy) {
            return response()->json([
                'status' => false,
                'message' => 'Privacy policy not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $privacy
        ]);
    }

    public function refund(): JsonResponse
    {
        return response()->json([
            'status' => true,
            'data' => CancellationRefundPolicy::where('is_active', 1)->first()
        ]);
    }
}
