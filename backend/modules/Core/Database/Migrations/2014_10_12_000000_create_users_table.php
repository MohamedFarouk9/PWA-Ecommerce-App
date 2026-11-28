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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            // $table->foreignId('current_team_id')->nullable();
            $table->string('image', 2048)->nullable();

             // Address Information
             $table->string('address')->nullable();
             $table->string('city')->nullable();
             $table->string('state')->nullable();
             $table->string('country')->nullable();
            //  $table->string('postal_code')->nullable();

             // Role & Status
             $table->enum('role', ['admin', 'user'])->default('user');
             $table->enum('status', ['active', 'inactive', 'banned'])->default('active');

            // Additional User Details
            //  $table->enum('gender', ['male', 'female', 'other'])->nullable();
            //  $table->date('date_of_birth')->nullable();

             $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
