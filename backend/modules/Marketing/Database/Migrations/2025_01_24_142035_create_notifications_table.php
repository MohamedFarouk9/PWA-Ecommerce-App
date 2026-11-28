<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->string('type'); // order.created, promotional, etc
            $table->string('title');
            $table->text('message');
            $table->text('data')->nullable(); // JSON data
            $table->enum('status', ['pending' , 'sent' , 'failed' , 'read'])->default('pending');
            $table->string('channel')->nullable(); // email, sms, realtime
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('created_at');
            $table->index(['user_id', 'status']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('notifications');
    }
};
