<?php

declare(strict_types=1);

use App\Core\Database;
use App\Models\User;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!function_exists('stream_isatty') || !stream_isatty(STDIN)) {
    fwrite(STDERR, "Exécute ce script dans un terminal interactif.\n");
    exit(1);
}

define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/src/Core/Database.php';
require BASE_PATH . '/src/Models/Role.php';
require BASE_PATH . '/src/Models/User.php';
require BASE_PATH . '/src/Repositories/RoleRepository.php';
require BASE_PATH . '/src/Repositories/UserRepository.php';

$connection = Database::getConnection();
$roleRepository = new RoleRepository();
$roleRepository->ensureDefaultRoles();

$statement = $connection->query(
    "SELECT COUNT(*) FROM utilisateurs u
     INNER JOIN roles r ON r.id = u.role_id
     WHERE r.code = 'ADMIN' AND u.actif = TRUE"
);
if ((int) $statement->fetchColumn() > 0) {
    fwrite(STDERR, "Un administrateur actif existe déjà. Aucun compte n’a été créé.\n");
    exit(1);
}

$readLine = static function (string $prompt): string {
    fwrite(STDOUT, $prompt);
    return trim((string) fgets(STDIN));
};

$nom = $readLine('Nom : ');
$prenom = $readLine('Prénom : ');
$email = strtolower($readLine('Email : '));
if ($nom === '' || $prenom === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Nom, prénom ou adresse email invalide.\n");
    exit(1);
}

fwrite(STDOUT, 'Mot de passe (12 caractères minimum) : ');
exec('stty -echo');
try {
    $password = trim((string) fgets(STDIN));
    fwrite(STDOUT, PHP_EOL . 'Confirmer le mot de passe : ');
    $confirmation = trim((string) fgets(STDIN));
} finally {
    exec('stty echo');
    fwrite(STDOUT, PHP_EOL);
}

if (strlen($password) < 12 || !hash_equals($password, $confirmation)) {
    fwrite(STDERR, "Mot de passe trop court ou confirmation incorrecte.\n");
    exit(1);
}

$userRepository = new UserRepository();
if ($userRepository->findByEmail($email) !== null) {
    fwrite(STDERR, "Cette adresse email est déjà utilisée.\n");
    exit(1);
}

$adminRole = $roleRepository->findByCode('ADMIN');
if ($adminRole === null) {
    fwrite(STDERR, "Le rôle ADMIN est introuvable.\n");
    exit(1);
}

$userRepository->create(new User(
    roleId: $adminRole->id,
    nom: $nom,
    prenom: $prenom,
    email: $email,
    passwordHash: password_hash($password, PASSWORD_ARGON2ID),
    actif: true,
));

fwrite(STDOUT, "Compte administrateur créé. Connecte-toi sur http://localhost:8080/login avec cet email et le mot de passe choisi.\n");
