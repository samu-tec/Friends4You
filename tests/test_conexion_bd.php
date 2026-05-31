<?php
/**
 * Prueba de conexión a la base de datos.
 *
 * Abre la conexión PDO con la configuración de app/config.php y muestra la
 * versión de MySQL si todo va bien, o el mensaje de error si falla.
 * Se puede ejecutar desde consola (php tests/test_conexion_bd.php) o
 * abriendo el archivo directamente con el navegador.
 */

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/db.php';

$es_consola = PHP_SAPI === 'cli';

try {
    $bd = obtener_bd();
    $version = $bd->query('SELECT VERSION()')->fetchColumn();
    $mensaje = 'OK - Conexión correcta a friends4you. Versión MySQL: ' . $version;
} catch (Exception $excepcion) {
    $mensaje = 'ERROR - No se ha podido conectar a la base de datos: ' . $excepcion->getMessage();
}

if ($es_consola) {
    echo $mensaje . PHP_EOL;
} else {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Test conexión BD</title></head><body>';
    echo '<h1>Test de conexión a base de datos</h1>';
    echo '<p>' . htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') . '</p>';
    echo '</body></html>';
}
