
-- ==========================================
-- Migration 005 : Matieres, eleves et enseignants
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

-- ------------------------------------------
-- Table : matieres
-- ------------------------------------------

CREATE TABLE matieres (
    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    code VARCHAR(20) NOT NULL,
    libelle VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_matieres
        PRIMARY KEY (id),

    CONSTRAINT uq_matieres_code
        UNIQUE (code),

    CONSTRAINT uq_matieres_libelle
        UNIQUE (libelle),

    CONSTRAINT chk_matieres_code
        CHECK (code ~ '^[A-Z0-9_]+$')
);

-- ------------------------------------------
-- Table : eleves
-- ------------------------------------------

CREATE TABLE eleves (
    id INTEGER GENERATED ALWAYS AS IDENTITY,
    utilisateur_id INTEGER NOT NULL,
    matricule VARCHAR(20) NOT NULL,
    date_naissance DATE NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_eleves
        PRIMARY KEY (id),

    CONSTRAINT uq_eleves_utilisateur
        UNIQUE (utilisateur_id),

    CONSTRAINT uq_eleves_matricule
        UNIQUE (matricule),

    CONSTRAINT fk_eleves_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_eleves_matricule
        CHECK (matricule ~ '^LYC-[0-9]{4}-[0-9]{5}$')
);

-- ------------------------------------------
-- Table : enseignants
-- ------------------------------------------

CREATE TABLE enseignants (
    id INTEGER GENERATED ALWAYS AS IDENTITY,
    utilisateur_id INTEGER NOT NULL,
    matiere_id SMALLINT NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_enseignants
        PRIMARY KEY (id),

    CONSTRAINT uq_enseignants_id_matiere
        UNIQUE (id, matiere_id),

    CONSTRAINT uq_enseignants_utilisateur
        UNIQUE (utilisateur_id),

    CONSTRAINT fk_enseignants_utilisateur
        FOREIGN KEY (utilisateur_id)
        REFERENCES utilisateurs(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_enseignants_matiere
        FOREIGN KEY (matiere_id)
        REFERENCES matieres(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_enseignants_matiere
    ON enseignants (matiere_id);

COMMIT;
