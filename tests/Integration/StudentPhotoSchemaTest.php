<?php

declare(strict_types=1);

require __DIR__ . '/../../src/Core/Database.php';

define('BASE_PATH', dirname(__DIR__, 2));
$connection = \App\Core\Database::getConnection();
$statement = $connection->prepare(
    "SELECT 1 FROM information_schema.columns
     WHERE table_schema = 'public' AND table_name = 'eleves' AND column_name = 'photo_path'"
);
$statement->execute();
if ($statement->fetchColumn() === false) {
    fwrite(STDERR, "Student photo migration is missing\n");
    exit(1);
}

$statement = $connection->prepare(
    "SELECT 1 FROM pg_constraint WHERE conname = 'chk_eleves_photo_path'"
);
$statement->execute();
if ($statement->fetchColumn() === false) {
    fwrite(STDERR, "Student photo path constraint is missing\n");
    exit(1);
}

echo "Student photo schema integration test passed\n";
