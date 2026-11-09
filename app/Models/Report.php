<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Жалоба пользователя на пост или комментарий.
 */
class Report extends Model
{
    use HasFactory;

    public const REASON_SPAM = 'spam';

    public const REASON_ABUSE = 'abuse';

    public const REASON_ADS = 'ads';

    public const REASON_OTHER = 'other';

    public const REASONS = [
        self::REASON_SPAM => 'Спам или реклама',
        self::REASON_ABUSE => 'Оскорбления, агрессия',
        self::REASON_ADS => 'Не по теме',
        self::REASON_OTHER => 'Другое',
    ];

    public const STATUS_NEW = 'new';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_NEW => 'Новая',
        self::STATUS_ACCEPTED => 'Принята',
        self::STATUS_REJECTED => 'Отклонена',
    ];

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'reason',
        'comment',
        'status',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NEW);
    }
}
