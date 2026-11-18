<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            if (!Schema::hasColumn('contacts', 'is_read')) {
                $table->boolean('is_read')->default(false)->after('message');
            }
            if (!Schema::hasColumn('contacts', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
            if (!Schema::hasColumn('contacts', 'reply_message')) {
                $table->text('reply_message')->nullable()->after('read_at');
            }
            if (!Schema::hasColumn('contacts', 'replied_at')) {
                $table->timestamp('replied_at')->nullable()->after('reply_message');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn(['is_read', 'read_at', 'reply_message', 'replied_at']);
        });
    }
};
