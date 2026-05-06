<?php
// Controlador de la API en JSON.
// Da soporte a las peticiones AJAX del frontend, por ejemplo el filtrado
// de eventos por interes sin recargar la pagina.

function api_controller()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['error' => 'Sesion requerida']);
        exit;
    }

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $pdo = get_db();

    if ($action === 'events') {
        $filtroInteres = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
        echo json_encode(fetch_events($pdo, $filtroInteres));
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Accion no encontrada']);
    exit;
}
