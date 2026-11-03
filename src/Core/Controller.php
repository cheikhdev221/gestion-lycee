<?php
declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected function render(string $template, array $data = []): void
    {
        extract($data, EXTR_SKIP);
        require BASE_PATH . '/templates/' . $template . '.php';
    }
}
