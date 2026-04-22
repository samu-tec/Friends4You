<?php
// Conexion a la base de datos con PDO.
// Se guarda en una variable global para reutilizar la misma conexion
// durante toda la peticion en vez de abrir una nueva en cada consulta.

function get_db()
{
    global $conexion;

    if (!$conexion) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';

        $conexion = new PDO($dsn, DB_USER, DB_PASS);
        // Lanzar excepcion si una consulta falla.
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // Devolver las filas como array asociativo.
        $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    return $conexion;
}
