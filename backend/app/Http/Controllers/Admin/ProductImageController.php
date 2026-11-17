<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageOptimizationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    protected ImageOptimizationService $imageService;

    public function __construct(ImageOptimizationService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Get all images for a product
     */
    public function index(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $images = $product->images()
            ->orderBy('position')
            ->get()
            ->map(function ($image) {
                return [
                    'id' => $image->id,
                    'path' => $image->path,
                    'position' => $image->position,
                    'is_cover' => $image->is_cover,
                    'urls' => [
                        'original' => $this->imageService->getImageUrl($image->product_id, $image->path, 'original'),
                        'thumbnail' => $this->imageService->getImageUrl($image->product_id, $image->path, 'thumbnail'),
                        'small' => $this->imageService->getImageUrl($image->product_id, $image->path, 'small'),
                        'medium' => $this->imageService->getImageUrl($image->product_id, $image->path, 'medium'),
                        'large' => $this->imageService->getImageUrl($image->product_id, $image->path, 'large'),
                        'blur' => $this->imageService->getImageUrl($image->product_id, $image->path, 'blur'),
                    ],
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => ['images' => $images],
        ]);
    }

    /**
     * Upload new images for a product
     */
    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:5120', // 5MB max
        ]);

        $files = $request->file('images');
        $results = [];
        $errors = [];

        $currentMaxPosition = $product->images()->max('position') ?? 0;

        foreach ($files as $index => $file) {
            $result = $this->imageService->uploadProductImage($file, $productId);

            if ($result['success']) {
                $position = $currentMaxPosition + $index + 1;
                $isCover = ($position === 1 && !$product->images()->where('is_cover', true)->exists());

                $productImage = ProductImage::create([
                    'product_id' => $productId,
                    'path' => $result['original_path'],
                    'position' => $position,
                    'is_cover' => $isCover,
                ]);

                $results[] = [
                    'id' => $productImage->id,
                    'path' => $productImage->path,
                    'position' => $productImage->position,
                    'is_cover' => $productImage->is_cover,
                    'urls' => [
                        'original' => $this->imageService->getImageUrl($productId, $result['original_path'], 'original'),
                        'thumbnail' => $this->imageService->getImageUrl($productId, $result['original_path'], 'thumbnail'),
                        'small' => $this->imageService->getImageUrl($productId, $result['original_path'], 'small'),
                        'medium' => $this->imageService->getImageUrl($productId, $result['original_path'], 'medium'),
                        'large' => $this->imageService->getImageUrl($productId, $result['original_path'], 'large'),
                        'blur' => $this->imageService->getImageUrl($productId, $result['original_path'], 'blur'),
                    ],
                ];
            } else {
                $errors[] = [
                    'file' => $file->getClientOriginalName(),
                    'error' => $result['error'],
                ];
            }
        }

        return response()->json([
            'status' => empty($errors) ? 'success' : 'partial',
            'data' => [
                'uploaded' => $results,
                'errors' => $errors,
            ],
            'message' => empty($errors)
                ? count($results) . ' image(s) uploaded successfully'
                : count($results) . ' uploaded, ' . count($errors) . ' failed',
        ], empty($errors) ? 201 : 207);
    }

    /**
     * Update image (reorder, set cover, etc.)
     */
    public function update(Request $request, int $productId, int $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        $request->validate([
            'position' => 'sometimes|integer|min:1',
            'is_cover' => 'sometimes|boolean',
        ]);

        // Handle position change (reorder)
        if ($request->has('position')) {
            $newPosition = $request->input('position');
            $oldPosition = $image->position;

            if ($newPosition !== $oldPosition) {
                // Shift other images
                if ($newPosition > $oldPosition) {
                    // Moving down: shift up the ones in between
                    $product->images()
                        ->where('position', '>', $oldPosition)
                        ->where('position', '<=', $newPosition)
                        ->decrement('position');
                } else {
                    // Moving up: shift down the ones in between
                    $product->images()
                        ->where('position', '>=', $newPosition)
                        ->where('position', '<', $oldPosition)
                        ->increment('position');
                }
                $image->position = $newPosition;
            }
        }

        // Handle cover image change
        if ($request->has('is_cover') && $request->boolean('is_cover')) {
            // Remove cover from other images
            $product->images()->where('is_cover', true)->update(['is_cover' => false]);
            $image->is_cover = true;
        }

        $image->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $image->id,
                'path' => $image->path,
                'position' => $image->position,
                'is_cover' => $image->is_cover,
                'urls' => [
                    'original' => $this->imageService->getImageUrl($productId, $image->path, 'original'),
                    'thumbnail' => $this->imageService->getImageUrl($productId, $image->path, 'thumbnail'),
                    'small' => $this->imageService->getImageUrl($productId, $image->path, 'small'),
                    'medium' => $this->imageService->getImageUrl($productId, $image->path, 'medium'),
                    'large' => $this->imageService->getImageUrl($productId, $image->path, 'large'),
                    'blur' => $this->imageService->getImageUrl($productId, $image->path, 'blur'),
                ],
            ],
        ]);
    }

    /**
     * Delete an image
     */
    public function destroy(int $productId, int $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        $wasCover = $image->is_cover;
        $deletedPosition = $image->position;

        // Delete from storage
        $this->imageService->deleteProductImages($productId, $image->path);

        // Delete from database
        $image->delete();

        // If deleted image was cover, set next image as cover
        if ($wasCover) {
            $nextImage = $product->images()->orderBy('position')->first();
            if ($nextImage) {
                $nextImage->update(['is_cover' => true]);
            }
        }

        // Reorder remaining images
        $product->images()
            ->where('position', '>', $deletedPosition)
            ->decrement('position');

        return response()->json([
            'status' => 'success',
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Bulk reorder images
     */
    public function reorder(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:product_images,id',
        ]);

        $imageIds = $request->input('image_ids');

        foreach ($imageIds as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['position' => $index + 1]);
        }

        // Reload and return
        $images = $product->images()->orderBy('position')->get();

        return response()->json([
            'status' => 'success',
            'data' => ['images' => $images],
        ]);
    }

    /**
     * Set cover image
     */
    public function setCover(int $productId, int $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image = $product->images()->findOrFail($imageId);

        // Remove cover from all
        $product->images()->update(['is_cover' => false]);

        // Set new cover
        $image->update(['is_cover' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Cover image updated',
        ]);
    }
}