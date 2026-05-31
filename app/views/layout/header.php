<?php /**
 * Cabecera común a todas las vistas.
 *
 * Pinta el <head>, la barra de navegación (que cambia según el rol del
 * usuario conectado) y los avisos pendientes de mostrar.
 */ ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= escapar(APP_NAME) ?></title>
    <meta name="base-url" content="<?= escapar(BASE_URL) ?>">
    <link rel="icon" type="image/png" href="<?= escapar(BASE_URL) ?>assets/img/logo.png">
    <link rel="stylesheet" href="<?= escapar(BASE_URL) ?>assets/css/styles.css?v=<?= filemtime(__DIR__ . '/../../../public/assets/css/styles.css') ?>">
</head>
<body>

<?php $usuario = usuario_actual(); ?>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= escapar(enlace('home')) ?>">
            <img src="<?= escapar(BASE_URL) ?>assets/img/logo.png" alt="<?= escapar(APP_NAME) ?>">
            <?= escapar(APP_NAME) ?>
        </a>
        <button class="menu-toggle" type="button" data-menu-toggle aria-label="Abrir menú">&#9776;</button>
        <nav class="main-nav" data-main-nav>
            <a class="<?= escapar(clase_activa('home')) ?>" href="<?= escapar(enlace('home')) ?>">Inicio</a>
            <?php if ($usuario): ?>
                <a class="<?= escapar(clase_activa('profile')) ?>" href="<?= escapar(enlace('profile')) ?>">Perfil</a>
                <?php if (tiene_rol('usuario')): ?>
                    <a class="<?= escapar(clase_activa('users')) ?>" href="<?= escapar(enlace('users')) ?>">Usuarios</a>
                <?php endif; ?>
                <a class="<?= escapar(clase_activa('events')) ?>" href="<?= escapar(enlace('events')) ?>">Eventos</a>
                <?php if (tiene_rol('colaborador')): ?>
                    <a class="<?= escapar(clase_activa('collaborator')) ?>" href="<?= escapar(enlace('collaborator')) ?>">Colaborador</a>
                <?php endif; ?>
                <?php if (tiene_rol('administrador')): ?>
                    <a class="<?= escapar(clase_activa('admin')) ?>" href="<?= escapar(enlace('admin')) ?>">Admin</a>
                <?php endif; ?>
                <a class="<?= escapar(clase_activa('help')) ?>" href="<?= escapar(enlace('help')) ?>">Ayuda</a>
                <a class="nav-logout" href="<?= escapar(enlace('logout')) ?>">Salir</a>
            <?php else: ?>
                <a class="<?= escapar(clase_activa('access')) ?>" href="<?= escapar(enlace('access')) ?>">Acceso</a>
                <a class="<?= escapar(clase_activa('help')) ?>" href="<?= escapar(enlace('help')) ?>">Ayuda</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php if ($usuario): ?>
        <div class="user-strip">
            Sesión: <strong><?= escapar($usuario['nombre']) ?></strong> &middot;
            Rol: <span class="badge badge--<?= escapar($usuario['rol']) ?>"><?= escapar(ucfirst($usuario['rol'])) ?></span>
        </div>
    <?php endif; ?>
</header>

<main class="container">
    <?php foreach (obtener_avisos() as $mensaje): ?>
        <div class="alert alert--<?= escapar($mensaje['tipo']) ?>">
            <?= escapar($mensaje['mensaje']) ?>
        </div>
    <?php endforeach; ?>
