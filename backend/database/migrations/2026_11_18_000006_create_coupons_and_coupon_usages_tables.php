<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('coupons')) {
            Schema::create('coupons', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('description')->nullable();
                $table->enum('discount_type', ['percentage', 'fixed'])->default('percentage');
                $table->decimal('discount_amount', 10, 2);
                $table->decimal('min_spend', 10, 2)->default(0);
                $table->decimal('max_discount', 10, 2)->nullable();
                $table->integer('usage_limit')->nullable();
                $table->integer('usage_per_user')->default(1);
                $table->integer('used_count')->default(0);
                $table->dateTime('starts_at')->nullable();
                $table->dateTime('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('coupon_usages')) {
            Schema::create('coupon_usages', function (Blueprint $table) {
                $table->id();
                $table->foreignId('coupon_id')->constrained('coupons')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->foreignId('order_id')->nullable()->constrained('orders')->onDelete('set null');
                $table->decimal('discount_amount', 10, 2);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'coupon_code')) {
                    $table->string('coupon_code')->nullable()->after('total_amount');
                }
                if (!Schema::hasColumn('orders', 'discount_amount')) {
                    $table->decimal('discount_amount', 10, 2)->default(0)->after('coupon_code');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn(['coupon_code', 'discount_amount']);
            });
        }
        Schema::dropIfExists('coupon_usages');
        Schema::dropIfExists('coupons');
    }
};
