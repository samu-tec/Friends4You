<?php
// Punto de entrada de la aplicacion (front controller).
// Carga la configuracion y el codigo, mira la pagina pedida con ?page=...
// y llama al controlador que corresponda.

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/db.php';
require __DIR__ . '/../app/core/helpers.php';
require __DIR__ . '/../app/core/auth.php';
require __DIR__ . '/../app/controllers/auth_controller.php';
require __DIR__ . '/../app/controllers/user_controller.php';
require __DIR__ . '/../app/controllers/collaborator_controller.php';
require __DIR__ . '/../app/controllers/event_controller.php';
require __DIR__ . '/../app/controllers/admin_controller.php';
require __DIR__ . '/../app/controllers/api_controller.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'home';

try {
    switch ($page) {
        case 'home':
            render('home');
            break;
        case 'access':
            access_controller();
            break;
        case 'logout':
            logout_controller();
            break;
        case 'profile':
            profile_controller();
            break;
        case 'users':
            users_controller();
            break;
        case 'events':
            events_controller();
            break;
        case 'collaborator':
            collaborator_controller();
            break;
        case 'admin':
            admin_controller();
            break;
        case 'report':
            report_controller();
            break;
        case 'help':
            render('help');
            break;
        case 'api':
            api_controller();
            break;
        default:
            render('home', ['notFound' => true]);
            break;
    }
} catch (PDOException $e) {
    // Si falla la base de datos, mostramos un mensaje simple.
    http_response_code(500);
    echo 'No se ha podido conectar con la base de datos. ';
    echo 'Revisa la configuracion en app/config.php y los scripts SQL.';
}
