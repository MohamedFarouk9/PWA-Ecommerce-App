<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to create product specifications pivot table.
     * This allows products to have specific attribute VALUES (e.g., "Blue", "Large").
     */
    public function up(): void
    {
        Schema::create('product_specifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_attribute_id')->constrained()->cascadeOnDelete();
            $table->string('value'); // The actual value: "Blue", "Large", "1000mAh"
            $table->string('unit')->nullable(); // For units: "GB", "kg", "inches"
            $table->integer('position')->default(0); // For ordering specifications
            $table->timestamps();

            // Ensure each product has unique attribute + value combination
            $table->unique(['product_id', 'product_attribute_id', 'value'], 'unique_product_attribute_value');

            // Indexes for performance
            $table->index(['product_id', 'position'], 'idx_specs_product_position');
            $table->index('value', 'idx_specs_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specifications');
    }
};