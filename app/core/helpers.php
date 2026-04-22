<?php
// Funciones auxiliares de uso general:
//  - Escape de HTML, URLs y redirecciones.
//  - Mensajes flash para avisar al usuario.
//  - Pintar las vistas con cabecera y pie comunes.
//  - Validaciones sencillas de formularios.

// Escapa un texto para imprimirlo en HTML y evitar inyeccion (XSS).
function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// Indica si la peticion actual es un envio de formulario (POST).
function is_post()
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Construye una URL interna a partir de la pagina y sus parametros.
function url($page = 'home', $params = [])
{
    $params = array_merge(['page' => $page], $params);
    return BASE_URL . '?' . http_build_query($params);
}

// Redirige a otra pagina de la aplicacion y termina el script.
function redirect_to($page = 'home', $params = [])
{
    header('Location: ' . url($page, $params));
    exit;
}

// Guarda un mensaje en la sesion para mostrarlo en la siguiente pagina.
function flash($tipo, $mensaje)
{
    $_SESSION['flash'][] = ['type' => $tipo, 'message' => $mensaje];
}

// Devuelve los mensajes flash y los borra de la sesion.
function get_flashes()
{
    $mensajes = isset($_SESSION['flash']) ? $_SESSION['flash'] : [];
    unset($_SESSION['flash']);
    return $mensajes;
}

// Pinta una vista con la cabecera y el pie comunes.
// Las claves de $data se convierten en variables dentro de la vista.
function render($vista, $data = [])
{
    extract($data);

    require __DIR__ . '/../views/layout/header.php';
    require __DIR__ . '/../views/' . $vista . '.php';
    require __DIR__ . '/../views/layout/footer.php';
}

// Devuelve "active" si la pagina indicada es la que se esta viendo (menu).
function active_class($page)
{
    $actual = isset($_GET['page']) ? $_GET['page'] : 'home';
    return $actual === $page ? 'active' : '';
}

// Da formato dd/mm/aaaa hh:mm a una fecha de la base de datos.
function format_date($fecha)
{
    if (!$fecha) {
        return '';
    }
    return date('d/m/Y H:i', strtotime($fecha));
}

// Devuelve las iniciales en mayusculas a partir del nombre y apellidos.
function initials($nombre, $apellidos = '')
{
    $a = mb_substr(trim((string) $nombre), 0, 1, 'UTF-8');
    $b = mb_substr(trim((string) $apellidos), 0, 1, 'UTF-8');
    $iniciales = $a . $b;
    return $iniciales !== '' ? mb_strtoupper($iniciales, 'UTF-8') : '?';
}

// Devuelve "selected" si el valor coincide (para los <select>).
function selected_value($actual, $esperado)
{
    return (string) $actual === (string) $esperado ? 'selected' : '';
}

// ---------------------------------------------------------------------
// Validaciones de formularios
// ---------------------------------------------------------------------

// Recorta espacios y convierte a texto.
function clean_text($valor)
{
    return trim((string) $valor);
}

// Comprueba que el correo tiene un formato valido.
function validate_email_address($email)
{
    return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Comprueba que los campos indicados estan rellenos.
// Devuelve un array con un mensaje por cada campo que falte.
function validate_required_fields($data, $campos)
{
    $errores = [];

    foreach ($campos as $campo => $etiqueta) {
        $valor = isset($data[$campo]) ? $data[$campo] : '';
        if (clean_text($valor) === '') {
            $errores[] = 'El campo "' . $etiqueta . '" es obligatorio. Por favor, rellenalo.';
        }
    }

    return $errores;
}

// Comprueba que la contrasena tiene la longitud minima.
function validate_password_length($password, $minimo = 8)
{
    return strlen($password) >= $minimo;
}

// Comprueba que la fecha tiene el formato del campo datetime-local.
function validate_datetime_value($valor)
{
    $fecha = DateTime::createFromFormat('Y-m-d\TH:i', $valor);
    return $fecha !== false;
}
