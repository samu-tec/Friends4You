<?php /** Vista de la página de inicio: presentación de la aplicación y descripción de los roles. */ ?>
<?php if (!empty($notFound)): ?>
    <div class="alert alert--error">La página solicitada no existe. Te hemos redirigido al inicio.</div>
<?php endif; ?>

<section class="hero">
    <div>
        <p class="eyebrow">Red social para organizar quedadas</p>
        <h1>Friends4You</h1>
        <p>
            Aplicación web para conocer personas con intereses parecidos,
            buscar usuarios por ciudad, gestionar amistades y apuntarse a eventos.
            Una alternativa sencilla y centrada en lo importante: hacer amigos.
        </p>
        <div class="actions">
            <?php if (is_logged_in()): ?>
                <a class="button" href="<?= e(url('profile')) ?>">Ir a mi perfil</a>
                <a class="button button--secondary" href="<?= e(url('events')) ?>">Ver eventos</a>
            <?php else: ?>
                <a class="button" href="<?= e(url('access')) ?>">Iniciar sesión o registrarse</a>
                <a class="button button--secondary" href="<?= e(url('help')) ?>">Ver ayuda</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-panel">
        <h2>Funciones principales</h2>
        <ul class="clean-list">
            <li><strong>Registro y acceso</strong><span class="muted">Contraseñas cifradas con bcrypt</span></li>
            <li><strong>Perfil personalizable</strong><span class="muted">Ciudad e intereses propios</span></li>
            <li><strong>Solicitudes de amistad</strong><span class="muted">Acepta o rechaza invitaciones</span></li>
            <li><strong>Eventos y quedadas</strong><span class="muted">Apúntate o crea los tuyos</span></li>
            <li><strong>Panel de admin</strong><span class="muted">Gestión y estadísticas</span></li>
        </ul>
    </div>
</section>

<section class="section">
    <h2>¿Quién puede usar Friends4You?</h2>
    <div class="grid three">
        <article class="card">
            <h3>Usuario</h3>
            <p class="muted">Gestiona su perfil, busca personas por ciudad e intereses, envía solicitudes de amistad y participa en eventos.</p>
            <p><span class="badge badge--usuario">Rol: Usuario</span></p>
        </article>
        <article class="card">
            <h3>Colaborador</h3>
            <p class="muted">Representa un local o negocio y puede crear eventos vinculados a su establecimiento, ganando visibilidad.</p>
            <p><span class="badge badge--colaborador">Rol: Colaborador</span></p>
        </article>
        <article class="card">
            <h3>Administrador</h3>
            <p class="muted">Gestiona usuarios, intereses, colaboradores y eventos. Accede al informe estadístico del sistema.</p>
            <p><span class="badge badge--administrador">Rol: Administrador</span></p>
        </article>
    </div>
</section>
