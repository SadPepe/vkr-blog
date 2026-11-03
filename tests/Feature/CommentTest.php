<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

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
}
