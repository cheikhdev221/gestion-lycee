
-- ==========================================
-- Migration 009 : Creation des evaluations
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE evaluations (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    affectation_id INTEGER NOT NULL,
    semestre_id SMALLINT NOT NULL,
    classe_id INTEGER NOT NULL,
    annee_scolaire_id SMALLINT NOT NULL,

    libelle VARCHAR(150) NOT NULL,

    type VARCHAR(20) NOT NULL,

    coefficient NUMERIC(5,2) NOT NULL DEFAULT 1.00,

    date_evaluation DATE NOT NULL,

    description TEXT,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_evaluations
        PRIMARY KEY (id),

    CONSTRAINT fk_evaluations_affectation_classe_annee
        FOREIGN KEY (affectation_id, classe_id, annee_scolaire_id)
        REFERENCES affectations_enseignants(id, classe_id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_evaluations_semestre_annee
        FOREIGN KEY (semestre_id, annee_scolaire_id)
        REFERENCES semestres(id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_evaluations_type
        CHECK (type IN ('DEVOIR', 'COMPOSITION')),

    CONSTRAINT chk_evaluations_coefficient
        CHECK (coefficient > 0),

    CONSTRAINT uq_evaluations_id_classe_annee
        UNIQUE (id, classe_id, annee_scolaire_id)
);

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_evaluations_affectation_annee
    ON evaluations (affectation_id, classe_id, annee_scolaire_id);

CREATE INDEX idx_evaluations_semestre_annee
    ON evaluations (semestre_id, annee_scolaire_id);

CREATE INDEX idx_evaluations_date
    ON evaluations (date_evaluation);

COMMIT;

-- ==========================================
-- FIN MIGRATION 009
-- ==========================================
