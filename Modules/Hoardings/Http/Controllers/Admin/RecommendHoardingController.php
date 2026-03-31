<?php

namespace Modules\Hoardings\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Hoarding;
use Illuminate\Http\Request;

class RecommendHoardingController extends Controller
{
    public function unrecommend($id, Request $request): \Illuminate\Http\JsonResponse
    {
        $hoarding = Hoarding::find($id);
        if (!$hoarding) {
            return response()->json(['success' => false, 'message' => 'Hoarding not found.'], 404);
        }
        $hoarding->is_recommended = 0;
        $hoarding->save();
        return response()->json(['success' => true, 'message' => 'Hoarding marked as not recommended.']);
    }
    public function recommend($id, Request $request): \Illuminate\Http\JsonResponse
    {
        $hoarding = Hoarding::find($id);
        if (!$hoarding) {
            return response()->json(['success' => false, 'message' => 'Hoarding not found.'], 404);
        }
        $hoarding->is_recommended = 1;
        $hoarding->save();
        return response()->json(['success' => true, 'message' => 'Hoarding marked as recommended.']);
    }

    public function bulkRecommend(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer|exists:hoardings,id',
        ]);
        $ids = $request->input('ids');
        $count = Hoarding::whereIn('id', $ids)->update(['is_recommended' => 1]);
        return response()->json([
            'success' => true,
            'message' => "$count hoarding(s) marked as recommended."
        ]);
    }
}
