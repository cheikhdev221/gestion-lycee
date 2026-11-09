<?php

declare(strict_types=1);

namespace App\Services;

use finfo;

final class StudentPhotoStorage
{
    private const MAX_BYTES = 2_000_000;
    private const MAX_DIMENSION = 6000;
    private const MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(array $upload): string
    {
        if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \InvalidArgumentException('Ajoute une photo d’identité au format JPEG, PNG ou WebP.');
        }

        $temporaryPath = (string) ($upload['tmp_name'] ?? '');
        $size = (int) ($upload['size'] ?? 0);
        if ($temporaryPath === '' || !is_uploaded_file($temporaryPath) || $size <= 0 || $size > self::MAX_BYTES) {
            throw new \InvalidArgumentException('La photo doit faire moins de 2 Mo.');
        }

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($temporaryPath);
        $extension = self::MIME_EXTENSIONS[$mimeType] ?? null;
        $dimensions = getimagesize($temporaryPath);
        if ($extension === null || $dimensions === false
            || $dimensions[0] > self::MAX_DIMENSION || $dimensions[1] > self::MAX_DIMENSION) {
            throw new \InvalidArgumentException('Format de photo non accepté. Utilise JPEG, PNG ou WebP.');
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $extension;
        $directory = self::directory();
        if (!is_dir($directory) && !mkdir($directory, 0770, true) && !is_dir($directory)) {
            throw new \RuntimeException('Le stockage des photos est indisponible.');
        }

        $destination = $directory . DIRECTORY_SEPARATOR . $filename;
        if (!move_uploaded_file($temporaryPath, $destination)) {
            throw new \RuntimeException('La photo n’a pas pu être enregistrée.');
        }

        chmod($destination, 0640);

        return $filename;
    }

    public function resolve(?string $filename): ?string
    {
        if ($filename === null || !preg_match('/^[a-f0-9]{32}\.(jpg|png|webp)$/', $filename)) {
            return null;
        }

        $path = self::directory() . DIRECTORY_SEPARATOR . $filename;

        return is_file($path) ? $path : null;
    }

    public function mimeType(string $filename): string
    {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            default => 'application/octet-stream',
        };
    }

    public function delete(string $filename): void
    {
        $path = $this->resolve($filename);
        if ($path !== null) {
            unlink($path);
        }
    }

    private static function directory(): string
    {
        return BASE_PATH . '/storage/student-photos';
    }
}