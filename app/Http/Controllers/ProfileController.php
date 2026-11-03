<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Article;
use App\Models\User;
use App\Support\PostPreview;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
            'user' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'about' => $request->user()->about,
                'avatar_url' => $request->user()->avatar_url,
                'cover_url' => $request->user()->cover_url,
                'background_url' => $request->user()->background_url,
            ],

        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validated();

        foreach (['cover_image', 'background_image'] as $field) {
            unset($data[$field], $data['remove_' . $field]);
        }

        $this->applyProfileImage($request, $user, 'cover_image', 'profile/covers');
        $this->applyProfileImage($request, $user, 'background_image', 'profile/backgrounds');

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::to('/profile')->with('success', 'Профиль обновлён');
    }

    /**
     * Загружает, заменяет или удаляет картинку профиля (обложку/фон).
     */
    private function applyProfileImage(Request $request, User $user, string $field, string $directory): void
    {
        if ($request->hasFile($field)) {
            $this->deleteProfileImage($user->{$field});
            $user->{$field} = $request->file($field)->store($directory, 'public');

            return;
        }

        if ($request->boolean('remove_' . $field)) {
            $this->deleteProfileImage($user->{$field});
            $user->{$field} = null;
        }
    }

    private function deleteProfileImage(?string $path): void
    {
        if (!$path || str_starts_with($path, 'http')) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function show(Request $request)
    {
        $user = $request->user();
        $user->append(['avatar_url', 'cover_url', 'background_url']);

        $stats = $user->articles()
            ->selectRaw('
                COUNT(*) as articles_count,
                COALESCE(SUM(views_count), 0) as views_count,
                COALESCE(SUM(likes_count), 0) as likes_count,
                COALESCE(SUM(comments_count), 0) as comments_count
            ')
            ->first();

        return Inertia::render('Profile/Show', [
            'user' => $user,
            'stats' => [
                'articles_count' => (int) $stats->articles_count,
                'views_count' => (int) $stats->views_count,
                'likes_count' => (int) $stats->likes_count,
                'comments_count' => (int) $stats->comments_count,
            ],
            'articles' => PostPreview::attachPaginator(
                $user->articles()
                    ->with('user:id,name,avatar')
                    ->latest()
                    ->select(array_merge(Article::CARD_COLUMNS, ['content']))
                    ->paginate(12)
            ),
            'media' => $user->articles()
                ->whereNotNull('card_image_path')
                ->latest()
                ->take(6)
                ->get(['id', 'slug', 'title', 'card_image_path']),
        ]);
    }
}
