<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\SliderController;
use App\Http\Controllers\Admin\SiteController;
use App\Models\NotificationLogs;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Auth Routes (Rate Limited)
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:10,1');
Route::post('/forget-password', [AuthController::class, 'sendResetLinkEmail'])->middleware('throttle:5,1');
Route::post('/password-reset', [AuthController::class, 'passwordReset'])->middleware('throttle:5,1');

// Cart routes (Guest and Authenticated - protected against IDOR)
Route::post('/cart/add', [CartController::class, 'addToCart']);
Route::get('/cart', [CartController::class, 'getCartItems']);
Route::patch('/cart/update/{id}', [CartController::class, 'updateCart']);
Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);

// Coupon Validation & Application
Route::post('/coupons/apply', [\App\Http\Controllers\API\CouponController::class, 'apply']);

// Authenticated User Routes
Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);

    // Orders & Checkout
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::get('/orders/{id}/invoice', [OrderController::class, 'invoice']);
    Route::post('/orders', [OrderController::class, 'store']);

    // Payments (Protected against unauthorized charges)
    Route::post('/payment/charge', [PaymentController::class, 'charge'])->middleware('throttle:10,1');
    Route::post('/payment/refund', [PaymentController::class, 'refund']);

    // Reviews
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::patch('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
});

// Webhook (Public, signature-verified in gateway)
Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

// Contact
Route::post('/post-contact', [ContactController::class, 'postContact'])->middleware('throttle:5,1');

// Public Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [ApiCategoryController::class, 'index']);
    Route::get('/search', [ApiCategoryController::class, 'search']);
    Route::get('/{slug}', [ApiCategoryController::class, 'show']);
    Route::get('/{slug}/products', [ApiCategoryController::class, 'products']);
    Route::get('/{slug}/breadcrumb', [ApiCategoryController::class, 'breadcrumb']);
});

// Products
Route::get('/products', [ProductController::class, 'getAllPublished']);
Route::get('/product/{id}', [ProductController::class, 'getProductById']);
Route::get('/related-product/{id}', [ProductController::class, 'getRelatedProducts']);
Route::get('/latest-products', [ProductController::class, 'LastestProducts']);
Route::get('/products/homepage-sections', [ProductController::class, 'homepageSections']);
Route::get('/products/category/{slug}', [ProductController::class, 'getProductsByCategory']);
Route::get('/products/remark/{remark}', [ProductController::class, 'productsByRemark']);
Route::get('/products/search/{query}', [ProductController::class, 'search']);
Route::get('/search/{query}', [ProductController::class, 'search']); // Compatibility alias
Route::get('/product/{category_slug}/{subcategory_slug}', [ProductController::class, 'getProductBySubCategory']);
Route::get('/products/section/{sectionId}', [ProductController::class, 'getBySection']);

// Sliders
Route::get('/sliders', [SliderController::class, 'index']);
Route::get('/sliders/active', [SliderController::class, 'getActive']);
Route::get('/sliders/featured', [SliderController::class, 'getFeatured']);
Route::get('/sliders/position/{position}', [SliderController::class, 'getByPosition']);
Route::get('/sliders/search', [SliderController::class, 'search']);
Route::get('/sliders/{id}', [SliderController::class, 'show']);

// Product Details
Route::get('/product-details/{id}', [ProductController::class, 'getProductDetails']);

// Notifications
Route::get('/notifications', [NotificationController::class, 'index']);
Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

// Site Management
Route::get('/content/{type}', [SiteController::class, 'getContent']);
Route::post('/content/{type}', [SiteController::class, 'updateContent'])->middleware('auth:api');
Route::get('/settings', [SiteController::class, 'getSettings']);
Route::post('/settings', [SiteController::class, 'updateSettings'])->middleware('auth:api');

// Admin Routes
Route::prefix('admin')->middleware('auth:api')->group(function () {
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
    Route::post('/categories/order', [AdminCategoryController::class, 'updateOrder']);

    Route::get('/sections', [\App\Http\Controllers\Admin\ProductController::class, 'sections']);
    Route::post('/sections', [\App\Http\Controllers\Admin\ProductController::class, 'storeSection']);
    Route::put('/sections/{section}', [\App\Http\Controllers\Admin\ProductController::class, 'updateSection']);
    Route::delete('/sections/{section}', [\App\Http\Controllers\Admin\ProductController::class, 'destroySection']);
    Route::post('/sections/{section}/assign-products', [\App\Http\Controllers\Admin\ProductController::class, 'assignProducts']);
    Route::get('/sections/{section}/products', [\App\Http\Controllers\Admin\ProductController::class, 'productsBySection']);

    // Product Image Management
    Route::prefix('products/{productId}/images')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProductImageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Admin\ProductImageController::class, 'store']);
        Route::put('/{imageId}', [\App\Http\Controllers\Admin\ProductImageController::class, 'update']);
        Route::delete('/{imageId}', [\App\Http\Controllers\Admin\ProductImageController::class, 'destroy']);
        Route::post('/reorder', [\App\Http\Controllers\Admin\ProductImageController::class, 'reorder']);
        Route::post('/{imageId}/cover', [\App\Http\Controllers\Admin\ProductImageController::class, 'setCover']);
    });
});

// Health check route
Route::get('/health/notifications', function () {
    $queueSizes = [
        'email' => 0,
        'realtime' => 0,
    ];

    try {
        $consumer = app(RabbitMQConsumer::class);
        $queueSizes['email'] = $consumer->getQueueMessageCount('notifications.email.queue');
        $queueSizes['realtime'] = $consumer->getQueueMessageCount('notifications.realtime.queue');
    } catch (\Throwable $e) {
        \Log::warning('RabbitMQ health check degraded', ['error' => $e->getMessage()]);
    }

    $failedNotifications = NotificationLogs::where('status', 'failed')
        ->where('created_at', '>', now()->subHour())
        ->count();

    return response()->json([
        'status' => 'healthy',
        'queue_sizes' => $queueSizes,
        'failed_in_last_hour' => $failedNotifications,
    ]);
});

// Admin API endpoints (accessible for dashboard frontend)
Route::prefix('admin')->group(function () {
    Route::get('/dashboard/stats', [\App\Http\Controllers\Admin\DashboardController::class, 'stats']);
    Route::get('/search', [\App\Http\Controllers\Admin\DashboardController::class, 'search']);
    Route::apiResource('users', \App\Http\Controllers\Admin\UserController::class);
});
