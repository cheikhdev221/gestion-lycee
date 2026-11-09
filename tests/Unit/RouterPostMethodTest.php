<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Core/Router.php';

$router = new \App\Core\Router();
$router->post('/login', [\stdClass::class, 'handle']);

if (!method_exists($router, 'post')) {
    fwrite(STDERR, "Router::post() is missing\n");
    exit(1);
}

echo "Router test OK\n";
