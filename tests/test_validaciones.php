<?php
/**
 * Pruebas de unidad de las funciones de validación y de escape.
 *
 * No necesita base de datos. Se ejecuta desde consola
 * (php tests/test_validaciones.php) o abriendo el archivo en el navegador.
 * El resultado de cada prueba se muestra como "OK" o "ERROR".
 */

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/helpers.php';

$pruebas = [
    'Correo correcto' => filter_var('lucia@friends4you.com', FILTER_VALIDATE_EMAIL) !== false,
    'Correo incorrecto' => filter_var('correo-invalido', FILTER_VALIDATE_EMAIL) === false,
    'Campos obligatorios' => count(validar_campos_obligatorios(['nombre' => '', 'ciudad' => 'Málaga'], ['nombre' => 'nombre'])) === 1,
    'Longitud mínima de contraseña' => strlen('12345678') >= 8 && strlen('1234') < 8,
    'Escape HTML' => escapar('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;',
];

$es_consola = PHP_SAPI === 'cli';

if (!$es_consola) {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Tests validaciones</title></head><body>';
    echo '<h1>Tests de validaciones</h1><ul>';
}

foreach ($pruebas as $nombre => $correcto) {
    $linea = ($correcto ? 'OK' : 'ERROR') . ' - ' . $nombre;
    if ($es_consola) {
        echo $linea . PHP_EOL;
    } else {
        echo '<li>' . escapar($linea) . '</li>';
    }
}

if (!$es_consola) {
    echo '</ul></body></html>';
}
