<?php
declare(strict_types=1);

namespace App\Core;

use App\Services\AuthService;
use App\Services\AuthorizationService;
use App\Repositories\RoleRepository;
use App\Core\CsrfToken;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][$this->normalizePath($path)] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][$this->normalizePath($path)] = $handler;
    }

    public function dispatch(string $method, string $path): void
    {
        $method = strtoupper($method);
        $normalizedPath = $this->normalizePath($path);
        $handler = $this->routes[$method][$normalizedPath] ?? null;

        if ($handler === null) {
            http_response_code(404);
            echo 'Page introuvable';
            return;
        }

        if ($method === 'POST' && !CsrfToken::verify($_POST['_csrf'] ?? null)) {
            http_response_code(419);
            echo 'Jeton de sécurité invalide ou expiré.';
            return;
        }

        if (!in_array($normalizedPath, ['/', '/login', '/inscription-eleve'], true)) {
            $authService = new AuthService();
            $user = $authService->currentUser();
            if ($user === null) {
                header('Location: /login');
                http_response_code(302);
                return;
            }

            $role = (new RoleRepository())->findById($user->roleId);
            if ($role === null || !(new AuthorizationService())->canAccess($role->code, $method, $normalizedPath)) {
                http_response_code(403);
                echo 'Accès interdit';
                return;
            }
        }

        [$class, $action] = $handler;
        (new $class())->$action();
    }

    private function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        return $normalized === '' ? '/' : $normalized;
    }
}
