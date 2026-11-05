
-- ==========================================
-- Migration 003 : Creation de la table annees_scolaires
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

CREATE EXTENSION IF NOT EXISTS btree_gist;

CREATE TABLE annees_scolaires (

    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    libelle VARCHAR(20) NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut VARCHAR(15) NOT NULL DEFAULT 'A_VENIR',
    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_annees_scolaires
        PRIMARY KEY (id),

    CONSTRAINT uq_annees_scolaires_libelle
        UNIQUE (libelle),

    CONSTRAINT chk_annees_scolaires_dates
        CHECK (date_fin > date_debut),

    CONSTRAINT chk_annees_scolaires_libelle
        CHECK (libelle ~ '^[0-9]{4}-[0-9]{4}$'),

    CONSTRAINT chk_annees_scolaires_statut
        CHECK (statut IN ('A_VENIR', 'EN_COURS', 'TERMINEE')),

    CONSTRAINT chk_annees_scolaires_coherence
        CHECK (
            CAST(SUBSTRING(libelle FROM 1 FOR 4) AS INTEGER) + 1
            = CAST(SUBSTRING(libelle FROM 6 FOR 4) AS INTEGER)
        ),

    CONSTRAINT ex_annees_scolaires_periode
        EXCLUDE USING GIST (
            daterange(date_debut, date_fin, '[]') WITH &&
        )
);

CREATE UNIQUE INDEX uq_annees_scolaires_active
    ON annees_scolaires (statut)
    WHERE statut = 'EN_COURS';

COMMIT;
