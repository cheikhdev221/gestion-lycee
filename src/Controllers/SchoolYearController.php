<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\AnneeScolaire;
use App\Repositories\AnneeScolaireRepository;

final class SchoolYearController extends Controller
{
    public function __construct(
        private readonly AnneeScolaireRepository $repository = new AnneeScolaireRepository(),
    ) {
    }

    public function index(): void
    {
        $this->render('school-years/index', [
            'annees' => $this->repository->findAll(),
        ]);
    }

    public function create(): void
    {
        $this->render('school-years/create', [
            'error' => null,
            'success' => null,
        ]);
    }

    public function edit(): void
    {
        $annee = $this->repository->findById((int) ($_GET['id'] ?? 0));
        if ($annee === null) {
            http_response_code(404);
            echo 'Année scolaire introuvable.';
            return;
        }

        $this->render('school-years/edit', ['annee' => $annee, 'error' => null]);
    }

    public function update(): void
    {
        $id = (int) ($_POST['id'] ?? 0);
        $current = $this->repository->findById($id);
        if ($current === null) {
            http_response_code(404);
            echo 'Année scolaire introuvable.';
            return;
        }

        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $dateDebut = trim((string) ($_POST['date_debut'] ?? ''));
        $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
        $statut = strtoupper(trim((string) ($_POST['statut'] ?? '')));
        $allowedTransitions = [
            'A_VENIR' => ['A_VENIR', 'EN_COURS'],
            'EN_COURS' => ['EN_COURS', 'TERMINEE'],
            'TERMINEE' => ['TERMINEE'],
        ];

        $dateDebutValue = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateDebut);
        $dateFinValue = \DateTimeImmutable::createFromFormat('!Y-m-d', $dateFin);
        if ($libelle === '' || $dateDebutValue === false || $dateDebutValue->format('Y-m-d') !== $dateDebut
            || $dateFinValue === false || $dateFinValue->format('Y-m-d') !== $dateFin
            || $dateFinValue <= $dateDebutValue) {
            $this->render('school-years/edit', ['annee' => $current, 'error' => 'Libellé ou dates invalides.']);
            return;
        }
        if (!in_array($statut, $allowedTransitions[$current->statut] ?? [], true)) {
            $this->render('school-years/edit', ['annee' => $current, 'error' => 'Transition de statut non autorisée.']);
            return;
        }

        $hasClasses = $this->repository->hasClasses($id);
        if ($hasClasses && ($libelle !== $current->libelle || $dateDebut !== $current->dateDebut || $dateFin !== $current->dateFin)) {
            $this->render('school-years/edit', ['annee' => $current, 'error' => 'Le libellé et les dates sont verrouillés dès que des classes sont rattachées.']);
            return;
        }

        try {
            $this->repository->update(new AnneeScolaire(
                id: $id,
                libelle: $libelle,
                dateDebut: $dateDebut,
                dateFin: $dateFin,
                statut: $statut,
            ));
            header('Location: /annees');
        } catch (\Throwable $exception) {
            $this->render('school-years/edit', ['annee' => $current, 'error' => 'La modification a échoué. Vérifie les dates et les chevauchements.']);
        }
    }

    public function store(): void
    {
        $libelle = trim((string) ($_POST['libelle'] ?? ''));
        $dateDebut = trim((string) ($_POST['date_debut'] ?? ''));
        $dateFin = trim((string) ($_POST['date_fin'] ?? ''));
        $statut = strtoupper(trim((string) ($_POST['statut'] ?? 'A_VENIR')));

        if ($libelle === '' || $dateDebut === '' || $dateFin === '') {
            $this->render('school-years/create', ['error' => 'Tous les champs sont obligatoires.', 'success' => null]);
            return;
        }

        $annee = new AnneeScolaire(
            libelle: $libelle,
            dateDebut: $dateDebut,
            dateFin: $dateFin,
            statut: $statut,
        );

        try {
            $this->repository->create($annee);
            $this->render('school-years/create', ['error' => null, 'success' => 'Année scolaire créée.']);
        } catch (\Throwable $e) {
            $this->render('school-years/create', ['error' => $e->getMessage(), 'success' => null]);
        }
    }
}
