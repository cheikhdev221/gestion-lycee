
-- ==========================================
-- Migration 010 : Creation de la table notes
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE TABLE notes (

    id INTEGER GENERATED ALWAYS AS IDENTITY,

    evaluation_id INTEGER NOT NULL,
    eleve_id INTEGER NOT NULL,
    classe_id INTEGER NOT NULL,
    annee_scolaire_id SMALLINT NOT NULL,

    valeur NUMERIC(5,2) NOT NULL,

    commentaire TEXT,

    saisi_par INTEGER NOT NULL,

    date_saisie TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_notes
        PRIMARY KEY (id),

    CONSTRAINT uq_notes_evaluation_eleve
        UNIQUE (evaluation_id, eleve_id),

    CONSTRAINT fk_notes_evaluation_classe_annee
        FOREIGN KEY (evaluation_id, classe_id, annee_scolaire_id)
        REFERENCES evaluations(id, classe_id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_notes_eleve_classe_annee
        FOREIGN KEY (eleve_id, classe_id, annee_scolaire_id)
        REFERENCES inscriptions(eleve_id, classe_id, annee_scolaire_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_notes_saisi_par
        FOREIGN KEY (saisi_par)
        REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_notes_valeur
        CHECK (valeur >= 0 AND valeur <= 20)
);

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_notes_eleve_classe_annee
    ON notes (eleve_id, classe_id, annee_scolaire_id);

CREATE INDEX idx_notes_evaluation_classe_annee
    ON notes (evaluation_id, classe_id, annee_scolaire_id);

CREATE INDEX idx_notes_saisi_par
    ON notes (saisi_par);

COMMIT;

-- ==========================================
-- FIN MIGRATION 010
-- ==========================================
