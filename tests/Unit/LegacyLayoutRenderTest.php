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

$controller = new class extends \App\Core\Controller {
    public function showUsers(): void
    {
        $this->render('users/index', [
            'users' => [],
            'roleNames' => [],
            'currentUserId' => 0,
            'user' => ['id' => 7, 'prenom' => 'Aminata', 'nom' => 'Diallo'],
        ]);
    }
};

ob_start();
$controller->showUsers();
$html = ob_get_clean();
foreach (['bootstrap@5.3.3', '/assets/css/app.css', 'primaryNavigation', 'legacy-page', 'name="_csrf"', 'Aminata Diallo'] as $expected) {
    if (!str_contains($html, $expected)) {
        fwrite(STDERR, "Legacy layout render is missing: $expected\n");
        exit(1);
    }
}

echo "Legacy layout render test passed\n";
