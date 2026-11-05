
-- ==========================================
-- Migration 008 : Creation des semestres
BEGIN;

-- Projet : Gestion-Lycee
-- SGBD : PostgreSQL 16
-- ==========================================

-- ------------------------------------------
-- Table : semestres
-- ------------------------------------------

CREATE TABLE semestres (

    id SMALLINT GENERATED ALWAYS AS IDENTITY,
    annee_scolaire_id SMALLINT NOT NULL,

    code VARCHAR(10) NOT NULL,
    libelle VARCHAR(100) NOT NULL,

    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,

    created_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT pk_semestres
        PRIMARY KEY (id),

    CONSTRAINT uq_semestres_code_annee
        UNIQUE (code, annee_scolaire_id),

    CONSTRAINT uq_semestres_libelle_annee
        UNIQUE (libelle, annee_scolaire_id),

    CONSTRAINT uq_semestres_id_annee
        UNIQUE (id, annee_scolaire_id),

    CONSTRAINT fk_semestres_annee_scolaire
        FOREIGN KEY (annee_scolaire_id)
        REFERENCES annees_scolaires(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT chk_semestres_code
        CHECK (code IN ('S1', 'S2')),

    CONSTRAINT chk_semestres_dates
        CHECK (date_fin > date_debut),

    CONSTRAINT ex_semestres_periode
        EXCLUDE USING GIST (
            annee_scolaire_id WITH =,
            daterange(date_debut, date_fin, '[]') WITH &&
        )
);

-- ------------------------------------------
-- Fonction de verification des dates
-- ------------------------------------------

CREATE FUNCTION verifier_periode_semestre()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog, public
AS $$
DECLARE
    debut_annee DATE;
    fin_annee DATE;
BEGIN
    SELECT date_debut, date_fin
    INTO debut_annee, fin_annee
    FROM annees_scolaires
    WHERE id = NEW.annee_scolaire_id
    FOR SHARE;

    IF NOT FOUND THEN
        RAISE EXCEPTION 'Annee scolaire inexistante';
    END IF;

    IF NEW.date_debut < debut_annee
       OR NEW.date_fin > fin_annee THEN
        RAISE EXCEPTION
            'Les dates du semestre doivent respecter les limites de l''annee scolaire';
    END IF;

    RETURN NEW;
END;
$$;

-- ------------------------------------------
-- Trigger de validation
-- ------------------------------------------

CREATE TRIGGER trg_verifier_periode_semestre
BEFORE INSERT OR UPDATE OF date_debut, date_fin, annee_scolaire_id
ON semestres
FOR EACH ROW
EXECUTE FUNCTION verifier_periode_semestre();

-- ------------------------------------------
-- Coherence lors de la modification de l'annee
-- ------------------------------------------

CREATE FUNCTION verifier_nouvelles_limites_annee_scolaire()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog, public
AS $$
BEGIN
    IF EXISTS (
        SELECT 1
        FROM semestres
        WHERE annee_scolaire_id = OLD.id
          AND (
              date_debut < NEW.date_debut
              OR date_fin > NEW.date_fin
          )
    ) THEN
        RAISE EXCEPTION
            'Les nouvelles limites de l''annee scolaire excluent un semestre existant';
    END IF;

    RETURN NEW;
END;
$$;

CREATE TRIGGER trg_verifier_limites_annee_scolaire
BEFORE UPDATE OF date_debut, date_fin
ON annees_scolaires
FOR EACH ROW
EXECUTE FUNCTION verifier_nouvelles_limites_annee_scolaire();

-- Les contraintes differees permettent de creer l'annee et ses deux semestres
-- dans une seule transaction tout en rejetant un etat final incomplet.
CREATE FUNCTION verifier_deux_semestres_par_annee()
RETURNS TRIGGER
LANGUAGE plpgsql
SET search_path = pg_catalog, public
AS $$
DECLARE
    v_annee_ids SMALLINT[];
    v_annee_id SMALLINT;
    v_nombre_semestres BIGINT;
BEGIN
    IF TG_TABLE_NAME = 'annees_scolaires' THEN
        v_annee_ids := ARRAY[NEW.id];
    ELSIF TG_OP = 'INSERT' THEN
        v_annee_ids := ARRAY[NEW.annee_scolaire_id];
    ELSIF TG_OP = 'DELETE' THEN
        v_annee_ids := ARRAY[OLD.annee_scolaire_id];
    ELSE
        v_annee_ids := ARRAY[
            OLD.annee_scolaire_id,
            NEW.annee_scolaire_id
        ];
    END IF;

    FOREACH v_annee_id IN ARRAY v_annee_ids LOOP
        IF EXISTS (
            SELECT 1
            FROM annees_scolaires
            WHERE id = v_annee_id
        ) THEN
            PERFORM 1
            FROM annees_scolaires
            WHERE id = v_annee_id
            FOR NO KEY UPDATE;

            SELECT COUNT(*)
            INTO v_nombre_semestres
            FROM semestres
            WHERE annee_scolaire_id = v_annee_id;

            IF v_nombre_semestres <> 2 THEN
                RAISE EXCEPTION
                    'Une annee scolaire doit avoir exactement deux semestres';
            END IF;
        END IF;
    END LOOP;

    RETURN NULL;
END;
$$;

CREATE CONSTRAINT TRIGGER trg_annees_scolaires_deux_semestres
AFTER INSERT OR UPDATE
ON annees_scolaires
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION verifier_deux_semestres_par_annee();

CREATE CONSTRAINT TRIGGER trg_semestres_deux_par_annee
AFTER INSERT OR UPDATE OR DELETE
ON semestres
DEFERRABLE INITIALLY DEFERRED
FOR EACH ROW
EXECUTE FUNCTION verifier_deux_semestres_par_annee();

-- ------------------------------------------
-- Indexation
-- ------------------------------------------

CREATE INDEX idx_semestres_annee
    ON semestres (annee_scolaire_id);

COMMIT;

-- ==========================================
-- FIN MIGRATION 008
-- ==========================================
