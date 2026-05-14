<?php
// Pruebas de unidad de las funciones de validacion y de escape.
// Se puede ejecutar por consola (php tests/test_validaciones.php)
// o abriendo el archivo desde el navegador.

require __DIR__ . '/../app/config.php';
require __DIR__ . '/../app/core/helpers.php';

$tests = [
    'Correo correcto' => validate_email_address('lucia@friends4you.com') === true,
    'Correo incorrecto' => validate_email_address('correo-invalido') === false,
    'Campos obligatorios' => count(validate_required_fields(['nombre' => '', 'ciudad' => 'Malaga'], ['nombre' => 'nombre'])) === 1,
    'Longitud minima de contrasena' => validate_password_length('12345678') === true && validate_password_length('1234') === false,
    'Escape HTML' => e('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;',
];

$esConsola = PHP_SAPI === 'cli';

if (!$esConsola) {
    echo '<!doctype html><html lang="es"><head><meta charset="utf-8">';
    echo '<title>Tests validaciones</title></head><body>';
    echo '<h1>Tests de validaciones</h1><ul>';
}

foreach ($tests as $nombre => $correcto) {
    $linea = ($correcto ? 'OK' : 'ERROR') . ' - ' . $nombre;
    if ($esConsola) {
        echo $linea . PHP_EOL;
    } else {
        echo '<li>' . e($linea) . '</li>';
    }
}

if (!$esConsola) {
    echo '</ul></body></html>';
}
