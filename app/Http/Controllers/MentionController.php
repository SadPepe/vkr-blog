<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MentionController extends Controller
{
    /**
     * Поиск пользователей для подсказок при вводе @username.
     */
    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => 'nullable|string|max:32',
        ]);

        $query = trim((string) ($validated['q'] ?? ''));

        if ($query === '') {
            return response()->json([]);
        }

        $pattern = '%'.$query.'%';
        $likeOp = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        return User::query()
            ->whereNull('blocked_at')
            ->where(function ($builder) use ($likeOp, $pattern) {
                $builder->where('username', $likeOp, $pattern)
                    ->orWhere('name', $likeOp, $pattern);
            })
            ->orderBy('name')
            ->limit(6)
            ->get(['id', 'name', 'username', 'avatar'])
            ->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'avatar_url' => $user->avatar_url,
            ]);
    }
}
