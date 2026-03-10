<?php

namespace App\Http\Controllers\Web\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{

    public function store(Request $request)
    {

        $request->validate([
            'hoarding_id' => 'required|exists:hoardings,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:4000'
        ]);

        $userId = Auth::id();
        $hoardingId = $request->hoarding_id;

        $rating = Rating::where('user_id',$userId)
                        ->where('hoarding_id',$hoardingId)
                        ->first();

        if($rating){

            $rating->update([
                'rating' => $request->rating,
                'review' => $request->review
            ]);

        }else{

            Rating::create([
                'user_id' => $userId,
                'hoarding_id' => $hoardingId,
                'rating' => $request->rating,
                'review' => $request->review
            ]);

        }

        return back()->with('success','Thanks For Rating.');
    }

}