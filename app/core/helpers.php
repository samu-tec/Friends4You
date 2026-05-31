<?php
/**
 * Funciones auxiliares de uso general.
 *
 * Agrupa utilidades pequeñas que se usan en muchos sitios:
 *   - Escape de HTML, generación de URLs y redirecciones.
 *   - Avisos de un solo uso (duran hasta la siguiente página).
 *   - Renderizado de vistas con cabecera y pie comunes.
 *   - Validaciones sencillas de formularios.
 */

/**
 * Escapa un texto para imprimirlo dentro de HTML.
 *
 * Convierte caracteres especiales (<, >, ", ', &) en sus entidades HTML.
 * Es la defensa principal contra XSS al pintar valores del usuario.
 *
 * @param mixed $valor Texto o valor a escapar.
 * @return string Texto seguro para imprimir dentro de HTML.
 */
function escapar($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Indica si la petición actual es un envío de formulario (POST).
 *
 * @return bool true si el método HTTP es POST, false si no.
 */
function es_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Construye una URL interna a partir de la página y sus parámetros.
 *
 * Devuelve algo como "/Friends4You/public/?page=events&id_evento=3".
 *
 * @param string $pagina Identificador de la página (home, profile, events...).
 * @param array $parametros Parámetros adicionales (opcional).
 * @return string URL absoluta dentro de la aplicación.
 */
function enlace($pagina = 'home', $parametros = [])
{
    $parametros = array_merge(['page' => $pagina], $parametros);
    return BASE_URL . '?' . http_build_query($parametros);
}

/**
 * Redirige a otra página de la aplicación y termina el script.
 *
 * Manda una cabecera Location: y llama a exit, así no se ejecuta el resto
 * del código tras la redirección.
 *
 * @param string $pagina Página de destino.
 * @param array $parametros Parámetros adicionales (opcional).
 * @return void
 */
function redirigir($pagina = 'home', $parametros = [])
{
    header('Location: ' . enlace($pagina, $parametros));
    exit;
}

/**
 * Guarda un aviso en la sesión.
 *
 * Los avisos se muestran una sola vez en la siguiente página
 * (típicamente avisos de éxito, error o información tras una acción).
 *
 * @param string $tipo Tipo del mensaje: success, error o info.
 * @param string $mensaje Texto del mensaje.
 * @return void
 */
function aviso($tipo, $mensaje)
{
    $_SESSION['avisos'][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

/**
 * Devuelve los avisos pendientes y los borra de la sesión.
 *
 * Se llama desde la cabecera para pintar los avisos y vaciar la cola.
 *
 * @return array Lista de mensajes con sus claves "tipo" y "mensaje".
 */
function obtener_avisos()
{
    $mensajes = isset($_SESSION['avisos']) ? $_SESSION['avisos'] : [];
    unset($_SESSION['avisos']);
    return $mensajes;
}

/**
 * Renderiza una vista junto con la cabecera y el pie comunes.
 *
 * Los datos llegan en el array $datos. Con extract() se convierten en
 * variables sueltas: la clave "usuarios" pasa a ser $usuarios dentro de la vista,
 * "intereses" pasa a ser $intereses, etc. Así las plantillas pueden leer
 * directamente sus variables sin tener que escribir $datos['usuarios'].
 *
 * @param string $vista Nombre del archivo (sin extensión) dentro de app/views.
 * @param array $datos Datos que la vista necesita.
 * @return void
 */
function mostrar_vista($vista, $datos = [])
{
    extract($datos);

    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $vista . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}

/**
 * Devuelve la cadena "active" si la página indicada es la que se está viendo.
 *
 * Se usa en la barra de navegación para resaltar el enlace de la página actual.
 *
 * @param string $pagina Página a comparar con la actual.
 * @return string "active" si coincide, cadena vacía si no.
 */
function clase_activa($pagina)
{
    $actual = isset($_GET['page']) ? $_GET['page'] : 'home';
    return $actual === $pagina ? 'active' : '';
}

/**
 * Da formato dd/mm/aaaa hh:mm a una fecha de la base de datos.
 *
 * Si la fecha está vacía devuelve cadena vacía para no romper el HTML.
 *
 * @param string|null $fecha Fecha en formato MySQL ("Y-m-d H:i:s") o null.
 * @return string Fecha en formato español o cadena vacía.
 */
function formatear_fecha($fecha)
{
    if (!$fecha) {
        return '';
    }
    return date('d/m/Y H:i', strtotime($fecha));
}

/**
 * Devuelve las iniciales (en mayúsculas) a partir del nombre y los apellidos.
 *
 * Coge la primera letra del nombre y la primera letra del apellido y las
 * pasa a mayúsculas. Si no hay datos devuelve "?" para no dejar el avatar
 * en blanco.
 *
 * @param string $nombre Nombre del usuario.
 * @param string $apellidos Apellidos del usuario (opcional).
 * @return string Una o dos letras en mayúscula, o "?" si no hay datos.
 */
function iniciales($nombre, $apellidos = '')
{
    $inicial_nombre = substr(trim((string) $nombre), 0, 1);
    $inicial_apellido = substr(trim((string) $apellidos), 0, 1);
    $letras = $inicial_nombre . $inicial_apellido;
    return $letras !== '' ? strtoupper($letras) : '?';
}

/**
 * Devuelve "selected" si el valor coincide con el esperado.
 *
 * Pensada para marcar la opción correcta en una etiqueta <select>.
 *
 * @param mixed $actual Valor seleccionado actualmente.
 * @param mixed $esperado Valor de la opción a comprobar.
 * @return string "selected" si coinciden, cadena vacía si no.
 */
function valor_seleccionado($actual, $esperado)
{
    return (string) $actual === (string) $esperado ? 'selected' : '';
}

// ---------------------------------------------------------------------
// Validaciones de formularios
// ---------------------------------------------------------------------

/**
 * Comprueba que los campos indicados están rellenos.
 *
 * Recibe los datos del formulario y la lista de campos obligatorios con
 * la etiqueta que se mostrará en el mensaje de error. Devuelve un mensaje
 * por cada campo que falte.
 *
 * @param array $datos Datos del formulario (normalmente $_POST).
 * @param array $campos Mapa "nombre_campo" => "etiqueta visible".
 * @return array Lista de mensajes de error (vacía si todo está bien).
 */
function validar_campos_obligatorios($datos, $campos)
{
    $errores = [];

    foreach ($campos as $campo => $etiqueta) {
        $valor = isset($datos[$campo]) ? $datos[$campo] : '';
        if (trim((string) $valor) === '') {
            $errores[] = 'El campo "' . $etiqueta . '" es obligatorio. Por favor, rellénalo.';
        }
    }

    return $errores;
}

/**
 * Comprueba que una fecha tiene el formato del campo HTML datetime-local.
 *
 * datetime-local envía valores como "2026-09-12T18:00", sin segundos.
 *
 * @param string $valor Cadena recibida del input datetime-local.
 * @return bool true si el formato es válido, false si no.
 */
function validar_fecha_hora($valor)
{
    // datetime-local envía valores como "2026-09-12T18:00". Comprobamos
    // ese formato con un patrón sencillo: 4 dígitos guion 2 guion 2, etc.
    return preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $valor) === 1;
}
