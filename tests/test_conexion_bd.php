<?php
// Prueba de conexion a la base de datos.
// Abre la conexion PDO y muestra la version de MySQL, o el error si falla.

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/db.php';

$esConsola = PHP_SAPI === 'cli';

try {
    $pdo = get_db();
    $version = $pdo->query('SELECT VERSION()')->fetchColumn();
    $mensaje = 'OK - Conexion correcta a friends4you. Version MySQL: ' . $version;
} catch (Exception $e) {
    $mensaje = 'ERROR - No se ha podido conectar a la base de datos: ' . $e->getMessage();
}

if ($esConsola) {
    echo $mensaje . PHP_EOL;
} else {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Test conexion BD</title></head><body>';
    echo '<h1>Test de conexion a base de datos</h1>';
    echo '<p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
}
