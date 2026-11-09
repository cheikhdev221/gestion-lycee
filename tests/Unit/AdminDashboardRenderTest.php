<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/src/Services/NavigationService.php';

$pageTitle = 'Vue d’ensemble';
$activeNav = 'overview';
$user = ['nom' => 'Diallo', 'prenom' => 'Aminata'];
$csrfToken = str_repeat('a', 64);
$summary = [
    'users' => [(object) ['actif' => true]],
    'annees' => [(object) ['id' => 4, 'libelle' => '2026-2027', 'statut' => 'EN_COURS']],
    'classes' => [(object) ['anneeScolaireId' => 4], (object) ['anneeScolaireId' => 3]],
    'eleves' => [(object) []],
    'enseignants' => [(object) []],
    'inscriptions' => [(object) ['statut' => 'PENDING'], (object) ['statut' => 'PAYE']],
    'evaluations' => [],
    'recentInscriptions' => [],
];

ob_start();
require BASE_PATH . '/templates/admin/dashboard.php';
$html = ob_get_clean();

$expectedContent = [
    'Vue d’ensemble',
    '2026-2027',
    'Dernières inscriptions',
    'Aucune inscription enregistrée.',
    '/assets/css/app.css',
    '2',
];
foreach ($expectedContent as $expected) {
    if (!str_contains($html, $expected)) {
        fwrite(STDERR, "Admin dashboard is missing expected content: $expected\n");
        exit(1);
    }
}

echo "Admin dashboard render test passed\n";
