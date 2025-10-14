<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Category;
use App\Models\Article;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
     
        // 2. Делаем category_id nullable (если еще не)
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->change();
        });

        // 3. Обновляем существующие статьи
        Article::whereNull('category_id')
            ->update(['category_id' => '1']);

        // 4. Меняем обратно на not nullable
        Schema::table('articles', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable(false)->change();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            //
        });
    }
};
