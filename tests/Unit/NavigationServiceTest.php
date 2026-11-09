<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Services/NavigationService.php';

$navigation = new \App\Services\NavigationService();
$adminPaths = array_column($navigation->itemsFor('ADMIN'), 'href');
$directorPaths = array_column($navigation->itemsFor('DIRECTEUR'), 'href');
$teacherPaths = array_column($navigation->itemsFor('ENSEIGNANT'), 'href');
$studentPaths = array_column($navigation->itemsFor('ELEVE'), 'href');

$checks = [
    in_array('/users', $adminPaths, true),
    !in_array('/users', $directorPaths, true),
    !in_array('/users', $teacherPaths, true),
    $teacherPaths === ['/', '/classes', '/eleves', '/evaluations', '/notes'],
    $studentPaths === ['/mon-espace'],
    $navigation->homePathFor('ELEVE') === '/mon-espace',
];

if (in_array(false, $checks, true)) {
    fwrite(STDERR, "Role navigation checks failed\n");
    exit(1);
}

echo "Role navigation tests passed\n";
