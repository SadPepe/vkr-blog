<?php

namespace Tests\Unit;

use App\Support\MentionRenderer;
use Tests\TestCase;

class MentionRendererTest extends TestCase
{
    public function test_mention_becomes_profile_link(): void
    {
        $html = MentionRenderer::render('<p>Привет, @test_user!</p>');

        $this->assertStringContainsString('class="mention"', $html);
        $this->assertStringContainsString(route('authors.byUsername', 'test_user'), $html);
        $this->assertStringContainsString('@test_user', $html);
    }

    public function test_mentions_inside_code_and_existing_links_are_not_duplicated(): void
    {
        $html = MentionRenderer::render(
            '<p><code>@test_user</code> <a href="/u/test_user">@test_user</a> @other_user</p>'
        );

        $this->assertSame(2, substr_count($html, '@test_user'));
        $this->assertStringContainsString(route('authors.byUsername', 'other_user'), $html);
    }

    public function test_email_like_text_is_not_treated_as_mention(): void
    {
        $html = MentionRenderer::render('<p>mail@test_user.ru</p>');

        $this->assertStringNotContainsString('class="mention"', $html);
    }
}
