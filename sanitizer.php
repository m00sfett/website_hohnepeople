<?php

declare(strict_types=1);

function safeHtml(?string $html): string
{
    $html = (string)$html;
    if ($html === '') {
        return '';
    }

    $allowedTags = ['p', 'br', 'strong', 'em', 'a', 'ul', 'ol', 'li'];
    $allowedTagString = '<' . implode('><', $allowedTags) . '>';
    $cleaned = strip_tags($html, $allowedTagString);

    $dom = new DOMDocument();
    $previous = libxml_use_internal_errors(true);
    $dom->loadHTML('<div>' . $cleaned . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    $wrapper = $dom->getElementsByTagName('div')->item(0);
    if ($wrapper === null) {
        return '';
    }

    $allowedLookup = array_flip($allowedTags);
    $sanitizeNode = static function (DOMNode $node) use (&$sanitizeNode, $allowedLookup): void {
        if ($node->nodeType === XML_ELEMENT_NODE) {
            $tagName = strtolower($node->nodeName);
            if ($tagName !== 'div' && !isset($allowedLookup[$tagName])) {
                $textNode = $node->ownerDocument->createTextNode($node->textContent);
                $node->parentNode?->replaceChild($textNode, $node);
                return;
            }

            $href = '';
            if ($tagName === 'a') {
                $href = trim((string)$node->getAttribute('href'));
            }

            while ($node->attributes->length > 0) {
                $node->removeAttributeNode($node->attributes->item(0));
            }

            if ($tagName === 'a') {
                $parsedHref = $href !== '' ? parse_url($href) : false;
                $scheme = isset($parsedHref['scheme']) ? strtolower($parsedHref['scheme']) : '';
                if ($href !== '' && in_array($scheme, ['http', 'https'], true)) {
                    $node->setAttribute('href', $href);
                }
            }
        }

        if ($node->hasChildNodes()) {
            // Copy the child nodes to avoid skipping nodes when removing.
            $children = [];
            foreach ($node->childNodes as $child) {
                $children[] = $child;
            }
            foreach ($children as $child) {
                $sanitizeNode($child);
            }
        }
    };

    $sanitizeNode($wrapper);

    $result = '';
    foreach ($wrapper->childNodes as $child) {
        $result .= $dom->saveHTML($child);
    }

    return $result;
}
