<?php
namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reviews\Store;
use App\Http\Requests\Reviews\Update;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller {

    public function __construct(private ReviewRepositoryInterface $reviewRepository) {}

    public function index($productId) {
        $reviews = $this->reviewRepository->getByProductId($productId, 5);
        return response()->json(['reviews' => $reviews], 200);
    }

    public function store(Store $request) {
        $data            = $request->validated();
        $data['user_id'] = auth()->id();
        $review          = $this->reviewRepository->create($data);
        return response()->json(['review' => $review], 201);
    }

    public function update(Update $request, $id) {
        if (!$this->reviewRepository->isOwnedBy($id, Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review = $this->reviewRepository->update($id, $request->validated());
        return response()->json(['review' => $review]);
    }

    public function destroy($id) {
        if (!$this->reviewRepository->isOwnedBy($id, Auth::id())) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $this->reviewRepository->delete($id);
        return response()->json(['message' => 'Review deleted']);
    }

}
