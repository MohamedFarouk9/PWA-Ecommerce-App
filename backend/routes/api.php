<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\CategoryController as ApiCategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\API\ProductController;

use App\Http\Controllers\Admin\ProductDetailsController;
// use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\SliderController;
use App\Models\NotificationLogs;
use App\Services\RabbitMQ\RabbitMQConsumer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::middleware([TrackVisitor::class])->get('/track-visitor', function () {
//     return response()->json(['message' => 'Visitor tracked successfully.']);
// });

//Auth

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Password Reset Routes
Route::post('/forget-password', [AuthController::class, 'sendResetLinkEmail']);
Route::post('/password-reset', [AuthController::class, 'passwordReset']);
// Email Verification Routes
// Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');
// Route::post('/email/resend', [AuthController::class, 'resendVerificationEmail'])->middleware('auth:api')->name('verification.resend');

// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return response()->json(['user' => auth()->guard('api')->user()]);
// });

Route::middleware('auth:api')->group(function () {
    Route::get('/user', [AuthController::class, 'userProfile']);

    //reviews
    Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::patch('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);

    //cart
    Route::post('/cart/add', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCartItems']);
    Route::patch('/cart/update/{id}', [CartController::class, 'updateCart']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'removeFromCart']);

});

// Public Category Routes
Route::prefix('categories')->group(function () {
    Route::get('/', [ApiCategoryController::class, 'index']);
    Route::get('/search', [ApiCategoryController::class, 'search']);
    Route::get('/{slug}', [ApiCategoryController::class, 'show']);
    Route::get('/{slug}/products', [ApiCategoryController::class, 'products']);
    Route::get('/{slug}/breadcrumb', [ApiCategoryController::class, 'breadcrumb']);
});




Route::post('/payment/charge', [PaymentController::class, 'charge']);
Route::post('/payment/webhook', [PaymentController::class, 'handleWebhook']);

Route::post('/post-contact', [ContactController::class, 'postContact']);

//products
Route::get('/products', [ProductController::class, 'getAllPublished']);
Route::get('/product/{id}', [ProductController::class, 'getProductById']);
Route::get('/related-product/{id}', [ProductController::class, 'RelatedProducts']);
Route::get('/latest-products', [ProductController::class, 'LastestProducts']);
Route::get('/products/homepage-sections', [ProductController::class, 'homepageSections']);
Route::get('/products/category/{slug}', [ProductController::class, 'getProductsByCategory']);
Route::get('/products/remark/{remark}', [ProductController::class, 'productsByRemark']);
Route::get('/products/search/{query}', [ProductController::class, 'ProductBySearh']);
Route::get('/product/{category_slug}/{subcategory_slug}', [ProductController::class, 'getProductBySubCategory']);
Route::get('/products/section/{sectionId}', [ProductController::class, 'getBySection']); // New

// Route::get('/products/featured', [ProductController::class, 'getFeatured']);


//slider
Route::get('/sliders', [SliderController::class, 'index']);
Route::get('/sliders/active', [SliderController::class, 'getActive']);
Route::get('/sliders/featured', [SliderController::class, 'getFeatured']);
Route::get('/sliders/position/{position}', [SliderController::class, 'getByPosition']);
Route::get('/sliders/search', [SliderController::class, 'search']);
Route::get('/sliders/{id}', [SliderController::class, 'show']);

//product_details
Route::get('/product-details/{id}', [ProductDetailsController::class, 'productDetails']);
Route::get('/related-product/{product_id}', [ProductDetailsController::class, 'relatedProduct']);

//notifications
Route::get('/notifications', [NotificationController::class, 'index']);
Route::put('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);

// Site management routes
Route::get('/content/{type}', [SiteController::class, 'getContent']);
Route::post('/content/{type}', [SiteController::class, 'updateContent'])->middleware('auth:api');
Route::get('/settings', [SiteController::class, 'getSettings']);
Route::post('/settings', [SiteController::class, 'updateSettings'])->middleware('auth:api');

// Track visitor middleware
// Route::middleware([TrackVisitor::class])->group(function () {
//     // Add any routes that need to track visitors here
// });
//php artisan passport:client --personal

// Admin Category Routes
Route::prefix('admin')->middleware('auth:api')->group(function () {
    //categories
    Route::get('/categories', [AdminCategoryController::class, 'index']);
    Route::post('/categories', [AdminCategoryController::class, 'store']);
    Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
    Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
    Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);
    Route::post('/categories/order', [AdminCategoryController::class, 'updateOrder']);

    //sections
    Route::get('/sections', [\App\Http\Controllers\Admin\ProductController::class, 'sections']);
    Route::post('/sections', [\App\Http\Controllers\Admin\ProductController::class, 'storeSection']);
    Route::put('/sections/{section}', [\App\Http\Controllers\Admin\ProductController::class, 'updateSection']);
    Route::delete('/sections/{section}', [\App\Http\Controllers\Admin\ProductController::class, 'destroySection']);
    Route::post('/sections/{section}/assign-products', [\App\Http\Controllers\Admin\ProductController::class, 'assignProducts']);
    Route::get('/sections/{section}/products', [\App\Http\Controllers\Admin\ProductController::class, 'productsBySection']);
});

// health check route
Route::get('/health/notifications', function () {
    $queueSizes = [
        'email' => app(RabbitMQConsumer::class)
            ->getQueueMessageCount('notifications.email.queue'),
        'realtime' => app(RabbitMQConsumer::class)
            ->getQueueMessageCount('notifications.realtime.queue'),
    ];

    $failedNotifications = NotificationLogs::where('status', 'failed')
        ->where('created_at', '>', now()->subHour())
        ->count();

    return response()->json([
        'status' => 'healthy',
        'queue_sizes' => $queueSizes,
        'failed_in_last_hour' => $failedNotifications,
    ]);
});

