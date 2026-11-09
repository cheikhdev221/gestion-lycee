<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class PromotionRepository
{
    public function findCandidates(): array
    {
        $statement = Database::getConnection()->prepare(
            "SELECT i.id AS ancienne_inscription_id, e.id AS eleve_id, u.nom, u.prenom, e.matricule,
                    c.libelle AS ancienne_classe, an.libelle AS ancienne_annee,
                    cible.id AS nouvelle_classe_id, cible.libelle AS nouvelle_classe,
                    an2.libelle AS nouvelle_annee
             FROM inscriptions i
             INNER JOIN eleves e ON e.id = i.eleve_id
             INNER JOIN utilisateurs u ON u.id = e.utilisateur_id
             INNER JOIN classes c ON c.id = i.classe_id
             INNER JOIN niveaux n ON n.id = c.niveau_id
             INNER JOIN annees_scolaires an ON an.id = i.annee_scolaire_id
             INNER JOIN annees_scolaires an2
                ON substring(an2.libelle FROM 1 FOR 4)::INTEGER = substring(an.libelle FROM 6 FOR 4)::INTEGER
                AND an2.statut IN ('A_VENIR', 'EN_COURS')
             INNER JOIN classes cible ON cible.annee_scolaire_id = an2.id
             INNER JOIN niveaux n2 ON n2.id = cible.niveau_id AND n2.ordre = n.ordre + 1
             WHERE i.statut = 'PAYE' AND an.statut = 'TERMINEE'
               AND NOT EXISTS (
                    SELECT 1 FROM inscriptions suivante
                    WHERE suivante.eleve_id = i.eleve_id
                      AND suivante.annee_scolaire_id = an2.id
               )
             ORDER BY u.nom ASC, u.prenom ASC, cible.libelle ASC"
        );
        $statement->execute();

        return $statement->fetchAll();
    }

    public function approve(int $eleveId, int $classeId, int $directeurId): int
    {
        $connection = Database::getConnection();
        $connection->beginTransaction();

        try {
            $statement = $connection->prepare(
                "SELECT i.id AS inscription_id, i.classe_id, i.annee_scolaire_id, i.statut,
                        an.libelle AS annee, an.statut AS annee_statut, n.ordre AS niveau_ordre
                 FROM inscriptions i
                 INNER JOIN classes c ON c.id = i.classe_id
                 INNER JOIN niveaux n ON n.id = c.niveau_id
                 INNER JOIN annees_scolaires an ON an.id = i.annee_scolaire_id
                 WHERE i.eleve_id = :eleve_id
                 ORDER BY an.date_debut DESC
                 LIMIT 1
                 FOR UPDATE OF i"
            );
            $statement->execute(['eleve_id' => $eleveId]);
            $source = $statement->fetch();

            if ($source === false || $source['statut'] !== 'PAYE' || $source['annee_statut'] !== 'TERMINEE') {
                throw new \RuntimeException('L’élève doit avoir une inscription payée dans une année terminée.');
            }

            $statement = $connection->prepare(
                'SELECT c.id, c.annee_scolaire_id, an.libelle AS annee, an.statut AS annee_statut,
                        n.ordre AS niveau_ordre
                 FROM classes c
                 INNER JOIN niveaux n ON n.id = c.niveau_id
                 INNER JOIN annees_scolaires an ON an.id = c.annee_scolaire_id
                 WHERE c.id = :classe_id
                 FOR UPDATE OF c'
            );
            $statement->execute(['classe_id' => $classeId]);
            $target = $statement->fetch();

            $expectedYear = substr($source['annee'], 5, 4) . '-' . ((int) substr($source['annee'], 5, 4) + 1);
            if ($target === false
                || $target['annee'] !== $expectedYear
                || !in_array($target['annee_statut'], ['A_VENIR', 'EN_COURS'], true)
                || (int) $target['niveau_ordre'] !== (int) $source['niveau_ordre'] + 1) {
                throw new \RuntimeException('La classe choisie ne correspond pas au niveau supérieur de l’année suivante.');
            }

            $statement = $connection->prepare(
                'SELECT 1 FROM inscriptions WHERE eleve_id = :eleve_id AND annee_scolaire_id = :annee_id'
            );
            $statement->execute(['eleve_id' => $eleveId, 'annee_id' => $target['annee_scolaire_id']]);
            if ($statement->fetchColumn() !== false) {
                throw new \RuntimeException('Une inscription existe déjà pour cet élève dans cette année scolaire.');
            }

            $statement = $connection->prepare(
                "INSERT INTO inscriptions (eleve_id, classe_id, annee_scolaire_id, statut)
                 VALUES (:eleve_id, :classe_id, :annee_id, 'PENDING')
                 RETURNING id"
            );
            $statement->execute([
                'eleve_id' => $eleveId,
                'classe_id' => $classeId,
                'annee_id' => $target['annee_scolaire_id'],
            ]);
            $nouvelleInscriptionId = (int) $statement->fetchColumn();

            $statement = $connection->prepare(
                "INSERT INTO audit_logs (utilisateur_id, action, entite, entite_id, anciennes_donnees, nouvelles_donnees)
                 VALUES (:utilisateur_id, 'PROMOTION_VALIDEE', 'inscriptions', :entite_id, CAST(:anciennes AS JSONB), CAST(:nouvelles AS JSONB))"
            );
            $statement->execute([
                'utilisateur_id' => $directeurId,
                'entite_id' => $nouvelleInscriptionId,
                'anciennes' => json_encode([
                    'inscription_id' => (int) $source['inscription_id'],
                    'classe_id' => (int) $source['classe_id'],
                    'annee_scolaire_id' => (int) $source['annee_scolaire_id'],
                ], JSON_THROW_ON_ERROR),
                'nouvelles' => json_encode([
                    'eleve_id' => $eleveId,
                    'classe_id' => $classeId,
                    'annee_scolaire_id' => (int) $target['annee_scolaire_id'],
                    'statut' => 'PENDING',
                ], JSON_THROW_ON_ERROR),
            ]);

            $connection->commit();

            return $nouvelleInscriptionId;
        } catch (\Throwable $exception) {
            if ($connection->inTransaction()) {
                $connection->rollBack();
            }

            throw $exception;
        }
    }
}