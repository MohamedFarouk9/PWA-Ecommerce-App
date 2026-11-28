<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\ImageOptimizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Models\ProductImage;

class ProductImageController extends Controller
{
    public function __construct(protected ImageOptimizationService $imageService) {}

    public function index(int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $images = $product->images()
            ->orderBy('position')
            ->get()
            ->map(function ($image) {
                return [
                    'id'         => $image->id,
                    'path'       => $image->path,
                    'position'   => $image->position,
                    'is_cover'   => $image->is_cover,
                    'urls'       => [
                        'original'  => $this->imageService->getImageUrl($image->product_id, $image->path, 'original'),
                        'thumbnail' => $this->imageService->getImageUrl($image->product_id, $image->path, 'thumbnail'),
                        'small'     => $this->imageService->getImageUrl($image->product_id, $image->path, 'small'),
                        'medium'    => $this->imageService->getImageUrl($image->product_id, $image->path, 'medium'),
                        'large'     => $this->imageService->getImageUrl($image->product_id, $image->path, 'large'),
                        'blur'      => $this->imageService->getImageUrl($image->product_id, $image->path, 'blur'),
                    ],
                    'created_at' => $image->created_at,
                    'updated_at' => $image->updated_at,
                ];
            });

        return response()->json([
            'status' => 'success',
            'data'   => ['images' => $images],
        ]);
    }

    public function store(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'images'   => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $files              = $request->file('images');
        $results            = [];
        $errors             = [];
        $currentMaxPosition = $product->images()->max('position') ?? 0;

        foreach ($files as $index => $file) {
            $result = $this->imageService->uploadProductImage($file, $productId);

            if ($result['success']) {
                $position = $currentMaxPosition + $index + 1;
                $isCover  = ($position === 1 && !$product->images()->where('is_cover', true)->exists());

                $productImage = ProductImage::create([
                    'product_id' => $productId,
                    'path'       => $result['original_path'],
                    'position'   => $position,
                    'is_cover'   => $isCover,
                ]);

                $results[] = [
                    'id'       => $productImage->id,
                    'path'     => $productImage->path,
                    'position' => $productImage->position,
                    'is_cover' => $productImage->is_cover,
                    'urls'     => [
                        'original'  => $this->imageService->getImageUrl($productId, $result['original_path'], 'original'),
                        'thumbnail' => $this->imageService->getImageUrl($productId, $result['original_path'], 'thumbnail'),
                        'small'     => $this->imageService->getImageUrl($productId, $result['original_path'], 'small'),
                        'medium'    => $this->imageService->getImageUrl($productId, $result['original_path'], 'medium'),
                        'large'     => $this->imageService->getImageUrl($productId, $result['original_path'], 'large'),
                        'blur'      => $this->imageService->getImageUrl($productId, $result['original_path'], 'blur'),
                    ],
                ];
            } else {
                $errors[] = [
                    'file'  => $file->getClientOriginalName(),
                    'error' => $result['error'],
                ];
            }
        }

        return response()->json([
            'status'  => empty($errors) ? 'success' : 'partial',
            'data'    => ['uploaded' => $results, 'errors' => $errors],
            'message' => empty($errors)
                ? count($results) . ' image(s) uploaded successfully'
                : count($results) . ' uploaded, ' . count($errors) . ' failed',
        ], empty($errors) ? 201 : 207);
    }

    public function update(Request $request, int $productId, int $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image   = $product->images()->findOrFail($imageId);

        $request->validate([
            'position' => 'sometimes|integer|min:1',
            'is_cover' => 'sometimes|boolean',
        ]);

        if ($request->has('position')) {
            $newPosition = $request->input('position');
            $oldPosition = $image->position;

            if ($newPosition !== $oldPosition) {
                if ($newPosition > $oldPosition) {
                    $product->images()->where('position', '>', $oldPosition)->where('position', '<=', $newPosition)->decrement('position');
                } else {
                    $product->images()->where('position', '>=', $newPosition)->where('position', '<', $oldPosition)->increment('position');
                }
                $image->position = $newPosition;
            }
        }

        if ($request->has('is_cover') && $request->boolean('is_cover')) {
            $product->images()->where('is_cover', true)->update(['is_cover' => false]);
            $image->is_cover = true;
        }

        $image->save();

        return response()->json([
            'status' => 'success',
            'data'   => [
                'id'       => $image->id,
                'path'     => $image->path,
                'position' => $image->position,
                'is_cover' => $image->is_cover,
                'urls'     => [
                    'original'  => $this->imageService->getImageUrl($productId, $image->path, 'original'),
                    'thumbnail' => $this->imageService->getImageUrl($productId, $image->path, 'thumbnail'),
                    'small'     => $this->imageService->getImageUrl($productId, $image->path, 'small'),
                    'medium'    => $this->imageService->getImageUrl($productId, $image->path, 'medium'),
                    'large'     => $this->imageService->getImageUrl($productId, $image->path, 'large'),
                    'blur'      => $this->imageService->getImageUrl($productId, $image->path, 'blur'),
                ],
            ],
        ]);
    }

    public function destroy(int $productId, int $imageId): JsonResponse
    {
        $product         = Product::findOrFail($productId);
        $image           = $product->images()->findOrFail($imageId);
        $wasCover        = $image->is_cover;
        $deletedPosition = $image->position;

        $this->imageService->deleteProductImages($productId, $image->path);
        $image->delete();

        if ($wasCover) {
            $nextImage = $product->images()->orderBy('position')->first();
            if ($nextImage) {
                $nextImage->update(['is_cover' => true]);
            }
        }

        $product->images()->where('position', '>', $deletedPosition)->decrement('position');

        return response()->json(['status' => 'success', 'message' => 'Image deleted successfully']);
    }

    public function reorder(Request $request, int $productId): JsonResponse
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'image_ids'   => 'required|array',
            'image_ids.*' => 'integer|exists:product_images,id',
        ]);

        foreach ($request->input('image_ids') as $index => $imageId) {
            $product->images()->where('id', $imageId)->update(['position' => $index + 1]);
        }

        $images = $product->images()->orderBy('position')->get();

        return response()->json(['status' => 'success', 'data' => ['images' => $images]]);
    }

    public function setCover(int $productId, int $imageId): JsonResponse
    {
        $product = Product::findOrFail($productId);
        $image   = $product->images()->findOrFail($imageId);

        $product->images()->update(['is_cover' => false]);
        $image->update(['is_cover' => true]);

        return response()->json(['status' => 'success', 'message' => 'Cover image updated']);
    }
}
