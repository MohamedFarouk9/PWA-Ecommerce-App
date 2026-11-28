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
        Schema::create('visitors', function (Blueprint $table) {
            $table->id();
            $table->string('ip');
            $table->string('url');
            $table->string('referrer')->nullable(); // The previous page they came from.
            $table->string('agent');                // Information about the visitor's browser and operating system.
            $table->integer('visited_time')->nullable(); // The time the user first accessed the page.
            $table->timestamp('visited_at');  // The duration (in seconds) the visitor spent on the page.
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('visitors');
    }
};
