<?php

namespace App\Http\Controllers;

use App\Services\ReviewService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    protected $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    // Display all reviews
    public function index()
    {
        $reviews = $this->reviewService->getAllReviews();
        return response()->json($reviews);
    }

    // Get review by ID
    public function show($id)
    {
        try {
            $review = $this->reviewService->getReviewById($id);
            return response()->json($review);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        }
    }

    // Create a new review
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'enrollment_id' => 'required|exists:enrollments,id',
                'rating' => 'required|numeric|between:1,5',
                'comment' => 'nullable|string|max:255',
                'status' => 'required|in:pending,approved,declined',
            ]);

            $review = $this->reviewService->createReview($validatedData);

            return response()->json([
                'data' => $review,
                'message' => 'Review created successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Update an existing review
    public function update(Request $request, $id)
    {
        try {
            $validatedData = $request->validate([
                'enrollment_id' => 'required|exists:enrollments,id',
                'rating' => 'sometimes|required|numeric|between:1,5',
                'comment' => 'nullable|string|max:255',
                'status' => 'sometimes|required|in:pending,approved,declined',
            ]);

            $review = $this->reviewService->updateReview($id, $validatedData);

            return response()->json([
                'data' => $review,
                'message' => 'Review updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Delete a review
    public function destroy($id)
    {
        try {
            $this->reviewService->deleteReview($id);
            return response()->json(['message' => 'Review deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Get reviews by course ID
    public function getByCourse($courseId)
    {
        $reviews = $this->reviewService->getReviewsByCourse($courseId);
        return response()->json($reviews);
    }
}
