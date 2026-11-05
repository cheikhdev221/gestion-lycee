
-- ==========================================
-- Migration 006 : Creation de la table inscriptions
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE inscriptions (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    eleve_id INTEGER NOT NULL,
    classe_id INTEGER NOT NULL,
    annee_scolaire_id SMALLINT NOT NULL,

    statut VARCHAR(20) NOT NULL DEFAULT 'PENDING',

    date_inscription DATE NOT NULL DEFAULT CURRENT_DATE,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- --------------------------------------
    -- Cles primaires et unicite
    -- --------------------------------------

    CONSTRAINT pk_inscriptions
        PRIMARY KEY (id),

    CONSTRAINT uq_inscriptions_eleve_annee
        UNIQUE (eleve_id, annee_scolaire_id),

    CONSTRAINT uq_inscriptions_eleve_classe_annee
        UNIQUE (eleve_id, classe_id, annee_scolaire_id),

    -- --------------------------------------
    -- Cles etrangeres
    -- --------------------------------------

    CONSTRAINT fk_inscriptions_eleve
        FOREIGN KEY (eleve_id)
        REFERENCES eleves(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_inscriptions_classe_annee
        FOREIGN KEY (classe_id, annee_scolaire_id)
        REFERENCES classes(id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    -- --------------------------------------
    -- Contraintes de validation
    -- --------------------------------------

    CONSTRAINT chk_inscriptions_statut
        CHECK (statut IN (
            'PENDING',
            'PAYE',
            'ANNULEE'
        ))
);

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_inscriptions_classe_annee
    ON inscriptions (classe_id, annee_scolaire_id);

CREATE INDEX idx_inscriptions_annee_classe
    ON inscriptions (annee_scolaire_id, classe_id);

COMMIT;

