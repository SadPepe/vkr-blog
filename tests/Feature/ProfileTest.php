<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_author_links_are_normalized_and_saved(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'links' => [
                    ['label' => 'Telegram', 'url' => 't.me/moyblog'],
                    ['label' => '', 'url' => 'https://example.com'],
                    ['label' => 'Пустая', 'url' => ''],
                ],
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $links = $user->refresh()->links;

        $this->assertCount(2, $links);
        $this->assertSame('https://t.me/moyblog', $links[0]['url']);
        $this->assertSame('Telegram', $links[0]['label']);
        // название не заполнено — подставляется домен
        $this->assertSame('example.com', $links[1]['label']);
    }

    public function test_invalid_author_link_is_rejected(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'links' => [['label' => 'Битое', 'url' => 'не ссылка']],
            ]);

        $response->assertSessionHasErrors('links.0.url');
        $this->assertNull($user->refresh()->links);
    }

    public function test_background_blur_can_be_updated(): void
    {
        $user = User::factory()->create();
        $this->assertSame(12, $user->refresh()->background_blur);

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'background_blur' => 20,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertSame(20, $user->refresh()->background_blur);
    }

    public function test_avatar_can_be_uploaded_and_removed(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                // Настоящий PNG: валиден и там, где нет GD (на хосте — фолбэк ImageStore)
                'avatar_image' => UploadedFile::fake()->createWithContent(
                    'avatar.png',
                    base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='),
                ),
            ]);

        $response->assertSessionHasNoErrors()->assertRedirect('/profile');

        $user->refresh();

        $this->assertNotNull($user->avatar);
        Storage::disk('public')->assertExists($user->avatar);

        $path = $user->avatar;

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'remove_avatar_image' => true,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->avatar);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_avatar_must_be_an_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'avatar_image' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
            ]);

        $response->assertSessionHasErrors('avatar_image');

        $this->assertNull($user->refresh()->avatar);
    }

    public function test_profile_edit_page_exposes_background_blur(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile/edit');

        $response->assertOk()->assertSee('background_blur', false);
    }

    public function test_background_blur_must_be_within_range(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
                'background_blur' => 25,
            ]);

        $response->assertSessionHasErrors('background_blur');

        $this->assertSame(12, $user->refresh()->background_blur);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->put('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile/edit', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_deleting_account_removes_profile_and_article_files(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'avatar' => 'profile/avatars/avatar.jpg',
            'cover_image' => 'profile/covers/cover.jpg',
            'background_image' => 'profile/backgrounds/background.jpg',
        ]);

        Article::factory()->for($user)->create([
            'content' => '<p><img src="/storage/images/post.jpg"></p>',
            'card_image_path' => '/storage/images/card.jpg',
        ]);

        $files = [
            'profile/avatars/avatar.jpg',
            'profile/covers/cover.jpg',
            'profile/backgrounds/background.jpg',
            'images/post.jpg',
            'images/card.jpg',
        ];

        foreach ($files as $file) {
            Storage::disk('public')->put($file, 'test');
        }

        $this->actingAs($user)
            ->delete('/profile/edit', ['password' => 'password'])
            ->assertRedirect('/');

        foreach ($files as $file) {
            Storage::disk('public')->assertMissing($file);
        }

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile/edit', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrors('password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_own_author_page_redirects_to_profile(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->get(route('authors.show', $user))
            ->assertRedirect(route('profile'));
    }

    public function test_other_author_page_is_displayed(): void
    {
        $author = User::factory()->create();
        $viewer = User::factory()->create();

        $this
            ->actingAs($viewer)
            ->get(route('authors.show', $author))
            ->assertOk();
    }
}
