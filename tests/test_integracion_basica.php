<?php
// Pruebas de integracion con la base de datos.
// Comprueba que los datos iniciales (script 02) se han cargado bien.

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/db.php';

$esConsola = PHP_SAPI === 'cli';

// Imprime el resultado de una prueba (en consola o en HTML).
function mostrar_resultado($nombre, $correcto, $esConsola)
{
    $linea = ($correcto ? 'OK' : 'ERROR') . ' - ' . $nombre;
    if ($esConsola) {
        echo $linea . PHP_EOL;
    } else {
        echo '<li>' . htmlspecialchars($linea, ENT_QUOTES, 'UTF-8') . '</li>';
    }
}

if (!$esConsola) {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Tests integracion</title></head><body>';
    echo '<h1>Tests de integracion basica</h1><ul>';
}

try {
    $pdo = get_db();
    $tests = [];

    $tests['Roles principales creados'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM rol WHERE nombre IN ("administrador", "usuario", "colaborador")'
    )->fetchColumn() === 3;

    $tests['Usuarios de prueba creados'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM usuario WHERE correo IN (
            "admin@friends4you.com",
            "lucia@friends4you.com",
            "carlos@friends4you.com",
            "marta@friends4you.com",
            "padelclub@friends4you.com",
            "cafeteriaplaza@friends4you.com"
        )'
    )->fetchColumn() === 6;

    $stmt = $pdo->prepare('SELECT contrasena FROM usuario WHERE correo = ? LIMIT 1');
    $stmt->execute(['admin@friends4you.com']);
    $hashAdmin = (string) $stmt->fetchColumn();
    $tests['Password hash compatible con login'] = password_verify('1234', $hashAdmin);

    $tests['Intereses suficientes para pruebas'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM interes'
    )->fetchColumn() >= 12;

    $tests['Colaboradores iniciales creados'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM colaborador'
    )->fetchColumn() >= 2;

    $tests['Eventos activos disponibles'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM evento WHERE estado_evento = "activo"'
    )->fetchColumn() >= 3;

    $tests['Solicitudes o amistades iniciales'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM amistad'
    )->fetchColumn() >= 2;

    $tests['Asistencias confirmadas iniciales'] = (int) $pdo->query(
        'SELECT COUNT(*) FROM asistencia WHERE estado_asistencia = "confirmada"'
    )->fetchColumn() >= 4;

    foreach ($tests as $nombre => $correcto) {
        mostrar_resultado($nombre, $correcto, $esConsola);
    }
} catch (Exception $e) {
    mostrar_resultado('Conexion e integracion con base de datos: ' . $e->getMessage(), false, $esConsola);
}

if (!$esConsola) {
    echo '</ul></body></html>';
}
