<?php
// Configuracion general de la aplicacion.
// Los valores por defecto sirven para XAMPP en local. En el despliegue con
// Docker se pasan por variables de entorno (getenv) para no tocar el codigo.

define('APP_NAME', 'Friends4You');

// URL base de la aplicacion (XAMPP por defecto).
$base = getenv('F4Y_BASE_URL');
if (!$base) {
    $base = '/Friends4You/public/';
}
if (substr($base, -1) !== '/') {
    $base = $base . '/';
}
define('BASE_URL', $base);

// Datos de conexion a la base de datos MySQL.
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');

// Zona horaria para las fechas.
date_default_timezone_set('Europe/Madrid');
