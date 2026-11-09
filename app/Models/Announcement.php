<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Объявление администрации сайта — бегущая строка над шапкой.
 */
class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'body',
        'url',
        'is_active',
        'starts_at',
        'ends_at',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Только то, что нужно показать прямо сейчас: включено и попадает в интервал дат.
     */
    public function scopeActive(Builder $query): Builder
    {
        $now = now();

        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now));
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('sort_order')->orderByDesc('id');
    }
}
