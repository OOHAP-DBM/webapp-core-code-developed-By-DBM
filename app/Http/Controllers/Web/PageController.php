<?php

namespace App\Http\Controllers\Web;

use App\Models\AboutPage;
use App\Models\AboutLeader;
use App\Models\Faq;
use App\Models\TermsAndCondition;
use App\Models\Disclaimer;
use App\Models\PrivacyPolicy;
use App\Models\CancellationRefundPolicy;
use App\Http\Controllers\Controller;


class PageController extends Controller
{
    public function about()
    {
        // Single About page content
        $about = AboutPage::first(); // ek hi row hogi

        // Leaders list (ordered)
        $leaders = AboutLeader::orderBy('sort_order')->get();

        return view('pages.about', compact('about', 'leaders'));
    }

    public function faqs()
    {
        return view('pages.faqs', [
            'faqs' => Faq::where('is_active', 1)->orderBy('sort_order')->get()
        ]);
    }

    public function terms()
    {
        return view('pages.terms', [
            'sections' => TermsAndCondition::where('is_active', 1)->orderBy('sort_order')->get()
        ]);
    }

    public function disclaimer()
    {
        return view('pages.disclaimer', [
            'data' => Disclaimer::where('is_active', 1)->first()
        ]);
    }

    public function privacy()
    {
        return view('pages.privacy', [
            'data' => PrivacyPolicy::where('is_active', 1)->first()
        ]);
    }

    public function refund()
    {
        return view('pages.refund', [
            'data' => CancellationRefundPolicy::where('is_active', 1)->first()
        ]);
    }
}
