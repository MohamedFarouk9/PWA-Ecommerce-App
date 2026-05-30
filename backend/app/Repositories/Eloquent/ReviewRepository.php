<?php

namespace App\Repositories\Eloquent;

use App\Models\ProductReview;
use App\Repositories\Contracts\ReviewRepositoryInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    protected $model;

    public function __construct(ProductReview $model)
    {
        $this->model = $model;
    }

    /**
     * Get reviews by product ID with limit
     */
    public function getByProductId(int $productId, int $limit = 5)
    {
        return $this->model
            ->with('user')
            ->where('product_id', $productId)
            ->limit($limit)
            ->get();
    }

    /**
     * Create a new review
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a review
     */
    public function update(int $id, array $data)
    {
        $review = $this->findOrFail($id);
        $review->update($data);
        return $review;
    }

    /**
     * Delete a review
     */
    public function delete(int $id)
    {
        $review = $this->findOrFail($id);
        return $review->delete();
    }

    /**
     * Find a review by ID
     */
    public function findOrFail(int $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Check if review is owned by user
     */
    public function isOwnedBy(int $reviewId, int $userId): bool
    {
        return $this->model
            ->where('id', $reviewId)
            ->where('user_id', $userId)
            ->exists();
    }
}
