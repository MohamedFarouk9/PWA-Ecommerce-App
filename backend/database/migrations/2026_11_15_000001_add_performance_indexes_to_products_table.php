<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to optimize high-load queries.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Composite index for category listing & status filter
            $table->index(['product_category_id', 'product_status_id'], 'idx_products_cat_status');

            // Composite index for latest published products pagination
            $table->index(['product_status_id', 'created_at'], 'idx_products_status_created');

            // Price indexing for range and sorting queries
            $table->index('price', 'idx_products_price');

            // Name index for prefix search queries
            $table->index('name', 'idx_products_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('idx_products_cat_status');
            $table->dropIndex('idx_products_status_created');
            $table->dropIndex('idx_products_price');
            $table->dropIndex('idx_products_name');
        });
    }
};
