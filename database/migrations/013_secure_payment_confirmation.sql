BEGIN;

DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM pg_roles WHERE rolname = 'gestion_lycee_paiement_executor'
    ) THEN
        CREATE ROLE gestion_lycee_paiement_executor NOLOGIN;
    END IF;
END;
$$;

GRANT USAGE ON SCHEMA public TO gestion_lycee_paiement_executor;
GRANT SELECT ON public.paiements, public.inscriptions TO gestion_lycee_paiement_executor;
GRANT UPDATE (statut, date_paiement, updated_at) ON public.paiements TO gestion_lycee_paiement_executor;
GRANT UPDATE (statut, updated_at) ON public.inscriptions TO gestion_lycee_paiement_executor;

ALTER FUNCTION public.confirmer_paiement(INTEGER)
    OWNER TO gestion_lycee_paiement_executor;

REVOKE ALL ON FUNCTION public.confirmer_paiement(INTEGER) FROM PUBLIC;

DO $$
BEGIN
    EXECUTE format(
        'GRANT EXECUTE ON FUNCTION public.confirmer_paiement(INTEGER) TO %I',
        current_user
    );
END;
$$;

COMMIT;