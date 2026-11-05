
-- ==========================================
-- Migration 007 : Affectations des enseignants
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE affectations_enseignants (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    enseignant_id INTEGER NOT NULL,
    classe_id INTEGER NOT NULL,
    matiere_id SMALLINT NOT NULL,
    annee_scolaire_id SMALLINT NOT NULL,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_affectations_enseignants
        PRIMARY KEY (id),

    CONSTRAINT uq_affectations_id_classe_annee
        UNIQUE (id, classe_id, annee_scolaire_id),

    CONSTRAINT uq_affectations_enseignants
        UNIQUE (
            enseignant_id,
            matiere_id,
            classe_id,
            annee_scolaire_id
        ),

    CONSTRAINT fk_affectations_enseignants_enseignant_matiere
        FOREIGN KEY (enseignant_id, matiere_id)
        REFERENCES enseignants(id, matiere_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_affectations_enseignants_classe_annee
        FOREIGN KEY (classe_id, annee_scolaire_id)
        REFERENCES classes(id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE INDEX idx_affectations_classe_matiere
    ON affectations_enseignants (classe_id, matiere_id);

CREATE INDEX idx_affectations_classe_annee
    ON affectations_enseignants (classe_id, annee_scolaire_id);

CREATE INDEX idx_affectations_annee
    ON affectations_enseignants (annee_scolaire_id);

COMMIT;
