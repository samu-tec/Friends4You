<?php
/**
 * Autenticación y control de acceso por roles.
 *
 * Inicia la sesión, guarda y consulta los datos del usuario conectado y
 * ofrece dos funciones (exigir_sesion y exigir_rol) para proteger las
 * páginas privadas redirigiendo al acceso o al inicio si no procede.
 */

// Arranca la sesión si todavía no estaba iniciada (PHP solo permite una).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Guarda los datos del usuario en la sesión tras un login correcto.
 *
 * Solo se guarda lo imprescindible (id, nombre, correo y rol), no la
 * contraseña ni el hash. El resto se vuelve a consultar a la base de datos
 * cuando hace falta.
 *
 * @param array $usuario Fila de la tabla usuario con la columna extra "rol".
 * @return void
 */
function iniciar_sesion_usuario($usuario)
{
    $_SESSION['usuario'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nombre' => $usuario['nombre'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol'],
    ];
}

/**
 * Devuelve los datos del usuario conectado o null si no hay sesión.
 *
 * @return array|null Array con id_usuario, nombre, correo y rol; null si no hay sesión.
 */
function usuario_actual()
{
    return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
}

/**
 * Devuelve el id del usuario conectado o null si no hay sesión.
 *
 * @return int|null Id del usuario actual, o null si no hay sesión.
 */
function id_usuario_actual()
{
    return isset($_SESSION['usuario']['id_usuario']) ? (int) $_SESSION['usuario']['id_usuario'] : null;
}

/**
 * Indica si hay un usuario con sesión iniciada.
 *
 * @return bool true si hay sesión, false si no.
 */
function hay_sesion()
{
    return usuario_actual() !== null;
}

/**
 * Comprueba si el usuario conectado tiene alguno de los roles indicados.
 *
 * Acepta un único rol (cadena) o varios (array). Si no hay sesión devuelve
 * directamente false.
 *
 * @param string|array $roles Rol o lista de roles a comprobar.
 * @return bool true si el usuario tiene alguno de esos roles, false si no.
 */
function tiene_rol($roles)
{
    $usuario = usuario_actual();
    if (!$usuario) {
        return false;
    }

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    return in_array($usuario['rol'], $roles);
}

/**
 * Bloquea el acceso a usuarios sin sesión iniciada.
 *
 * Si no hay usuario en la sesión, muestra un aviso y redirige a la página
 * de acceso. Las funciones que requieren sesión la llaman al principio.
 *
 * @return void
 */
function exigir_sesion()
{
    if (!hay_sesion()) {
        aviso('error', 'Debes iniciar sesión para acceder a esta página.');
        redirigir('access');
    }
}

/**
 * Bloquea el acceso si el usuario no tiene uno de los roles indicados.
 *
 * Primero exige sesión y después comprueba el rol. Si el rol no coincide,
 * muestra un aviso y devuelve al usuario a la página de inicio.
 *
 * @param string|array $roles Rol o lista de roles permitidos.
 * @return void
 */
function exigir_rol($roles)
{
    exigir_sesion();

    if (!tiene_rol($roles)) {
        aviso('error', 'No tienes permisos para acceder a esta sección.');
        redirigir('home');
    }
}

/**
 * Cierra la sesión del usuario eliminando sus datos.
 *
 * @return void
 */
function cerrar_sesion_usuario()
{
    unset($_SESSION['usuario']);
    aviso('success', 'Sesión cerrada correctamente.');
}
