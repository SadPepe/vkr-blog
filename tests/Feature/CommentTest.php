<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    private function fakePng(string $name = 'photo.png'): UploadedFile
    {
        $binary = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        return UploadedFile::fake()->createWithContent($name, $binary);
    }

    public function test_guest_cannot_comment(): void
    {
        $article = Article::factory()->create(['slug' => 'guest-comment']);

        $this->post(route('comments.store', $article->slug), ['content' => 'Привет'])
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_is_created_and_counter_grows(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'comment-counter']);

        $this->actingAs($user)
            ->post(route('comments.store', $article->slug), ['content' => 'Первый комментарий'])
            ->assertRedirect();

        $this->assertDatabaseHas('comments', [
            'article_id' => $article->id,
            'user_id' => $user->id,
            'content' => 'Первый комментарий',
            'parent_id' => null,
        ]);
        $this->assertSame(1, $article->refresh()->comments_count);
    }

    public function test_reply_does_not_increase_comments_counter(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'comment-reply']);

        $this->actingAs($user)->post(route('comments.store', $article->slug), ['content' => 'Родитель']);
        $parent = Comment::firstOrFail();

        $this->actingAs($user)->post(route('comments.store', $article->slug), [
            'content' => 'Ответ',
            'parent_id' => $parent->id,
        ]);

        $reply = Comment::where('content', 'Ответ')->firstOrFail();

        $this->assertSame($parent->id, $reply->parent_id);
        // Счётчик статьи считает только комментарии верхнего уровня
        $this->assertSame(1, $article->refresh()->comments_count);
    }

    public function test_author_can_update_own_comment(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create();
        $comment = Comment::factory()->for($article)->for($user)->create(['content' => 'Старый текст']);

        $this->actingAs($user)
            ->put(route('comments.update', $comment), ['content' => 'Новый текст'])
            ->assertRedirect();

        $this->assertSame('Новый текст', $comment->fresh()->content);
    }

    public function test_other_user_cannot_update_comment(): void
    {
        $comment = Comment::factory()->create(['content' => 'Чужой текст']);
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->put(route('comments.update', $comment), ['content' => 'Взлом'])
            ->assertForbidden();

        $this->assertSame('Чужой текст', $comment->fresh()->content);
    }

    public function test_author_can_delete_own_comment_and_counter_drops(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['comments_count' => 1]);
        $comment = Comment::factory()->for($article)->for($user)->create();

        $this->actingAs($user)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertSame(0, $article->refresh()->comments_count);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();
        $stranger = User::factory()->create();

        $this->actingAs($stranger)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id]);
    }

    public function test_hidden_reply_is_not_shown_on_article_page(): void
    {
        $author = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'hidden-reply']);
        $parent = Comment::factory()->for($article)->for($author)->create(['content' => 'Родитель']);
        $reply = Comment::factory()->for($article)->for($author)->create([
            'content' => 'Скрытый ответ',
            'parent_id' => $parent->id,
            'hidden_at' => now(),
        ]);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Articles/Show')
                ->has('article.comments', 1)
                ->has('article.comments.0.replies', 0));

        $this->assertDatabaseHas('comments', ['id' => $reply->id]);
    }

    public function test_comments_are_ordered_newest_first(): void
    {
        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'comments-order']);
        $older = Comment::factory()->for($article)->for($user)->create([
            'content' => 'Старый',
            'created_at' => now()->subHour(),
        ]);
        $newer = Comment::factory()->for($article)->for($user)->create(['content' => 'Новый']);

        $this->get(route('articles.show', $article->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Articles/Show')
                ->where('article.comments.0.id', $newer->id)
                ->where('article.comments.1.id', $older->id));
    }

    public function test_comment_with_photo_only_is_created(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $article = Article::factory()->create(['slug' => 'photo-comment']);

        $this->actingAs($user)
            ->post(route('comments.store', $article->slug), [
                'content' => '',
                'image' => $this->fakePng(),
            ])
            ->assertRedirect();

        $comment = Comment::firstOrFail();

        $this->assertSame('', $comment->content);
        $this->assertNotNull($comment->image_path);
        $this->assertNotNull($comment->image_url);
        Storage::disk('public')->assertExists($comment->image_path);
    }

    public function test_comment_without_text_and_photo_is_rejected(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $article = Article::factory()->create();

        $this->actingAs($user)
            ->post(route('comments.store', $article->slug), ['content' => '   '])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_photo_is_deleted_with_comment(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $article = Article::factory()->create();
        $comment = Comment::factory()->for($article)->for($user)->create([
            'content' => 'С фото',
            'image_path' => 'comments/test-photo.webp',
        ]);
        Storage::disk('public')->put($comment->image_path, 'image');

        $this->actingAs($user)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect();

        Storage::disk('public')->assertMissing('comments/test-photo.webp');
    }

    public function test_author_can_replace_and_remove_comment_photo(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $article = Article::factory()->create();
        $comment = Comment::factory()->for($article)->for($user)->create([
            'content' => 'С фото',
            'image_path' => 'comments/old-photo.webp',
        ]);
        Storage::disk('public')->put('comments/old-photo.webp', 'image');

        $this->actingAs($user)
            ->put(route('comments.update', $comment), ['content' => 'С новым фото', 'image' => $this->fakePng()])
            ->assertRedirect();

        $comment->refresh();
        $newPath = $comment->image_path;
        $this->assertNotSame('comments/old-photo.webp', $newPath);
        Storage::disk('public')->assertMissing('comments/old-photo.webp');
        Storage::disk('public')->assertExists($newPath);

        $this->actingAs($user)
            ->put(route('comments.update', $comment), ['content' => 'Без фото', 'remove_image' => true])
            ->assertRedirect();

        $this->assertNull($comment->fresh()->image_path);
        Storage::disk('public')->assertMissing($newPath);
    }
}
