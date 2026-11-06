<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Юридические страницы доступны без авторизации.
     */
    public function test_legal_pages_are_public(): void
    {
        foreach ([
            '/privacy',
            '/terms',
            '/consent',
            '/consent-distribution',
            '/copyright',
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }

    /**
     * В общих props есть реквизиты оператора для футера и страниц.
     */
    public function test_legal_props_are_shared(): void
    {
        $this->get('/privacy')
            ->assertInertia(fn ($page) => $page
                ->component('Legal/Privacy')
                ->where('legal.email', config('legal.email'))
                ->where('legal.domain', config('legal.domain'))
            );
    }
}
