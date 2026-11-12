<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use PHPUnit\Framework\TestCase;

class HtmlSanitizerTest extends TestCase
{
    public function test_video_wrapper_is_kept_but_raw_iframe_is_removed(): void
    {
        $html = '<div class="video-embed video-embed--youtube" data-video-provider="youtube" data-video-id="dQw4w9WgXcQ" data-video-hash=""></div>'
            .'<iframe src="https://evil.example/embed"></iframe>';

        $result = HtmlSanitizer::sanitize($html);

        $this->assertStringContainsString('class="video-embed video-embed--youtube"', $result);
        $this->assertStringContainsString('data-video-provider="youtube"', $result);
        $this->assertStringContainsString('data-video-id="dQw4w9WgXcQ"', $result);
        $this->assertStringNotContainsString('<iframe', $result);
    }

    public function test_unknown_video_provider_and_unsafe_id_are_removed(): void
    {
        $html = '<div class="video-embed" data-video-provider="evil" data-video-id="bad id&quot;><script>alert(1)</script>"></div>';

        $result = HtmlSanitizer::sanitize($html);

        $this->assertStringNotContainsString('data-video-provider', $result);
        $this->assertStringNotContainsString('data-video-id', $result);
        $this->assertStringNotContainsString('<script', $result);
    }
}
