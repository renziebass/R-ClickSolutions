<?php
declare(strict_types=1);

namespace Mariah\Services;

/**
 * Turns operator-authored HTML into a small, known-safe subset.
 *
 * THIS IS A SECURITY BOUNDARY, and it is the only one there is. Until rich
 * text existed, every value from the database reached the public page through
 * esc(), so nothing an operator typed could ever become markup. Rich text
 * creates the first unescaped path, and there is no Content-Security-Policy
 * anywhere in this project to catch what gets through — so whatever this class
 * returns is what runs in a guest's browser.
 *
 * Two rules follow from that:
 *
 *   1. It runs on WRITE, server-side, in ResourceController. Sanitising in the
 *      browser sanitises nothing: the attacker controls the browser.
 *   2. It REBUILDS from an allowlist rather than removing what looks bad.
 *      strip_tags() is not a sanitiser — it happily keeps `onerror=` on any
 *      tag it does not remove, and a blocklist is only ever as good as
 *      yesterday's list of attacks.
 *
 * Colour is expressed as a class from a fixed palette, never a style
 * attribute. The editor emits `style="color: rgb(168,134,42)"`, this maps the
 * palette values onto classes and drops the attribute, so `style` never
 * survives to storage at all. That is what keeps the allowlist small enough to
 * reason about.
 */
final class HtmlSanitizer
{
    /** Block elements. `dir` is permitted on these, for LTR/RTL copy. */
    private const BLOCKS = ['p', 'h2', 'h3', 'ul', 'ol', 'li'];

    /** Inline elements. */
    private const INLINE = ['br', 'strong', 'em', 'u', 's', 'a', 'span'];

    /**
     * Removed with their contents. Everything else that is not allowed is
     * unwrapped instead — the tag goes, the words stay — because a guest
     * losing a paragraph to a typo'd tag is worse than losing the tag.
     */
    private const STRIP_WITH_CONTENT = [
        'script', 'style', 'iframe', 'object', 'embed', 'template',
        'noscript', 'title', 'head', 'link', 'meta', 'base', 'form',
    ];

    /** Schemes an author may link to. */
    private const URL_SCHEMES = ['http', 'https', 'mailto', 'tel'];

    /** Text colour: normalised hex => class. Mirrors the public page tokens. */
    private const TEXT_COLOURS = [
        '#0f3d3e' => 'rte-c-emerald',
        '#a8862a' => 'rte-c-gold',
        '#6a6a66' => 'rte-c-soft',
    ];

    /** Highlight colour: normalised hex => class. */
    private const HIGHLIGHTS = [
        '#e7ce7e' => 'rte-h-gold',
        '#cfe6dd' => 'rte-h-mint',
    ];

    /** Guards against a hand-crafted payload nesting until PHP runs out of stack. */
    private const MAX_DEPTH = 40;

    /** Every class this sanitiser will ever emit, for the CSS to match. */
    public static function paletteClasses(): array
    {
        return array_values(array_merge(
            array_values(self::TEXT_COLOURS),
            array_values(self::HIGHLIGHTS)
        ));
    }

    /**
     * The allowlisted subset of $html, or plain text if that cannot be
     * determined safely.
     */
    public static function clean(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        // Fail closed. A host without ext-dom cannot parse the markup, and
        // returning the input unchanged would hand an attacker the page. Text
        // is always safe, and the public page renders bare text correctly.
        if (!extension_loaded('dom')) {
            return self::toText($html);
        }

        $document = new \DOMDocument('1.0', 'UTF-8');

        // The meta charset is what makes loadHTML read UTF-8; without it
        // libxml assumes ISO-8859-1 and mangles every accented character.
        $wrapped = '<!DOCTYPE html><html><head>'
            . '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'
            . '</head><body>' . $html . '</body></html>';

        $previous = libxml_use_internal_errors(true);

        // LIBXML_NONET: never let the parser fetch an external entity.
        $loaded = $document->loadHTML($wrapped, LIBXML_NONET);

        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (!$loaded) {
            return self::toText($html);
        }

        $body = $document->getElementsByTagName('body')->item(0);

        if ($body === null) {
            return self::toText($html);
        }

        self::cleanChildren($body, 0);

        $out = '';
        foreach (iterator_to_array($body->childNodes) as $child) {
            $out .= (string) $document->saveHTML($child);
        }

        $out = trim($out);

        // "<p><br></p>" is what an editor leaves behind when someone clears the
        // field. Empty should mean empty, so the column stores NULL rather than
        // a paragraph of nothing.
        return self::toText($out) === '' && !str_contains($out, '<li') ? '' : $out;
    }

