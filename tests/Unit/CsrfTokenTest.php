<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Core/Session.php';
require __DIR__ . '/../../src/Core/CsrfToken.php';

$token = \App\Core\CsrfToken::get();
if (!\App\Core\CsrfToken::verify($token)) {
    fwrite(STDERR, "A generated CSRF token was rejected\n");
    exit(1);
}
if (\App\Core\CsrfToken::verify('invalid-token')) {
    fwrite(STDERR, "An invalid CSRF token was accepted\n");
    exit(1);
}

\App\Core\CsrfToken::rotate();
if (\App\Core\CsrfToken::verify($token)) {
    fwrite(STDERR, "A rotated CSRF token remained valid\n");
    exit(1);
}

echo "CSRF token tests passed\n";
