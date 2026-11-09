<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__, 2));
spl_autoload_register(static function (string $class): void {
    if (!str_starts_with($class, 'App\\')) {
        return;
    }
    $path = BASE_PATH . '/src/' . str_replace('\\', '/', substr($class, 4)) . '.php';
    if (is_file($path)) {
        require $path;
    }
});

$service = new \App\Services\StudentRegistrationService();
$missingName = $service->register(['email' => 'eleve@example.test'], []);
if ($missingName['success'] || !str_contains($missingName['message'], 'nom et un prénom')) {
    fwrite(STDERR, "Invalid registration name was not rejected\n");
    exit(1);
}

$missingPhoto = $service->register([
    'nom' => 'Diallo',
    'prenom' => 'Aminata',
    'email' => 'eleve@example.test',
    'password' => 'long-password-2026',
    'date_naissance' => '2010-01-01',
    'role_code' => 'ADMIN',
], ['error' => UPLOAD_ERR_NO_FILE]);
if ($missingPhoto['success'] || !str_contains($missingPhoto['message'], 'photo d’identité')) {
    fwrite(STDERR, "A missing identity photo was not rejected\n");
    exit(1);
}

echo "Student registration validation tests passed\n";
