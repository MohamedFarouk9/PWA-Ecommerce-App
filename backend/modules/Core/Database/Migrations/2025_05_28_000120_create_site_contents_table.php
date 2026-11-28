<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contents', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // about, refund, privacy, etc.
            $table->longText('content');
            $table->string('version');
            $table->timestamps();
        });
    }

    /**
     * Version field serves multiple purposes:
     * 1. Cache Busting: When content is updated, a new version is generated
     * 2. Content Tracking: Helps track when content was last modified
     * The version is a random string generated using Str::random(10)
     * Example: "a1b2c3d4e5"
     */

    public function down(): void
    {
        Schema::dropIfExists('site_contents');
    }
};
