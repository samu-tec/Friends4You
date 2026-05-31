<?php
/**
 * Pruebas de integración básica con la base de datos.
 *
 * Comprueba que los scripts SQL iniciales se han ejecutado correctamente:
 * existen los tres roles, las seis cuentas de prueba, los intereses,
 * los colaboradores, los eventos activos y las amistades y asistencias
 * iniciales. Sirve para detectar rápidamente una BD recién importada
 * que no se ha cargado bien.
 */

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/db.php';

$es_consola = PHP_SAPI === 'cli';

/**
 * Imprime el resultado de una prueba (en consola o en HTML).
 *
 * @param string $nombre Nombre descriptivo de la prueba.
 * @param bool $correcto Resultado de la comprobación.
 * @param bool $es_consola Indica si la salida es por consola o navegador.
 * @return void
 */
function imprimir_resultado($nombre, $correcto, $es_consola)
{
    $linea = ($correcto ? 'OK' : 'ERROR') . ' - ' . $nombre;
    if ($es_consola) {
        echo $linea . PHP_EOL;
    } else {
        echo '<li>' . htmlspecialchars($linea, ENT_QUOTES, 'UTF-8') . '</li>';
    }
}

if (!$es_consola) {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Tests integración</title></head><body>';
    echo '<h1>Tests de integración básica</h1><ul>';
}

try {
    $bd = obtener_bd();
    $pruebas = [];

    $pruebas['Roles principales creados'] = (int) $bd->query(
        'SELECT COUNT(*) FROM rol WHERE nombre IN ("administrador", "usuario", "colaborador")'
    )->fetchColumn() === 3;

    $pruebas['Usuarios de prueba creados'] = (int) $bd->query(
        'SELECT COUNT(*) FROM usuario WHERE correo IN (
            "admin@friends4you.com",
            "lucia@friends4you.com",
            "carlos@friends4you.com",
            "marta@friends4you.com",
            "padelclub@friends4you.com",
            "cafeteriaplaza@friends4you.com"
        )'
    )->fetchColumn() === 6;

    $consulta = $bd->prepare('SELECT contrasena FROM usuario WHERE correo = ? LIMIT 1');
    $consulta->execute(['admin@friends4you.com']);
    $hash_admin = (string) $consulta->fetchColumn();
    $pruebas['Hash de contraseña compatible con login'] = password_verify('1234', $hash_admin);

    $pruebas['Intereses suficientes para pruebas'] = (int) $bd->query(
        'SELECT COUNT(*) FROM interes'
    )->fetchColumn() >= 12;

    $pruebas['Colaboradores iniciales creados'] = (int) $bd->query(
        'SELECT COUNT(*) FROM colaborador'
    )->fetchColumn() >= 2;

    $pruebas['Eventos activos disponibles'] = (int) $bd->query(
        'SELECT COUNT(*) FROM evento WHERE estado_evento = "activo"'
    )->fetchColumn() >= 3;

    $pruebas['Solicitudes o amistades iniciales'] = (int) $bd->query(
        'SELECT COUNT(*) FROM amistad'
    )->fetchColumn() >= 2;

    $pruebas['Asistencias confirmadas iniciales'] = (int) $bd->query(
        'SELECT COUNT(*) FROM asistencia WHERE estado_asistencia = "confirmada"'
    )->fetchColumn() >= 4;

    foreach ($pruebas as $nombre => $correcto) {
        imprimir_resultado($nombre, $correcto, $es_consola);
    }
} catch (Exception $excepcion) {
    imprimir_resultado('Conexión e integración con base de datos: ' . $excepcion->getMessage(), false, $es_consola);
}

if (!$es_consola) {
    echo '</ul></body></html>';
}
