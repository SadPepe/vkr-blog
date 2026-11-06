<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Вариант A: вход по логину, email — необязательный (для восстановления).
     *
     * Существующим пользователям генерируем username из локальной части email
     * (или user{id}), чтобы после миграции они могли войти.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username', 32)->nullable()->after('name');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        $used = [];

        foreach (DB::table('users')->select('id', 'name', 'email')->orderBy('id')->get() as $user) {
            $base = $user->email ? Str::before($user->email, '@') : (string) $user->name;
            $base = Str::lower(preg_replace('/[^a-zA-Z0-9]+/', '_', $base) ?? '');
            $base = trim($base, '_');

            if ($base === '' || strlen($base) < 3) {
                $base = 'user'.$user->id;
            }

            $base = substr($base, 0, 24);
            $candidate = $base;
            $suffix = 1;

            while (isset($used[$candidate])) {
                $candidate = $base.'_'.$suffix;
                $suffix++;
            }

            $used[$candidate] = true;

            DB::table('users')->where('id', $user->id)->update(['username' => $candidate]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unique('username');
        });
    }

    public function down(): void
    {
        foreach (DB::table('users')->whereNull('email')->get() as $user) {
            DB::table('users')->where('id', $user->id)->update([
                'email' => 'user'.$user->id.'@example.invalid',
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropColumn('username');
            $table->string('email')->nullable(false)->change();
        });
    }
};
