<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add Amazon/Noon style product details fields.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Brand and product identification
            $table->string('brand')->nullable()->after('description');
            $table->string('product_code')->nullable()->unique()->after('brand');
            $table->string('remark')->nullable()->after('product_code');

            // Pricing enhancements
            $table->decimal('special_price', 10, 2)->nullable()->after('price');
            $table->string('sku')->nullable()->after('special_price');

            // Detailed product descriptions
            $table->string('short_description')->nullable()->after('quantity');

            // Product specifications (stored as JSON for flexibility)
            $table->json('color')->nullable()->after('short_description');
            $table->json('size')->nullable()->after('color');

            // Additional details
            $table->string('meta_title')->nullable()->after('remark');
            $table->string('meta_description')->nullable()->after('meta_title');
            $table->integer('view_count')->default(0)->after('meta_description');

            // Indexing for performance
            $table->index('brand', 'idx_products_brand');
            $table->index('product_code', 'idx_products_product_code');
            $table->index('remark', 'idx_products_remark');
            $table->index('special_price', 'idx_products_special_price');
            $table->index('view_count', 'idx_products_view_count');
            $table->index(['brand', 'price'], 'idx_products_brand_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_brand');
            $table->dropIndex('idx_products_product_code');
            $table->dropIndex('idx_products_remark');
            $table->dropIndex('idx_products_special_price');
            $table->dropIndex('idx_products_view_count');
            $table->dropIndex('idx_products_brand_price');

            $table->dropColumn([
                'brand',
                'product_code',
                'remark',
                'special_price',
                'sku',
                'short_description',
                'color',
                'size',
                'meta_title',
                'meta_description',
                'view_count',
            ]);
        });
    }
};