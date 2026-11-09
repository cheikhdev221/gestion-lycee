<?php
declare(strict_types=1);

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\ClassController;
use App\Controllers\EnrollmentController;
use App\Controllers\EvaluationController;
use App\Controllers\HomeController;
use App\Controllers\NoteController;
use App\Controllers\PaymentController;
use App\Controllers\PromotionController;
use App\Controllers\SchoolYearController;
use App\Controllers\StudentController;
use App\Controllers\StudentPortalController;
use App\Controllers\StudentPhotoController;
use App\Controllers\StudentRegistrationController;
use App\Controllers\StudentEnrollmentController;
use App\Controllers\TeacherController;
use App\Controllers\UserController;
use App\Core\Router;
use App\Repositories\RoleRepository;

define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $file = BASE_PATH . '/src/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($file)) {
        require $file;
    }
});

$router = new Router();
$router->get('/', [HomeController::class, 'index']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->get('/inscription-eleve', [StudentRegistrationController::class, 'create']);
$router->post('/inscription-eleve', [StudentRegistrationController::class, 'store']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/admin', [AdminController::class, 'dashboard']);
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/create', [UserController::class, 'create']);
$router->post('/users/store', [UserController::class, 'store']);
$router->get('/users/edit', [UserController::class, 'edit']);
$router->post('/users/update', [UserController::class, 'update']);
$router->post('/users/active', [UserController::class, 'toggleActive']);
$router->get('/annees', [SchoolYearController::class, 'index']);
$router->get('/annees/create', [SchoolYearController::class, 'create']);
$router->post('/annees/store', [SchoolYearController::class, 'store']);
$router->get('/annees/edit', [SchoolYearController::class, 'edit']);
$router->post('/annees/update', [SchoolYearController::class, 'update']);
$router->get('/classes', [ClassController::class, 'index']);
$router->get('/classes/create', [ClassController::class, 'create']);
$router->post('/classes/store', [ClassController::class, 'store']);
$router->get('/classes/edit', [ClassController::class, 'edit']);
$router->post('/classes/update', [ClassController::class, 'update']);
$router->get('/eleves', [StudentController::class, 'index']);
$router->get('/eleves/create', [StudentController::class, 'create']);
$router->post('/eleves/store', [StudentController::class, 'store']);
$router->get('/eleves/edit', [StudentController::class, 'edit']);
$router->post('/eleves/update', [StudentController::class, 'update']);
$router->get('/enseignants', [TeacherController::class, 'index']);
$router->get('/enseignants/create', [TeacherController::class, 'create']);
$router->post('/enseignants/store', [TeacherController::class, 'store']);
$router->get('/enseignants/edit', [TeacherController::class, 'edit']);
$router->post('/enseignants/update', [TeacherController::class, 'update']);
$router->get('/inscriptions', [EnrollmentController::class, 'index']);
$router->get('/inscriptions/create', [EnrollmentController::class, 'create']);
$router->post('/inscriptions/store', [EnrollmentController::class, 'store']);
$router->get('/inscriptions/edit', [EnrollmentController::class, 'edit']);
$router->post('/inscriptions/update', [EnrollmentController::class, 'update']);
$router->post('/inscriptions/cancel', [EnrollmentController::class, 'cancel']);
$router->get('/evaluations', [EvaluationController::class, 'index']);
$router->get('/evaluations/create', [EvaluationController::class, 'create']);
$router->post('/evaluations/store', [EvaluationController::class, 'store']);
$router->get('/evaluations/edit', [EvaluationController::class, 'edit']);
$router->post('/evaluations/update', [EvaluationController::class, 'update']);
$router->get('/notes', [NoteController::class, 'index']);
$router->get('/notes/create', [NoteController::class, 'create']);
$router->post('/notes/store', [NoteController::class, 'store']);
$router->get('/notes/edit', [NoteController::class, 'edit']);
$router->post('/notes/update', [NoteController::class, 'update']);
$router->get('/payments', [PaymentController::class, 'index']);
$router->get('/payments/create', [PaymentController::class, 'create']);
$router->post('/payments/store', [PaymentController::class, 'store']);
$router->post('/payments/simulate', [PaymentController::class, 'simulate']);
$router->get('/passages', [PromotionController::class, 'index']);
$router->post('/passages/valider', [PromotionController::class, 'approve']);
$router->get('/mon-espace', [StudentPortalController::class, 'index']);
$router->get('/mon-espace/photo', [StudentPhotoController::class, 'showMine']);
$router->get('/mon-espace/inscription', [StudentEnrollmentController::class, 'create']);
$router->post('/mon-espace/inscription', [StudentEnrollmentController::class, 'store']);

$roleRepository = new RoleRepository();
$roleRepository->ensureDefaultRoles();

$path = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$router->dispatch($_SERVER['REQUEST_METHOD'], $path);
