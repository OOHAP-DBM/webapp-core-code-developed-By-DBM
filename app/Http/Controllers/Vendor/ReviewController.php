<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\ReviewReply;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    /**
     * Vendor Reply to Review
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:250'
        ]);

        $review = Rating::findOrFail($id);

        if ($review->hoarding->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        ReviewReply::updateOrCreate(
            [
                'rating_id' => $review->id,
                'role'      => 'vendor',
            ],
            [
                'user_id' => Auth::id(),
                'reply'   => $request->reply,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Reply saved successfully'
        ]);
    }

    /**
     * Delete Review
     */
    public function destroy($id)
    {
        $review = Rating::findOrFail($id);

        if ($review->hoarding->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted'
        ]);
    }
    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        $deleted = Rating::whereIn('id', $request->ids)
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', Auth::id()))
            ->delete();

        return response()->json(['success' => true, 'deleted' => $deleted]);
    }
}