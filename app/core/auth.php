<?php
// Autenticacion: inicio de sesion, datos del usuario conectado
// y comprobacion de roles para proteger las paginas privadas.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Guarda los datos del usuario en la sesion tras un login correcto.
function login_user($usuario)
{
    $_SESSION['user'] = [
        'id_usuario' => (int) $usuario['id_usuario'],
        'nombre' => $usuario['nombre'],
        'correo' => $usuario['correo'],
        'rol' => $usuario['rol'],
    ];
}

// Devuelve los datos del usuario conectado o null si no hay sesion.
function current_user()
{
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

// Devuelve el id del usuario conectado o null.
function current_user_id()
{
    return isset($_SESSION['user']['id_usuario']) ? (int) $_SESSION['user']['id_usuario'] : null;
}

// Indica si hay un usuario con sesion iniciada.
function is_logged_in()
{
    return current_user() !== null;
}

// Comprueba si el usuario tiene uno de los roles indicados.
function has_role($roles)
{
    $usuario = current_user();
    if (!$usuario) {
        return false;
    }

    if (!is_array($roles)) {
        $roles = [$roles];
    }

    return in_array($usuario['rol'], $roles);
}

// Si no hay sesion, manda a la pagina de acceso.
function require_login()
{
    if (!is_logged_in()) {
        flash('error', 'Debes iniciar sesion para acceder a esta pagina.');
        redirect_to('access');
    }
}

// Si el usuario no tiene el rol necesario, vuelve al inicio.
function require_role($roles)
{
    require_login();

    if (!has_role($roles)) {
        flash('error', 'No tienes permisos para acceder a esta seccion.');
        redirect_to('home');
    }
}

// Cierra la sesion del usuario.
function logout_user()
{
    unset($_SESSION['user']);
    flash('success', 'Sesion cerrada correctamente.');
}
