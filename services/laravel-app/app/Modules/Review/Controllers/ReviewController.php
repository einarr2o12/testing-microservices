<?php

namespace App\Modules\Review\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Review\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::with('product')->get();
        return response()->json($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'required|email',
            'is_approved' => 'boolean'
        ]);

        $review = Review::create($validated);
        return response()->json($review->load('product'), 201);
    }

    public function show(Review $review): JsonResponse
    {
        return response()->json($review->load('product'));
    }

    public function update(Request $request, Review $review): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'exists:products,id',
            'rating' => 'integer|min:1|max:5',
            'comment' => 'nullable|string',
            'reviewer_name' => 'string|max:255',
            'reviewer_email' => 'email',
            'is_approved' => 'boolean'
        ]);

        $review->update($validated);
        return response()->json($review->load('product'));
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();
        return response()->json(null, 204);
    }
}