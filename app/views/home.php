<?php /** Vista de la página de inicio: presentación de la aplicación para el usuario. */ ?>
<?php if (!empty($no_encontrada)): ?>
    <div class="alert alert--error">La página solicitada no existe. Te hemos redirigido al inicio.</div>
<?php endif; ?>

<section class="hero">
    <div>
        <p class="eyebrow">Conoce gente y organiza quedadas</p>
        <h1>Friends4You</h1>
        <p>
            Friends4You te ayuda a hacer nuevas amistades con personas de tu
            ciudad que comparten tus intereses. Crea tu perfil, busca gente
            afín, envía solicitudes de amistad y apúntate a quedadas y eventos.
        </p>
        <div class="actions">
            <?php if (hay_sesion()): ?>
                <a class="button" href="<?= escapar(enlace('profile')) ?>">Ir a mi perfil</a>
                <a class="button button-secondary" href="<?= escapar(enlace('events')) ?>">Ver eventos</a>
            <?php else: ?>
                <a class="button" href="<?= escapar(enlace('access')) ?>">Crear cuenta o iniciar sesión</a>
                <a class="button button-secondary" href="<?= escapar(enlace('help')) ?>">Ver ayuda</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="hero-panel">
        <h2>¿Qué puedes hacer?</h2>
        <ul class="clean-list">
            <li><strong>Crear tu perfil</strong><span class="muted">Tu ciudad y tus intereses</span></li>
            <li><strong>Buscar gente</strong><span class="muted">Por ciudad e intereses comunes</span></li>
            <li><strong>Hacer amigos</strong><span class="muted">Envía y acepta solicitudes</span></li>
            <li><strong>Apuntarte a quedadas</strong><span class="muted">O crear las tuyas</span></li>
        </ul>
    </div>
</section>

<section class="section">
    <h2>Cómo funciona</h2>
    <div class="grid three">
        <article class="card">
            <h3>1. Crea tu cuenta</h3>
            <p class="muted">Regístrate gratis, indica tu ciudad y marca los temas que te gustan en tu perfil.</p>
        </article>
        <article class="card">
            <h3>2. Encuentra gente</h3>
            <p class="muted">Busca usuarios por ciudad o interés y envíales una solicitud de amistad.</p>
        </article>
        <article class="card">
            <h3>3. Quedad en persona</h3>
            <p class="muted">Apúntate a los eventos que te interesen o crea tu propia quedada.</p>
        </article>
    </div>
</section>
