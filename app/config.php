<?php
/**
 * Configuración general de la aplicación Friends4You.
 *
 * Aquí se definen las constantes con la URL base y los datos de conexión
 * a la base de datos. Los valores por defecto sirven para XAMPP en local.
 * En el despliegue con Docker se sobrescriben mediante variables de entorno
 * (getenv) para no tener que tocar el código fuente al cambiar de entorno.
 */

// Nombre que aparece en la pestaña del navegador y en el pie de página.
define('APP_NAME', 'Friends4You');

// URL base de la aplicación (en XAMPP cuelga de /Friends4You/public/).
$url_base = getenv('F4Y_BASE_URL');
if (!$url_base) {
    $url_base = '/Friends4You/public/';
}
if (substr($url_base, -1) !== '/') {
    $url_base = $url_base . '/';
}
define('BASE_URL', $url_base);

// Datos de conexión a la base de datos MySQL.
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');

// Zona horaria para las fechas (date(), strtotime(), DATETIME...).
date_default_timezone_set('Europe/Madrid');
