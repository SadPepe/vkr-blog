<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Факты согласий: дата, версия документа и отдельное согласие
     * на распространение данных публичного профиля (ст. 10.1 152-ФЗ).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('terms_accepted_at')->nullable()->after('password');
            $table->string('terms_version', 20)->nullable()->after('terms_accepted_at');
            $table->timestamp('privacy_consent_at')->nullable()->after('terms_version');
            $table->string('privacy_version', 20)->nullable()->after('privacy_consent_at');
            $table->timestamp('distribution_consent_at')->nullable()->after('privacy_version');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'terms_accepted_at',
                'terms_version',
                'privacy_consent_at',
                'privacy_version',
                'distribution_consent_at',
            ]);
        });
    }
};
