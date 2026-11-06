<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Article;
use App\Models\User;
use App\Support\PostPreview;
use App\Traits\HandlesArticles;
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
    use HandlesArticles;

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
                'username' => $request->user()->username,
                'email' => $request->user()->email,
                'about' => $request->user()->about,
                'avatar_url' => $request->user()->avatar_url,
                'cover_url' => $request->user()->cover_url,
                'background_url' => $request->user()->background_url,
                'background_blur' => $request->user()->background_blur,
                'links' => $request->user()->links ?? [],
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

        foreach (['avatar_image', 'cover_image', 'background_image'] as $field) {
            unset($data[$field], $data['remove_' . $field]);
        }

        $data['links'] = $this->normalizeLinks((array) ($data['links'] ?? []));

        $this->applyProfileImage($request, $user, 'avatar_image', 'profile/avatars', 'avatar');
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
     * Ссылки автора: пустые строки отбрасываем, схему подставляем, название берём из домена.
     *
     * @param  array<int, array{label?: ?string, url?: ?string}>  $links
     * @return array<int, array{label: string, url: string}>|null
     */
    private function normalizeLinks(array $links): ?array
    {
        $normalized = [];

        foreach ($links as $link) {
            $url = trim((string) ($link['url'] ?? ''));

            if ($url === '') {
                continue;
            }

            if (!preg_match('#^https?://#i', $url)) {
                $url = 'https://' . ltrim($url, '/');
            }

            $label = trim((string) ($link['label'] ?? ''));
            if ($label === '') {
                $label = (string) (parse_url($url, PHP_URL_HOST) ?? '');
                $label = preg_replace('/^www\./', '', $label);
            }

            $normalized[] = ['label' => $this->limit($label, 40), 'url' => $this->limit($url, 255)];

            if (count($normalized) >= 5) {
                break;
            }
        }

        return $normalized ?: null;
    }

    /**
     * Обрезка строки без mbstring-сюрпризов для метки/адреса.
     */
    private function limit(string $value, int $length): string
    {
        return \Illuminate\Support\Str::limit($value, $length, '');
    }

    /**
     * Загружает, заменяет или удаляет картинку профиля (аватар/обложку/фон).
     */
    private function applyProfileImage(
        Request $request,
        User $user,
        string $field,
        string $directory,
        ?string $column = null,
    ): void {
        $column ??= $field;

        if ($request->hasFile($field)) {
            $this->deleteProfileImage($user->{$column});
            $user->{$column} = $request->file($field)->store($directory, 'public');

            return;
        }

        if ($request->boolean('remove_' . $field)) {
            $this->deleteProfileImage($user->{$column});
            $user->{$column} = null;
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

        // Сначала файлы: после удаления пользователя статьи и изображения
        // останутся в базе без владельца, а файлы — на диске.
        $user->articles()->get()->each(function (Article $article) {
            $this->deleteArticleFiles($article);
        });

        $this->deleteProfileImage($user->avatar);
        $this->deleteProfileImage($user->cover_image);
        $this->deleteProfileImage($user->background_image);

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
                    ->with(['user:id,name,avatar', 'latestComment.user:id,name,avatar'])
                    ->latest()
                    ->select(array_merge(Article::CARD_COLUMNS, ['content']))
                    ->paginate(12)
            ),
            // Лучшие статьи автора: рейтинг по просмотрам вместо прежней медиасетки
            'topArticles' => \App\Models\Article::topFor($user),
        ]);
    }
}
