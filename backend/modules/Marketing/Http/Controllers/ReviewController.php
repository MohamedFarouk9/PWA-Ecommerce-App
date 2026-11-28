<?php

namespace Modules\Marketing\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Marketing\Models\ProductReview;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductReview::with(['product', 'user'])->latest();

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        if ($rating = $request->input('rating')) {
            $query->where('rating', $rating);
        }

        $reviews = $query->get();

        $stats = [
            'total'      => ProductReview::count(),
            'approved'   => ProductReview::where('status', 'approved')->count(),
            'pending'    => ProductReview::where('status', 'pending')->count(),
            'rejected'   => ProductReview::where('status', 'rejected')->count(),
            'avg_rating' => round((float) ProductReview::avg('rating'), 1),
        ];

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status'  => 'success',
                'stats'   => $stats,
                'reviews' => $reviews,
            ]);
        }

        return view('admin.reviews.index', compact('reviews', 'stats'));
    }

    public function show($id, Request $request)
    {
        $review = ProductReview::with(['product', 'user'])->findOrFail($id);

        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json(['status' => 'success', 'review' => $review]);
        }

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->update(['status' => 'approved']);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Review approved.']);
        }

        return back()->with('success', 'Review approved successfully.');
    }

    public function reject(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->update(['status' => 'rejected']);

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Review rejected.']);
        }

        return back()->with('success', 'Review rejected.');
    }

    public function destroy(Request $request, $id)
    {
        $review = ProductReview::findOrFail($id);
        $review->delete();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => 'Review deleted.']);
        }

        return redirect()->route('admin.reviews.index')->with('success', 'Review deleted successfully.');
    }
}
