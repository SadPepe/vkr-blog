<?php

namespace Tests\Feature;

use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_application_locale_is_russian(): void
    {
        $this->assertSame('ru', app()->getLocale());
    }

    public function test_validation_messages_are_translated(): void
    {
        $message = __('validation.required', ['attribute' => __('validation.attributes.title')]);

        $this->assertStringContainsString('заголовок', $message);
        $this->assertStringContainsString('обязательно', $message);
        $this->assertStringNotContainsString('required', $message);
    }

    public function test_pagination_links_are_rendered_in_russian(): void
    {
        $paginator = new LengthAwarePaginator(
            items: range(1, 12),
            total: 100,
            perPage: 12,
            currentPage: 2,
            options: ['path' => '/articles']
        );

        $html = $paginator->links()->toHtml();

        $this->assertStringContainsString('Назад', $html);
        $this->assertStringContainsString('Вперёд', $html);
        $this->assertStringNotContainsString('Previous', $html);
        $this->assertStringNotContainsString('Next', $html);
    }
}
