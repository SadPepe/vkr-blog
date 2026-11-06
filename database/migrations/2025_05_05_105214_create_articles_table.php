<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('excerpt', 200)->after('title');
            $table->string('category_slug')->nullable();
            $table->foreign('category_slug')
      ->references('slug')
      ->on('categories')
      ->nullOnDelete(); 
            $table->string('slug')->unique(); // ЧПУ для URL
            $table->text('content');
            $table->string('card_image_path')->nullable()->after('content');
            $table->timestamps();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
