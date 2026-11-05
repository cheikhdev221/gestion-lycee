
-- ==========================================
-- Migration 004 : Structure scolaire
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

-- ------------------------------------------
-- Table : niveaux
-- ------------------------------------------

CREATE TABLE niveaux (
    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    code VARCHAR(20) NOT NULL,
    libelle VARCHAR(100) NOT NULL,
    ordre SMALLINT NOT NULL,

    CONSTRAINT pk_niveaux PRIMARY KEY (id),
    CONSTRAINT uq_niveaux_code UNIQUE (code),
    CONSTRAINT uq_niveaux_libelle UNIQUE (libelle),
    CONSTRAINT chk_niveaux_ordre CHECK (ordre > 0)
);

-- ------------------------------------------
-- Table : series
-- ------------------------------------------

CREATE TABLE series (
    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    code VARCHAR(20) NOT NULL,
    libelle VARCHAR(100) NOT NULL,

    CONSTRAINT pk_series PRIMARY KEY (id),
    CONSTRAINT uq_series_code UNIQUE (code),
    CONSTRAINT uq_series_libelle UNIQUE (libelle)
);

-- ------------------------------------------
-- Table : series_niveaux
-- ------------------------------------------

CREATE TABLE series_niveaux (
    id INTEGER GENERATED ALWAYS AS IDENTITY,
    serie_id SMALLINT NOT NULL,
    niveau_id SMALLINT NOT NULL,

    CONSTRAINT pk_series_niveaux PRIMARY KEY (id),

    CONSTRAINT uq_series_niveaux
        UNIQUE (serie_id, niveau_id),

    CONSTRAINT fk_series_niveaux_serie
        FOREIGN KEY (serie_id)
        REFERENCES series(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_series_niveaux_niveau
        FOREIGN KEY (niveau_id)
        REFERENCES niveaux(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE INDEX idx_series_niveaux_niveau
    ON series_niveaux (niveau_id);

-- ------------------------------------------
-- Table : classes
-- ------------------------------------------

CREATE TABLE classes (
    id INTEGER GENERATED ALWAYS AS IDENTITY,
    libelle VARCHAR(100) NOT NULL,
    niveau_id SMALLINT NOT NULL,
    serie_id SMALLINT NOT NULL,
    annee_scolaire_id SMALLINT NOT NULL,

    CONSTRAINT pk_classes PRIMARY KEY (id),

    CONSTRAINT fk_classes_niveau
        FOREIGN KEY (niveau_id)
        REFERENCES niveaux(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_classes_serie
        FOREIGN KEY (serie_id)
        REFERENCES series(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_classes_serie_niveau
        FOREIGN KEY (serie_id, niveau_id)
        REFERENCES series_niveaux(serie_id, niveau_id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_classes_annee_scolaire
        FOREIGN KEY (annee_scolaire_id)
        REFERENCES annees_scolaires(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT uq_classes_id_annee
        UNIQUE (id, annee_scolaire_id),

    CONSTRAINT uq_classes_libelle_annee
        UNIQUE (libelle, annee_scolaire_id)
);

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_classes_niveau
    ON classes (niveau_id);

CREATE INDEX idx_classes_serie_niveau
    ON classes (serie_id, niveau_id);

CREATE INDEX idx_classes_annee
    ON classes (annee_scolaire_id);

COMMIT;
