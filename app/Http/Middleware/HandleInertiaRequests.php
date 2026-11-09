<?php

namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'can' => [
                    'moderate' => (bool) $request->user()?->canModerate(),
                    'admin' => (bool) $request->user()?->isAdmin(),
                ],
            ],
            'reportReasons' => \App\Models\Report::REASONS,
            // Бегущая строка: только актуальные, не больше пяти
            'announcements' => fn () => \App\Models\Announcement::active()
                ->ordered()
                ->limit(5)
                ->get(['id', 'body', 'url']),
            'categories' => Category::withCount('articles')->orderBy('name')->get(['id', 'name', 'slug']),
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'registrationRequiresCode' => (string) config('auth.registration_invite_code') !== '',
            'legal' => [
                'operator' => config('legal.operator'),
                'email' => config('legal.email'),
                'domain' => config('legal.domain'),
                'version' => config('legal.version'),
                'updatedAt' => config('legal.updated_at'),
            ],
        ];
    }
}
