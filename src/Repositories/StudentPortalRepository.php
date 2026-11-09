<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class StudentPortalRepository
{
    public function findForUser(int $userId): ?array
    {
        $connection = Database::getConnection();
        $statement = $connection->prepare(
            'SELECT e.id, e.matricule, e.date_naissance, e.photo_path, u.nom, u.prenom, u.email
             FROM eleves e
             INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
             WHERE e.utilisateur_id = :user_id'
        );
        $statement->execute(['user_id' => $userId]);
        $profile = $statement->fetch();

        if ($profile === false) {
            return null;
        }

        $statement = $connection->prepare(
            "SELECT i.id, i.statut, i.date_inscription, c.libelle AS classe, a.libelle AS annee_scolaire,
                    p.statut AS paiement_statut, p.montant AS paiement_montant, p.devise AS paiement_devise,
                    p.provider AS paiement_canal, p.transaction_ref AS paiement_reference
             FROM inscriptions i
             INNER JOIN classes c ON c.id = i.classe_id
             INNER JOIN annees_scolaires a ON a.id = i.annee_scolaire_id
             LEFT JOIN LATERAL (
                 SELECT statut, montant, devise, provider, transaction_ref
                 FROM paiements WHERE inscription_id = i.id
                 ORDER BY created_at DESC, id DESC LIMIT 1
             ) p ON TRUE
             WHERE i.eleve_id = :eleve_id
             ORDER BY a.date_debut DESC"
        );
        $statement->execute(['eleve_id' => $profile['id']]);
        $inscriptions = $statement->fetchAll();

           $statement = $connection->prepare(
              "SELECT c.id, c.libelle AS classe, c.annee_scolaire_id, a.libelle AS annee_scolaire
               FROM classes c
               INNER JOIN annees_scolaires a ON a.id = c.annee_scolaire_id
               WHERE a.statut IN ('A_VENIR', 'EN_COURS')
                AND NOT EXISTS (
                    SELECT 1 FROM inscriptions i
                    WHERE i.eleve_id = :eleve_id AND i.annee_scolaire_id = c.annee_scolaire_id
                )
               ORDER BY a.date_debut DESC, c.libelle ASC"
           );
           $statement->execute(['eleve_id' => $profile['id']]);
           $availableClasses = $statement->fetchAll();

        $statement = $connection->prepare(
            'SELECT n.valeur, n.commentaire, ev.libelle AS evaluation, ev.date_evaluation,
                    m.libelle AS matiere, c.libelle AS classe, a.libelle AS annee_scolaire
             FROM notes n
             INNER JOIN evaluations ev ON ev.id = n.evaluation_id
             INNER JOIN affectations_enseignants af ON af.id = ev.affectation_id
             INNER JOIN matieres m ON m.id = af.matiere_id
             INNER JOIN classes c ON c.id = n.classe_id
             INNER JOIN annees_scolaires a ON a.id = n.annee_scolaire_id
             WHERE n.eleve_id = :eleve_id
             ORDER BY ev.date_evaluation DESC, m.libelle ASC'
        );
        $statement->execute(['eleve_id' => $profile['id']]);

        return [
            'profile' => $profile,
            'inscriptions' => $inscriptions,
            'availableClasses' => $availableClasses,
            'notes' => $statement->fetchAll(),
        ];
    }
}
