<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Allows each product to have multiple versions/variants.
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();  // cascadeOnDelete()
            $table->foreignId('product_attribute_id')->constrained()->restrictOnDelete();
            $table->string('value'); // Actual value (e.g., "Red", "XL")
            $table->decimal('additional_price')->nullable();  // Additional price for this variant, if any
            $table->integer('quantity')->nullable(); // Quantity available for this variant
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
