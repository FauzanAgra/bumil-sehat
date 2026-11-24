<?php

namespace Tests\Unit;

use App\Services\HtmlSanitizerService;
use Tests\TestCase;

class HtmlSanitizerServiceTest extends TestCase
{
    public function test_strips_xss_payloads_while_preserving_safe_article_formatting(): void
    {
        $sanitizer = app(HtmlSanitizerService::class);

        $dirty = <<<'HTML'
            <h2 onclick="alert(1)">Nutrisi Trimester 2</h2>
            <p style="color:red">Konsumsi <strong>zat besi</strong> dan <em>asam folat</em>.</p>
            <script>alert('xss')</script>
            <iframe src="https://evil.test"></iframe>
            <svg onload="alert(2)"><circle r="10"/></svg>
            <img src="x" onerror="alert(3)" alt="Ilustrasi">
            <img src="javascript:alert(4)" alt="Bad">
            <img src="/storage/articles/images/safe.jpg" alt="Aman">
            <a href="java&#x0A;script:alert(5)">Bad Link</a>
            <a href="https://kemkes.go.id" target="_blank">Kemenkes</a>
            <ul><li>Bayam</li><li>Telur</li></ul>
        HTML;

        $clean = $sanitizer->sanitize($dirty);

        $this->assertStringContainsString('<h2>Nutrisi Trimester 2</h2>', $clean);
        $this->assertStringContainsString('<strong>zat besi</strong>', $clean);
        $this->assertStringContainsString('<em>asam folat</em>', $clean);
        $this->assertStringContainsString('<ul><li>Bayam</li><li>Telur</li></ul>', $clean);
        $this->assertStringContainsString('src="/storage/articles/images/safe.jpg"', $clean);
        $this->assertStringContainsString('href="https://kemkes.go.id"', $clean);
        $this->assertStringContainsString('rel="noopener noreferrer"', $clean);

        $this->assertStringNotContainsString('<script', $clean);
        $this->assertStringNotContainsString('alert(', $clean);
        $this->assertStringNotContainsString('<iframe', $clean);
        $this->assertStringNotContainsString('<svg', $clean);
        $this->assertStringNotContainsString('onerror', $clean);
        $this->assertStringNotContainsString('onclick', $clean);
        $this->assertStringNotContainsString('style=', $clean);
        $this->assertStringNotContainsString('javascript:', $clean);
    }
}