    /**
     * The readable text inside $html.
     *
     * Used wherever the words matter but the markup does not — the blog
     * auto-excerpt and the reading-time estimate, both of which would
     * otherwise count tags as words.
     */
    public static function toText(?string $html): string
    {
        $html = (string) $html;

        if (trim($html) === '') {
            return '';
        }

        // Block ends become paragraph breaks so deriveExcerpt() still sees
        // sentence structure instead of one run-on line.
        $text = preg_replace('#</(p|h2|h3|li|ul|ol|div)\s*>#i', "\n\n", $html) ?? $html;
        $text = preg_replace('#<br\s*/?>#i', "\n", $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\n{3,}/u', "\n\n", $text) ?? $text;

        return trim($text);
    }

    // -----------------------------------------------------------------

    private static function cleanChildren(\DOMNode $parent, int $depth): void
    {
        // iterator_to_array first: the live NodeList shifts underneath a
        // foreach as soon as a child is removed or unwrapped.
        foreach (iterator_to_array($parent->childNodes) as $child) {
            self::cleanNode($child, $depth);
        }
    }

    private static function cleanNode(\DOMNode $node, int $depth): void
    {
        // Text is always kept — saveHTML() escapes it on the way out.
        if ($node->nodeType === XML_TEXT_NODE) {
            return;
        }

        // Comments can carry conditional-comment payloads, and nothing an
        // operator types needs one.
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            $node->parentNode?->removeChild($node);
            return;
        }

        /** @var \DOMElement $node */
        $name = strtolower($node->nodeName);

        if (in_array($name, self::STRIP_WITH_CONTENT, true)) {
            $node->parentNode?->removeChild($node);
            return;
        }

        if ($depth >= self::MAX_DEPTH) {
            self::unwrap($node);
            return;
        }

        $allowed = in_array($name, self::BLOCKS, true)
            || in_array($name, self::INLINE, true);

        if (!$allowed) {
            // Clean what is inside before lifting it out, so a disallowed
            // wrapper cannot smuggle its children past the walk.
            self::cleanChildren($node, $depth + 1);
            self::unwrap($node);
            return;
        }

        $keep = self::cleanAttributes($node, $name);

        self::cleanChildren($node, $depth + 1);

        // A span that carried no palette class is decoration with nothing left
        // to decorate.
        if (!$keep) {
            self::unwrap($node);
        }
    }

    /**
     * Strips every attribute, then puts back only the ones this element is
     * allowed to have. Removing first is deliberate: an allowlist applied by
     * deletion can be defeated by an attribute name that normalises oddly,
     * whereas nothing survives a clean sweep it was not explicitly re-added to.
     *
     * @return bool false when the element has lost its only reason to exist
     */
    private static function cleanAttributes(\DOMElement $element, string $name): bool
    {
        $href  = null;
        $class = null;
        $dir   = null;

        foreach (iterator_to_array($element->attributes) as $attribute) {
            $attrName = strtolower($attribute->nodeName);
            $value    = $attribute->nodeValue ?? '';

            if ($attrName === 'href' && $name === 'a') {
                $href = self::safeUrl($value);
            } elseif ($attrName === 'dir' && in_array($name, self::BLOCKS, true)) {
                $lower = strtolower(trim($value));
                $dir   = in_array($lower, ['ltr', 'rtl'], true) ? $lower : null;
            } elseif ($attrName === 'class' && $name === 'span') {
                $class = self::safeClass($value);
            } elseif ($attrName === 'style' && $name === 'span') {
                // The editor writes colour as a style; it becomes a class here
                // and the attribute itself never reaches the database.
                $class ??= self::classFromStyle($value);
            }

            $element->removeAttribute($attribute->nodeName);
        }

        if ($name === 'a') {
            if ($href === null) {
                return false;   // an unlinkable link is just words
            }

            $element->setAttribute('href', $href);
            // Forced, not preserved: an author cannot opt a guest into
            // window.opener or a same-tab navigation away from the spa.
            $element->setAttribute('rel', 'noopener noreferrer');
            $element->setAttribute('target', '_blank');

            return true;
        }

        if ($name === 'span') {
            if ($class === null) {
                return false;
            }

            $element->setAttribute('class', $class);

            return true;
        }

        if ($dir !== null) {
            $element->setAttribute('dir', $dir);
        }

        return true;
    }

