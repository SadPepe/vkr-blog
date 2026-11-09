<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('status', 20)->default('published')->index()->after('category_slug');
            $table->timestamp('published_at')->nullable()->after('status');
        });

        // Всё, что уже опубликовано, получает дату публикации — иначе черновики её не отличат.
        DB::table('articles')->whereNull('published_at')->update(['published_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['status', 'published_at']);
        });
    }
};
