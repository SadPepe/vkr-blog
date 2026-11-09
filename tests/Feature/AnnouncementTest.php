<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_active_announcements_reach_the_public_pages(): void
    {
        Announcement::create(['body' => 'Идёт набор авторов', 'is_active' => true]);
        Announcement::create(['body' => 'Выключенное', 'is_active' => false]);
        Announcement::create(['body' => 'Просроченное', 'is_active' => true, 'ends_at' => now()->subDay()]);
        Announcement::create(['body' => 'Будущее', 'is_active' => true, 'starts_at' => now()->addDay()]);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->component('Home')
            ->has('announcements', 1)
            ->where('announcements.0.body', 'Идёт набор авторов'));
    }

    public function test_ticker_shows_at_most_five_announcements(): void
    {
        foreach (range(1, 7) as $i) {
            Announcement::create(['body' => "Объявление {$i}", 'is_active' => true]);
        }

        $this->get('/')->assertInertia(fn (Assert $page) => $page->has('announcements', 5));
    }

    public function test_only_admin_manages_announcements(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/admin/announcements')
            ->assertOk();

        $this->actingAs(User::factory()->moderator()->create())
            ->get('/admin/announcements')
            ->assertForbidden();
    }
}
