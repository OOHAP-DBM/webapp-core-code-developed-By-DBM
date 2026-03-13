<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rating;
use App\Models\ReviewReply;
use Illuminate\Support\Facades\Auth;
use App\Models\Hoarding;

class ReviewController extends Controller
{

        /**
     * @OA\Get(
     *     path="/hoardings/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Get reviews for a hoarding",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Hoarding ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */
    public function index($id)
    {
        $hoarding = Hoarding::findOrFail($id);

        $ratings = $hoarding->ratings()->with(['user', 'vendorReply'])->latest()->get();

        $totalReviews = $ratings->count();
        $avgRating    = $totalReviews ? round($ratings->avg('rating'), 1) : 0;

        $starCounts   = [];
        $starPercents = [];
        foreach ([5, 4, 3, 2, 1] as $star) {
            $count              = $ratings->where('rating', $star)->count();
            $starCounts[$star]  = $count;
            $starPercents[$star] = $totalReviews ? round(($count / $totalReviews) * 100) : 0;
        }

        $reviews = $ratings->map(function ($rev) {
            return [
                'id'          => $rev->id,
                'user'        => [
                    'id'     => $rev->user->id ?? null,
                    'name'   => $rev->user->name ?? 'Customer',
                    'avatar' => strtoupper(substr($rev->user->name ?? 'C', 0, 1)),
                ],
                'rating'      => $rev->rating,
                'review'      => $rev->review,
                'created_at'  => $rev->created_at->format('M d, Y'),
                'vendor_reply' => $rev->vendorReply ? [
                    'id'         => $rev->vendorReply->id,
                    'reply'      => $rev->vendorReply->reply,
                    'created_at' => $rev->vendorReply->created_at->format('M d, Y'),
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Reviews fetched successfully.',
            'data'    => [
                'summary' => [
                    'average_rating' => $avgRating,
                    'total_reviews'  => $totalReviews,
                    'star_counts'    => $starCounts,
                    'star_percents'  => $starPercents,
                ],
                'reviews' => $reviews,
            ]
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/hoardings/{id}/reviews",
     *     tags={"Reviews"},
     *     summary="Submit a review for a hoarding",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Hoarding ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="review", type="string", maxLength=250)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Review submitted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=409, description="Duplicate review"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request, $id)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:250',
        ]);

        $hoarding = Hoarding::findOrFail($id);

        // Prevent duplicate review by same user
        $existing = Rating::where('hoarding_id', $hoarding->id)
            ->where('user_id', Auth::id())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You have already submitted a review for this hoarding.',
                'data'    => null
            ], 409);
        }

        $review = Rating::create([
            'hoarding_id' => $hoarding->id,
            'user_id'     => Auth::id(),
            'rating'      => $request->rating,
            'review'      => $request->review,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Review submitted successfully.',
            'data'    => [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'review'     => $review->review,
                'created_at' => $review->created_at->format('M d, Y'),
            ]
        ], 201);
    }
    /**
     * @OA\Post(
     *     path="/v1/vendor/reviews/{id}/reply",
     *     tags={"Reviews"},
     *     summary="Vendor reply to a review",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review (Rating) ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"reply"},
     *             @OA\Property(property="reply", type="string", maxLength=250)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Reply saved successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply' => 'required|string|max:250'
        ]);

        $review = Rating::with('hoarding')->findOrFail($id);

        if ($review->hoarding->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not own this hoarding.',
                'data'    => null
            ], 403);
        }

        $replyRecord = ReviewReply::updateOrCreate(
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
            'message' => 'Reply saved successfully.',
            'data'    => [
                'id'        => $replyRecord->id,
                'rating_id' => $replyRecord->rating_id,
                'reply'     => $replyRecord->reply,
                'role'      => $replyRecord->role,
                'user_id'   => $replyRecord->user_id,
                'created_at'=> $replyRecord->created_at,
                'updated_at'=> $replyRecord->updated_at,
            ]
        ], 200);
    }

     /**
     * @OA\Delete(
     *     path="/v1/vendor/reviews/{id}",
     *     tags={"Reviews"},
     *     summary="Delete a single review (vendor only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Review (Rating) ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Review deleted successfully"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Not found")
     * )
     */

     
    public function destroy($id)
    {
        $review = Rating::with('hoarding')->findOrFail($id);

        if ($review->hoarding->vendor_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not own this hoarding.',
                'data'    => null
            ], 403);
        }

        $review->delete();

        return response()->json([
            'success' => true,
            'message' => 'Review deleted successfully.',
            'data'    => null
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/v1/vendor/reviews/bulk",
     *     tags={"Reviews"},
     *     summary="Bulk delete reviews (vendor only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ids"},
     *             @OA\Property(property="ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(response=200, description="Bulk delete successful"),
     *     @OA\Response(response=401, description="Unauthenticated"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array',
            'ids.*' => 'integer|exists:ratings,id'
        ]);

        $deleted = Rating::whereIn('id', $request->ids)
            ->whereHas('hoarding', fn($q) => $q->where('vendor_id', Auth::id()))
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} review(s) deleted successfully.",
            'data'    => [
                'deleted_count' => $deleted
            ]
        ], 200);
    }
}