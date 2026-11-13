<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Закреплённый пост автора: один на автора, поднимается над остальными
            // в профиле и на публичной странице автора
            $table->boolean('is_pinned')->default(false)->after('published_at');
            $table->timestamp('pinned_at')->nullable()->after('is_pinned');

            $table->index(['user_id', 'is_pinned']);
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_pinned']);
            $table->dropColumn(['is_pinned', 'pinned_at']);
        });
    }
};
