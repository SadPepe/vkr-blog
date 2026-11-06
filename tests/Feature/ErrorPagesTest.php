<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_unknown_page_uses_toner_error_layout(): void
    {
        $response = $this->get('/this-page-does-not-exist');

        $response
            ->assertNotFound()
            ->assertSee('data-theme="toner"', false)
            ->assertSee('Такой страницы нет')
            ->assertSee('aria-labelledby="error-heading"', false)
            ->assertSee('href="' . url('/') . '"', false);
    }

    #[DataProvider('errorViews')]
    public function test_error_views_render_with_toner_theme(string $code): void
    {
        $html = view("errors.{$code}")->render();

        $this->assertStringContainsString('data-theme="toner"', $html);
        $this->assertStringContainsString(">{$code}</p>", $html);
        $this->assertStringContainsString('Челны', $html);
        $this->assertStringContainsString('id="error-heading"', $html);
        $this->assertStringContainsString(url('/'), $html);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function errorViews(): array
    {
        return [
            '403' => ['403'],
            '404' => ['404'],
            '419' => ['419'],
            '500' => ['500'],
            '503' => ['503'],
        ];
    }
}
