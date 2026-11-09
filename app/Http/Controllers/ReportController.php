<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReportController extends Controller
{
    /**
     * Разрешённые цели жалобы: тип из запроса → модель.
     */
    private const TYPES = [
        'article' => Article::class,
        'comment' => Comment::class,
    ];

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reportable_type' => ['required', Rule::in(array_keys(self::TYPES))],
            'reportable_id' => ['required', 'integer'],
            'reason' => ['required', Rule::in(array_keys(Report::REASONS))],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        /** @var Article|Comment $target */
        $target = self::TYPES[$validated['reportable_type']]::findOrFail($validated['reportable_id']);

        if ((int) $target->user_id === (int) $request->user()->id) {
            return back()->with('error', 'На собственный материал жаловаться нельзя.');
        }

        $alreadyReported = Report::query()
            ->where('reporter_id', $request->user()->id)
            ->where('reportable_type', $target->getMorphClass())
            ->where('reportable_id', $target->id)
            ->where('status', Report::STATUS_NEW)
            ->exists();

        if ($alreadyReported) {
            return back()->with('error', 'Жалоба уже отправлена и ждёт рассмотрения.');
        }

        Report::create([
            'reporter_id' => $request->user()->id,
            'reportable_type' => $target->getMorphClass(),
            'reportable_id' => $target->id,
            'reason' => $validated['reason'],
            'comment' => $validated['comment'] ?? null,
            'status' => Report::STATUS_NEW,
        ]);

        return back()->with('success', 'Жалоба отправлена модератору.');
    }
}
