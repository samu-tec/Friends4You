<?php
/**
 * Controlador de la API JSON interna.
 *
 * Da soporte a las peticiones AJAX del frontend, por ejemplo el filtrado
 * de eventos por interés sin recargar la página. Las respuestas son JSON
 * con cabecera "Content-Type: application/json; charset=utf-8".
 */

/**
 * Página /api: punto de entrada de la API interna.
 *
 * Exige sesión iniciada (las peticiones AJAX usan la misma cookie de
 * sesión que el resto de la aplicación). Elige qué hacer según el
 * parámetro "accion" de la URL.
 *
 * Acciones disponibles:
 *   - eventos: devuelve los eventos activos en formato JSON, con filtro
 *              opcional por id_interes.
 *
 * @return void
 */
function controlador_api()
{
    header('Content-Type: application/json; charset=utf-8');

    if (!hay_sesion()) {
        http_response_code(401);
        echo json_encode(['error' => 'Sesión requerida']);
        exit;
    }

    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';
    $bd = obtener_bd();

    if ($accion === 'eventos') {
        $filtro_interes = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
        echo json_encode(obtener_eventos($bd, $filtro_interes));
        exit;
    }

    http_response_code(404);
    echo json_encode(['error' => 'Acción no encontrada']);
    exit;
}
