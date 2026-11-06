<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ссылки автора: массив пар «название + адрес» (до пяти), показывается в профиле.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('links')->nullable()->after('background_blur');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('links');
        });
    }
};
