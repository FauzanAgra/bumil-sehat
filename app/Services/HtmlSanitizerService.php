<?php

namespace App\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class HtmlSanitizerService
{
    /**
     * Tag HTML yang diizinkan untuk konten rich-text artikel (sesuai toolbar Filament RichEditor).
     *
     * @var array<string, array<int, string>>
     */
    private const ALLOWED_TAGS = [
        'p' => [],
        'br' => [],
        'hr' => [],
        'h2' => [],
        'h3' => [],
        'h4' => [],
        'strong' => [],
        'b' => [],
        'em' => [],
        'i' => [],
        'u' => [],
        'ul' => [],
        'ol' => [],
        'li' => [],
        'blockquote' => [],
        'code' => [],
        'pre' => [],
        'span' => [],
        'div' => [],
        'figure' => [],
        'figcaption' => [],
        'a' => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
    ];

    /**
     * Tag berbahaya yang harus dihapus beserta seluruh isi/anaknya.
     *
     * @var array<int, string>
     */
    private const STRIP_WITH_CONTENT_TAGS = [
        'script',
        'style',
        'iframe',
        'object',
        'embed',
        'form',
        'input',
        'button',
        'textarea',
        'select',
        'svg',
        'math',
        'template',
        'noscript',
        'meta',
        'link',
        'base',
    ];

    public function sanitize(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $previousUseErrors = libxml_use_internal_errors(true);

        try {
            $dom = new DOMDocument('1.0', 'UTF-8');
            $wrapped = '<?xml encoding="utf-8" ?><div>'.$html.'</div>';
            $dom->loadHTML($wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR | LIBXML_NOWARNING);

            // Hapus XML processing instruction yang ditambahkan untuk UTF-8
            foreach (iterator_to_array($dom->childNodes) as $child) {
                if ($child->nodeType === XML_PI_NODE) {
                    $dom->removeChild($child);
                }
            }

            $root = $dom->getElementsByTagName('div')->item(0);
            if (! $root) {
                return '';
            }

            $this->sanitizeNode($root);

            $output = '';
            foreach ($root->childNodes as $child) {
                $output .= $dom->saveHTML($child);
            }

            return trim($output);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousUseErrors);
        }
    }

    private function sanitizeNode(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $child) {
            if ($child->nodeType === XML_COMMENT_NODE) {
                $parent->removeChild($child);

                continue;
            }

            if (! $child instanceof DOMElement) {
                continue;
            }

            $tag = strtolower($child->tagName);

            if (in_array($tag, self::STRIP_WITH_CONTENT_TAGS, true)) {
                $parent->removeChild($child);

                continue;
            }

            // Sanitasi anak terlebih dahulu
            $this->sanitizeNode($child);

            if (! array_key_exists($tag, self::ALLOWED_TAGS)) {
                // Unwrap tag tidak dikenal tapi pertahankan teks/anak aman di dalamnya
                while ($child->firstChild) {
                    $parent->insertBefore($child->firstChild, $child);
                }
                $parent->removeChild($child);

                continue;
            }

            $this->sanitizeAttributes($child, self::ALLOWED_TAGS[$tag], $tag);
        }
    }

    /**
     * @param  array<int, string>  $allowedAttributes
     */
    private function sanitizeAttributes(DOMElement $element, array $allowedAttributes, string $tag): void
    {
        foreach (iterator_to_array($element->attributes) as $attr) {
            $attrName = strtolower($attr->name);
            $attrValue = trim($attr->value);

            if (! in_array($attrName, $allowedAttributes, true)) {
                $element->removeAttribute($attr->name);

                continue;
            }

            if ($attrName === 'href' && ! $this->isSafeUrl($attrValue, allowMailto: true)) {
                $element->removeAttribute($attr->name);

                continue;
            }

            if ($attrName === 'src' && ! $this->isSafeUrl($attrValue, allowMailto: false)) {
                $element->removeAttribute($attr->name);

                continue;
            }

            if ($attrName === 'target' && ! in_array($attrValue, ['_blank', '_self'], true)) {
                $element->removeAttribute($attr->name);
            }
        }

        if ($tag === 'a' && $element->getAttribute('target') === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function isSafeUrl(string $url, bool $allowMailto): bool
    {
        // Hapus karakter kontrol & whitespace tersembunyi untuk mencegah obfuscation (mis. "java\nscript:")
        $normalized = preg_replace('/[\x00-\x20\x7f]+/', '', html_entity_decode($url, ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '';

        if ($normalized === '') {
            return false;
        }

        // Izinkan relative URL aman (path atau anchor), tolak protocol-relative "//evil.com"
        if (str_starts_with($normalized, '/') && ! str_starts_with($normalized, '//')) {
            return true;
        }

        if (str_starts_with($normalized, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($normalized, PHP_URL_SCHEME));
        $allowedSchemes = $allowMailto ? ['http', 'https', 'mailto'] : ['http', 'https'];

        return in_array($scheme, $allowedSchemes, true);
    }
}
