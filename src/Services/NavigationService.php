<?php

declare(strict_types=1);

namespace App\Services;

final class NavigationService
{
    private const NAVIGATION = [
        'ADMIN' => [
            ['label' => 'Vue d’ensemble', 'href' => '/admin', 'key' => 'overview'],
            ['label' => 'Élèves', 'href' => '/eleves', 'key' => 'students'],
            ['label' => 'Enseignants', 'href' => '/enseignants', 'key' => 'teachers'],
            ['label' => 'Classes', 'href' => '/classes', 'key' => 'classes'],
            ['label' => 'Inscriptions', 'href' => '/inscriptions', 'key' => 'enrollments'],
            ['label' => 'Évaluations', 'href' => '/evaluations', 'key' => 'evaluations'],
            ['label' => 'Notes', 'href' => '/notes', 'key' => 'grades'],
            ['label' => 'Paiements', 'href' => '/payments', 'key' => 'payments'],
            ['label' => 'Années scolaires', 'href' => '/annees', 'key' => 'school-years'],
            ['label' => 'Passages', 'href' => '/passages', 'key' => 'promotions'],
            ['label' => 'Utilisateurs', 'href' => '/users', 'key' => 'users'],
        ],
        'DIRECTEUR' => [
            ['label' => 'Vue d’ensemble', 'href' => '/', 'key' => 'overview'],
            ['label' => 'Élèves', 'href' => '/eleves', 'key' => 'students'],
            ['label' => 'Enseignants', 'href' => '/enseignants', 'key' => 'teachers'],
            ['label' => 'Classes', 'href' => '/classes', 'key' => 'classes'],
            ['label' => 'Inscriptions', 'href' => '/inscriptions', 'key' => 'enrollments'],
            ['label' => 'Notes', 'href' => '/notes', 'key' => 'grades'],
            ['label' => 'Paiements', 'href' => '/payments', 'key' => 'payments'],
            ['label' => 'Années scolaires', 'href' => '/annees', 'key' => 'school-years'],
            ['label' => 'Passages', 'href' => '/passages', 'key' => 'promotions'],
        ],
        'ENSEIGNANT' => [
            ['label' => 'Vue d’ensemble', 'href' => '/', 'key' => 'overview'],
            ['label' => 'Mes classes', 'href' => '/classes', 'key' => 'classes'],
            ['label' => 'Mes élèves', 'href' => '/eleves', 'key' => 'students'],
            ['label' => 'Évaluations', 'href' => '/evaluations', 'key' => 'evaluations'],
            ['label' => 'Notes', 'href' => '/notes', 'key' => 'grades'],
        ],
        'ELEVE' => [
            ['label' => 'Mon espace', 'href' => '/mon-espace', 'key' => 'student-portal'],
        ],
    ];

    private const ROLE_LABELS = [
        'ADMIN' => 'Administration',
        'DIRECTEUR' => 'Direction',
        'ENSEIGNANT' => 'Enseignant',
        'ELEVE' => 'Élève',
    ];

    private const HOME_PATHS = [
        'ADMIN' => '/admin',
        'DIRECTEUR' => '/',
        'ENSEIGNANT' => '/',
        'ELEVE' => '/mon-espace',
    ];

    public function itemsFor(string $roleCode): array
    {
        return self::NAVIGATION[strtoupper($roleCode)] ?? [];
    }

    public function labelFor(string $roleCode): string
    {
        return self::ROLE_LABELS[strtoupper($roleCode)] ?? 'Espace scolaire';
    }

    public function homePathFor(string $roleCode): string
    {
        return self::HOME_PATHS[strtoupper($roleCode)] ?? '/';
    }
}
