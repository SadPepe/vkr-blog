<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Пост может быть без заголовка: в ленте показываем текст как есть.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->string('title')->nullable()->change();
        });
    }

    public function down(): void
    {
        // Пустые заголовки нельзя вернуть в NOT NULL — подставляем заглушку
        DB::table('articles')->whereNull('title')->update(['title' => 'Без названия']);

        Schema::table('articles', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
        });
    }
};
