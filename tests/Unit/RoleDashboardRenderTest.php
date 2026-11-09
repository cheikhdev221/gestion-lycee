<?php

declare(strict_types=1);

define('BASE_PATH', dirname(__DIR__, 2));
require BASE_PATH . '/src/Services/NavigationService.php';

$csrfToken = str_repeat('b', 64);
$user = ['id' => 1, 'nom' => 'Diallo', 'prenom' => 'Aminata'];

$summary = [
    'users' => [],
    'annees' => [],
    'classes' => [],
    'eleves' => [],
    'enseignants' => [],
    'inscriptions' => [],
    'evaluations' => [],
    'recentInscriptions' => [],
];
$roleCode = 'DIRECTEUR';
ob_start();
require BASE_PATH . '/templates/directeur/dashboard.php';
$directorHtml = ob_get_clean();
if (!str_contains($directorHtml, 'Passages') || !str_contains($directorHtml, 'Élèves')) {
    fwrite(STDERR, "Director dashboard navigation or data missing\n");
    exit(1);
}

$roleCode = 'ENSEIGNANT';
$classes = [];
$evaluations = [];
$notes = [];
ob_start();
require BASE_PATH . '/templates/enseignant/dashboard.php';
$teacherHtml = ob_get_clean();
if (!str_contains($teacherHtml, 'Mes classes') || !str_contains($teacherHtml, 'Saisie des notes')) {
    fwrite(STDERR, "Teacher dashboard navigation or data missing\n");
    exit(1);
}

$roleCode = 'ELEVE';
$activeNav = 'student-portal';
$pageTitle = 'Mon espace';
$portal = [
    'profile' => ['prenom' => 'Aminata', 'nom' => 'Diallo', 'matricule' => 'LYC-2026-00001', 'email' => 'a@example.test'],
    'inscriptions' => [],
    'notes' => [],
];
ob_start();
require BASE_PATH . '/templates/student-portal/index.php';
$studentHtml = ob_get_clean();
if (!str_contains($studentHtml, 'Mes notes') || !str_contains($studentHtml, 'LYC-2026-00001') || !str_contains($studentHtml, 'Mon espace')) {
    fwrite(STDERR, "Student portal render failed\n");
    exit(1);
}

echo "Role dashboards render tests passed\n";
