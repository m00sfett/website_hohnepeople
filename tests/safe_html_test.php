<?php

declare(strict_types=1);

require __DIR__ . '/../sanitizer.php';

function assertSameOutput(string $expected, string $actual): void
{
    if ($expected !== $actual) {
        throw new RuntimeException(sprintf("Expected: %s\nActual: %s", $expected, $actual));
    }
}

$javascriptLink = '<p><a href="javascript:alert(1)" onclick="alert(2)">Evil</a></p>';
assertSameOutput('<p><a>Evil</a></p>', safeHtml($javascriptLink));

$httpLink = '<a href="https://example.com" style="color:red">Link</a>';
assertSameOutput('<a href="https://example.com">Link</a>', safeHtml($httpLink));

$plainTextWithTags = '<script>alert(1)</script><p>Safe</p>';
assertSameOutput('alert(1)<p>Safe</p>', safeHtml($plainTextWithTags));

printf("All safeHtml assertions passed.%s", PHP_EOL);