    /** Moves an element's children into its place and removes it. */
    private static function unwrap(\DOMNode $node): void
    {
        $parent = $node->parentNode;

        if ($parent === null) {
            return;
        }

        while ($node->firstChild !== null) {
            $parent->insertBefore($node->firstChild, $node);
        }

        $parent->removeChild($node);
    }

    /**
     * The URL if an author may link to it, else null.
     *
     * Control characters are stripped before the scheme is read, because
     * "java\0script:" and "java\tscript:" are both navigable in some browsers
     * while looking harmless to a naive prefix check.
     */
    private static function safeUrl(string $url): ?string
    {
        $url = trim(preg_replace('/[\x00-\x20\x7F]/u', '', $url) ?? '');

        if ($url === '') {
            return null;
        }

        // Relative and anchor links stay on the site and carry no scheme.
        if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
            return $url;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));

        if ($scheme === '' || !in_array($scheme, self::URL_SCHEMES, true)) {
            return null;
        }

        return $url;
    }

    /** The palette classes present in $value, in palette order. */
    private static function safeClass(string $value): ?string
    {
        $wanted = preg_split('/\s+/', trim($value)) ?: [];
        $kept   = array_values(array_intersect(self::paletteClasses(), $wanted));

        return $kept === [] ? null : implode(' ', $kept);
    }

    /** Maps a colour declaration onto a palette class, or null if off-palette. */
    private static function classFromStyle(string $style): ?string
    {
        $classes = [];

        if (preg_match('/(?<![-\w])color\s*:\s*([^;]+)/i', $style, $m) === 1) {
            $hex = self::normaliseColour($m[1]);

            if ($hex !== null && isset(self::TEXT_COLOURS[$hex])) {
                $classes[] = self::TEXT_COLOURS[$hex];
            }
        }

        if (preg_match('/background(?:-color)?\s*:\s*([^;]+)/i', $style, $m) === 1) {
            $hex = self::normaliseColour($m[1]);

            if ($hex !== null && isset(self::HIGHLIGHTS[$hex])) {
                $classes[] = self::HIGHLIGHTS[$hex];
            }
        }

        return $classes === [] ? null : implode(' ', $classes);
    }

    /**
     * "#A8862A", "#fff" and "rgb(168, 134, 42)" all reduce to "#a8862a".
     *
     * Browsers normalise an execCommand colour to rgb() before it reaches the
     * DOM, so matching hex alone would never fire in practice.
     */
    private static function normaliseColour(string $value): ?string
    {
        $value = strtolower(trim($value));

        if (preg_match('/^#([0-9a-f]{3})$/', $value, $m) === 1) {
            return '#' . $m[1][0] . $m[1][0] . $m[1][1] . $m[1][1] . $m[1][2] . $m[1][2];
        }

        if (preg_match('/^#([0-9a-f]{6})$/', $value, $m) === 1) {
            return '#' . $m[1];
        }

        if (preg_match('/^rgba?\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/', $value, $m) === 1) {
            return sprintf('#%02x%02x%02x', (int) $m[1], (int) $m[2], (int) $m[3]);
        }

        return null;
    }
}
