<?php // Cabecera comun: HTML, barra de navegacion y mensajes flash. ?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(APP_NAME) ?></title>
    <meta name="base-url" content="<?= e(BASE_URL) ?>">
    <link rel="stylesheet" href="<?= e(BASE_URL) ?>assets/css/styles.css">
</head>
<body>

<?php $usuario = current_user(); ?>
<header class="site-header">
    <div class="site-header__inner">
        <a class="brand" href="<?= e(url('home')) ?>"><?= e(APP_NAME) ?></a>
        <button class="menu-toggle" type="button" data-menu-toggle aria-label="Abrir menu">&#9776;</button>
        <nav class="main-nav" data-main-nav>
            <a class="<?= e(active_class('home')) ?>" href="<?= e(url('home')) ?>">Inicio</a>
            <?php if ($usuario): ?>
                <a class="<?= e(active_class('profile')) ?>" href="<?= e(url('profile')) ?>">Perfil</a>
                <a class="<?= e(active_class('users')) ?>" href="<?= e(url('users')) ?>">Usuarios</a>
                <a class="<?= e(active_class('events')) ?>" href="<?= e(url('events')) ?>">Eventos</a>
                <?php if (has_role('colaborador')): ?>
                    <a class="<?= e(active_class('collaborator')) ?>" href="<?= e(url('collaborator')) ?>">Colaborador</a>
                <?php endif; ?>
                <?php if (has_role('administrador')): ?>
                    <a class="<?= e(active_class('admin')) ?>" href="<?= e(url('admin')) ?>">Admin</a>
                    <a class="<?= e(active_class('report')) ?>" href="<?= e(url('report')) ?>">Informe</a>
                <?php endif; ?>
                <a class="<?= e(active_class('help')) ?>" href="<?= e(url('help')) ?>">Ayuda</a>
                <a class="main-nav__logout" href="<?= e(url('logout')) ?>">Salir</a>
            <?php else: ?>
                <a class="<?= e(active_class('access')) ?>" href="<?= e(url('access')) ?>">Acceso</a>
                <a class="<?= e(active_class('help')) ?>" href="<?= e(url('help')) ?>">Ayuda</a>
            <?php endif; ?>
        </nav>
    </div>
    <?php if ($usuario): ?>
        <div class="user-strip">
            Sesion: <strong><?= e($usuario['nombre']) ?></strong> &middot;
            Rol: <span class="badge badge--rol"><?= e(ucfirst($usuario['rol'])) ?></span>
        </div>
    <?php endif; ?>
</header>

<main class="container">
    <?php foreach (get_flashes() as $mensaje): ?>
        <div class="alert alert--<?= e($mensaje['type']) ?>">
            <?= e($mensaje['message']) ?>
        </div>
    <?php endforeach; ?>
