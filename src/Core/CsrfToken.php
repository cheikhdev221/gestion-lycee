<?php

declare(strict_types=1);

namespace App\Core;

final class CsrfToken
{
    public static function get(): string
    {
        $token = Session::get('_csrf_token');

        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            Session::set('_csrf_token', $token);
        }

        return $token;
    }

    public static function verify(mixed $submittedToken): bool
    {
        $expectedToken = Session::get('_csrf_token');

        return is_string($submittedToken)
            && is_string($expectedToken)
            && hash_equals($expectedToken, $submittedToken);
    }

    public static function rotate(): void
    {
        Session::set('_csrf_token', bin2hex(random_bytes(32)));
    }
}