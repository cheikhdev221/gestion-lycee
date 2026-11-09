<?php

declare(strict_types=1);

$testFiles = array_merge(
    glob(__DIR__ . '/Unit/*.php') ?: [],
    glob(__DIR__ . '/Integration/*.php') ?: [],
);
sort($testFiles);

foreach ($testFiles as $testFile) {
    passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($testFile), $exitCode);
    if ($exitCode !== 0) {
        exit($exitCode);
    }
}

echo count($testFiles) . " test(s) passed\n";