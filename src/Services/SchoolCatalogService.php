<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\AnneeScolaireRepository;
use App\Repositories\ClasseRepository;
use App\Repositories\EleveRepository;
use App\Repositories\EnseignantRepository;
use App\Repositories\EvaluationRepository;
use App\Repositories\InscriptionRepository;
use App\Repositories\MatiereRepository;
use App\Repositories\NiveauRepository;
use App\Repositories\SerieRepository;
use App\Repositories\UserRepository;

final class SchoolCatalogService
{
    public function __construct(
        private readonly UserRepository $userRepository = new UserRepository(),
        private readonly AnneeScolaireRepository $anneeRepository = new AnneeScolaireRepository(),
        private readonly NiveauRepository $niveauRepository = new NiveauRepository(),
        private readonly SerieRepository $serieRepository = new SerieRepository(),
        private readonly ClasseRepository $classeRepository = new ClasseRepository(),
        private readonly MatiereRepository $matiereRepository = new MatiereRepository(),
        private readonly EleveRepository $eleveRepository = new EleveRepository(),
        private readonly EnseignantRepository $enseignantRepository = new EnseignantRepository(),
        private readonly InscriptionRepository $inscriptionRepository = new InscriptionRepository(),
        private readonly EvaluationRepository $evaluationRepository = new EvaluationRepository(),
    ) {
    }

    public function getSummary(): array
    {
        return [
            'users' => $this->userRepository->findAll(),
            'annees' => $this->anneeRepository->findAll(),
            'niveaux' => $this->niveauRepository->findAll(),
            'series' => $this->serieRepository->findAll(),
            'classes' => $this->classeRepository->findAll(),
            'matieres' => $this->matiereRepository->findAll(),
            'eleves' => $this->eleveRepository->findAll(),
            'enseignants' => $this->enseignantRepository->findAll(),
            'inscriptions' => $this->inscriptionRepository->findAll(),
            'recentInscriptions' => $this->inscriptionRepository->findRecentWithDetails(),
            'evaluations' => $this->evaluationRepository->findAll(),
        ];
    }
}
