<?php
/**
 * Punto de entrada de la aplicación (front controller).
 *
 * Apache está configurado para que cualquier petición termine aquí. Este
 * archivo carga la configuración y el resto del código, mira el parámetro
 * "page" de la URL y llama al controlador correspondiente. Si ocurre un
 * error de base de datos no controlado se muestra un mensaje simple.
 */

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

$pagina = isset($_GET['page']) ? $_GET['page'] : 'home';

try {
    switch ($pagina) {
        case 'home':
            mostrar_vista('home');
            break;
        case 'access':
            controlador_acceso();
            break;
        case 'logout':
            controlador_salir();
            break;
        case 'profile':
            controlador_perfil();
            break;
        case 'users':
            controlador_usuarios();
            break;
        case 'events':
            controlador_eventos();
            break;
        case 'collaborator':
            controlador_colaborador();
            break;
        case 'admin':
            controlador_admin();
            break;
        case 'help':
            mostrar_vista('help');
            break;
        case 'api':
            controlador_api();
            break;
        default:
            mostrar_vista('home', ['no_encontrada' => true]);
            break;
    }
} catch (PDOException $excepcion) {
    // Si falla la base de datos, mostramos un mensaje simple para que el
    // usuario sepa qué ocurre sin exponer detalles técnicos por pantalla.
    http_response_code(500);
    echo 'No se ha podido conectar con la base de datos. ';
    echo 'Revisa la configuración en app/config.php y los scripts SQL.';
}
