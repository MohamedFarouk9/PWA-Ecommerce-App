<?php

namespace Modules\Marketing\Database\Seeders;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductReviewSeeder extends Seeder
{
    /**
     * Seeds realistic, non-Lorem-Ipsum customer reviews with an authentic e-commerce
     * rating curve (skewed toward 4-5 stars) for products that do not yet have reviews.
     */
    public function run(): void
    {
        $userIds = User::where('role', 'user')->pluck('id')->all();

        if (empty($userIds)) {
            $this->command?->warn('ProductReviewSeeder: No customer users found — skipping.');
            return;
        }

        $positiveReviews = [
            'Exceeded my expectations right out of the box. Build quality is top notch and delivery was fast.',
            'Exactly as described in the specs. Been using it daily for three weeks with zero issues.',
            'Great value for the price — noticeably better finish and reliability than cheaper alternatives.',
            'Bought this after comparing several brands and I am really glad I went with this one.',
            'Super impressed with the performance and packaging. Would happily recommend to friends.',
            'Works seamlessly with the rest of my setup. Worth every penny.',
        ];

        $neutralReviews = [
            'Solid product overall. Does everything advertised, though the price feels slightly on the high side.',
            'Good quality and fast shipping. Took a little time to get used to, but happy with it now.',
            'Meets expectations for everyday use. Not mind-blowing, but reliable and well made.',
        ];

        $criticalReviews = [
            'Decent design, but sizing/fit ran slightly different than expected. Support helped exchange it quickly.',
            'Works fine, though I wish the included accessories were a bit sturdier for the price point.',
        ];

        $reviewsCreated = 0;

        Product::withCount('reviews')->chunkById(100, function ($products) use ($userIds, $positiveReviews, $neutralReviews, $criticalReviews, &$reviewsCreated) {
            foreach ($products as $product) {
                if ($product->reviews_count > 0) {
                    continue;
                }

                $reviewCount = rand(2, 5);
                $batch = [];

                for ($i = 0; $i < $reviewCount; $i++) {
                    // Realistic rating distribution: 50% 5-star, 30% 4-star, 12% 3-star, 5% 2-star, 3% 1-star
                    $roll = rand(1, 100);
                    if ($roll <= 50) {
                        $rating  = 5;
                        $comment = $positiveReviews[array_rand($positiveReviews)];
                    } elseif ($roll <= 80) {
                        $rating  = 4;
                        $comment = $positiveReviews[array_rand($positiveReviews)];
                    } elseif ($roll <= 92) {
                        $rating  = 3;
                        $comment = $neutralReviews[array_rand($neutralReviews)];
                    } elseif ($roll <= 97) {
                        $rating  = 2;
                        $comment = $criticalReviews[array_rand($criticalReviews)];
                    } else {
                        $rating  = 1;
                        $comment = $criticalReviews[array_rand($criticalReviews)];
                    }

                    $timestamp = now()->subDays(rand(1, 180));
                    $batch[] = [
                        'user_id'    => $userIds[array_rand($userIds)],
                        'product_id' => $product->id,
                        'rating'     => $rating,
                        'comment'    => $comment,
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ];
                }

                ProductReview::insert($batch);
                $reviewsCreated += count($batch);
            }
        });

        $this->command?->info("ProductReviewSeeder: Seeded {$reviewsCreated} realistic customer reviews!");
    }
}
