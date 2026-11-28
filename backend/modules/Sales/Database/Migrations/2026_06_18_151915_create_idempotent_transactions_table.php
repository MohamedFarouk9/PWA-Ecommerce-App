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
        Schema::create('idempotent_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('idempotency_key')->unique(); // Must be unique!
            $table->string('gateway'); // stripe, paymob, etc.
            $table->string('order_id');
            $table->string('customer_id');
            $table->string('request_hash'); // Hash of request data
            $table->string('transaction_id')->nullable();
            $table->string('status'); // pending, completed, failed
            $table->json('response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Indexes for fast lookups
            $table->index('idempotency_key');
            $table->index('order_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('idempotent_transactions');
    }
};
