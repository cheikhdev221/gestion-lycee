<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Services/AuthorizationService.php';

$policy = new \App\Services\AuthorizationService();
$checks = [
    ['ADMIN', 'POST', '/notes/store', false],
    ['ADMIN', 'POST', '/payments/simulate', true],
    ['DIRECTEUR', 'GET', '/users/edit', false],
    ['DIRECTEUR', 'POST', '/annees/update', true],
    ['DIRECTEUR', 'POST', '/notes/update', false],
    ['DIRECTEUR', 'POST', '/payments/simulate', false],
    ['ENSEIGNANT', 'POST', '/notes/update', true],
    ['ENSEIGNANT', 'POST', '/classes/update', false],
    ['ELEVE', 'GET', '/mon-espace', true],
    ['ELEVE', 'GET', '/notes', false],
];

foreach ($checks as [$role, $method, $path, $expected]) {
    if ($policy->canAccess($role, $method, $path) !== $expected) {
        fwrite(STDERR, "Authorization check failed: $role $method $path\n");
        exit(1);
    }
}

echo "Authorization policy tests passed\n";