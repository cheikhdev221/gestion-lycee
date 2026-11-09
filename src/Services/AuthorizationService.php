<?php

declare(strict_types=1);

namespace App\Services;

final class AuthorizationService
{
    public function canAccess(string $roleCode, string $method, string $path): bool
    {
        $roleCode = strtoupper($roleCode);
        $method = strtoupper($method);

        // Toute session authentifiée doit pouvoir se déconnecter, quel que soit son rôle.
        if ($method === 'POST' && $path === '/logout') {
            return true;
        }

        if ($roleCode === 'ADMIN') {
            return !($method === 'POST' && in_array($path, ['/notes/store', '/notes/update'], true))
                && !($method === 'GET' && $path === '/notes/edit');
        }

        if ($roleCode === 'DIRECTEUR') {
            return !in_array($path, ['/users', '/users/create', '/users/store', '/users/edit', '/users/update', '/users/active'], true)
                && !($method === 'POST' && in_array($path, ['/notes/store', '/notes/update', '/payments/simulate'], true))
                && !($method === 'GET' && $path === '/notes/edit');
        }

        if ($roleCode === 'ENSEIGNANT') {
            return in_array($method . ' ' . $path, [
                'GET /classes',
                'GET /eleves',
                'GET /evaluations',
                'GET /evaluations/create',
                'POST /evaluations/store',
                'GET /evaluations/edit',
                'POST /evaluations/update',
                'GET /notes',
                'GET /notes/create',
                'POST /notes/store',
                'GET /notes/edit',
                'POST /notes/update',
            ], true);
        }

        if ($roleCode === 'ELEVE') {
            return in_array($method . ' ' . $path, [
                'GET /mon-espace',
                'GET /mon-espace/photo',
                'GET /mon-espace/inscription',
                'POST /mon-espace/inscription',
            ], true);
        }

        return false;
    }
}
