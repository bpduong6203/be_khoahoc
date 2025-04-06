<?php

namespace App\Services;

use App\Models\Review;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str; // Add this import

class ReviewService
{
    // Get all reviews
    public function getAllReviews()
    {
        return Review::all();
    }

    // Get review by ID
    public function getReviewById($id)
    {
        $review = Review::find($id);
        if (!$review) {
            throw new \Exception('Review not found');
        }
        return $review;
    }

    // Create a new review - modified to explicitly set UUID
    public function createReview(array $data)
    {
        // Validate data before creating the review
        $validated = $this->validateReviewData($data);

        try {
            // Create with explicit UUID - don't use Review::create() method
            $review = new Review();
            $review->id = (string) Str::uuid(); // Explicitly set UUID
            $review->enrollment_id = $validated['enrollment_id'];
            $review->rating = $validated['rating'];
            $review->comment = $validated['comment'] ?? '';
            $review->status = $validated['status'];
            $review->save();

            return $review;
        } catch (\Exception $e) {
            throw new \Exception('Failed to create review: ' . $e->getMessage());
        }
    }

    // Update an existing review
    public function updateReview($id, array $data)
    {
        $review = Review::find($id);

        if (!$review) {
            throw new \Exception('Review not found');
        }

        // Validate data before updating the review
        $validated = $this->validateReviewData($data);

        try {
            $review->update($validated);
            return $review;
        } catch (\Exception $e) {
            throw new \Exception('Failed to update review: ' . $e->getMessage());
        }
    }

    // Delete a review
    public function deleteReview($id)
    {
        $review = Review::find($id);

        if (!$review) {
            throw new \Exception('Review not found');
        }

        try {
            $review->delete();
        } catch (\Exception $e) {
            throw new \Exception('Failed to delete review: ' . $e->getMessage());
        }
    }

    // Get reviews by course ID
    public function getReviewsByCourse($courseId)
    {
        return Review::whereHas('course', function ($query) use ($courseId) {
            $query->where('id', $courseId);
        })->get();
    }

    // Validate review data
    private function validateReviewData(array $data)
    {
        $validated = Validator::make($data, [
            'enrollment_id' => 'required|exists:enrollments,id',
            'rating' => 'required|numeric|between:1,5',
            'comment' => 'nullable|string|max:255',
            'status' => 'required|in:pending,approved,declined',
        ])->validate();

        return $validated;
    }
}
