<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Отдельный индекс по created_at: чистка article_views фильтрует по нему,
     * а составной (article_id, created_at) для такого delete не подходит.
     */
    public function up(): void
    {
        Schema::table('article_views', function (Blueprint $table) {
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('article_views', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });
    }
};
