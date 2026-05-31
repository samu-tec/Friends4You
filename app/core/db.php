<?php
/**
 * Conexión a la base de datos mediante PDO.
 *
 * Se guarda la conexión en una variable global para reutilizarla durante
 * toda la petición y no abrir una conexión nueva cada vez.
 */

/**
 * Devuelve la conexión PDO a la base de datos.
 *
 * Crea la conexión la primera vez que se llama y la reutiliza después.
 * Configura PDO para que las consultas que fallen lancen una excepción y
 * para que las filas se devuelvan como array asociativo.
 *
 * @return PDO Conexión a la base de datos lista para usar.
 */
function obtener_bd()
{
    global $conexion_bd;

    if (!isset($conexion_bd)) {
        $cadena_conexion = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        $conexion_bd = new PDO($cadena_conexion, DB_USER, DB_PASS);
        // Si una consulta falla, lanzar una excepción (la captura el index.php).
        $conexion_bd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Devolver las filas como array asociativo (acceso por nombre de columna).
        $conexion_bd->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $conexion_bd;
}
