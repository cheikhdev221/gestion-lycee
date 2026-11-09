<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Services/StudentPhotoStorage.php';

$storage = new \App\Services\StudentPhotoStorage();
foreach (['../secret.jpg', '/etc/passwd', 'plain.jpg', str_repeat('a', 32) . '.svg'] as $invalidName) {
    if ($storage->resolve($invalidName) !== null) {
        fwrite(STDERR, "Invalid photo path was accepted\n");
        exit(1);
    }
}
if ($storage->mimeType(str_repeat('a', 32) . '.jpg') !== 'image/jpeg'
    || $storage->mimeType(str_repeat('a', 32) . '.png') !== 'image/png'
    || $storage->mimeType('unknown.bin') !== 'application/octet-stream') {
    fwrite(STDERR, "Photo MIME type mapping failed\n");
    exit(1);
}

echo "Student photo storage tests passed\n";
