<?php /** Vista del perfil: edición de datos personales, intereses y contraseña. */ ?>
<section class="profile-hero">
    <div class="avatar avatar--xl"><?= e(initials($user['nombre'] ?? '', $user['apellidos'] ?? '')) ?></div>
    <div class="profile-hero__body">
        <h1><?= e(trim(($user['nombre'] ?? '') . ' ' . ($user['apellidos'] ?? ''))) ?></h1>
        <p class="profile-hero__email"><?= e($user['correo'] ?? '') ?></p>
        <div class="profile-hero__meta">
            <span class="badge badge--rol"><?= e(ucfirst($user['rol'] ?? '')) ?></span>
            <span class="meta-pill">
                <span class="meta-pill__label">Ciudad</span>
                <strong><?= e($user['ciudad'] ?? 'Sin definir') ?></strong>
            </span>
            <span class="meta-pill">
                <span class="meta-pill__label">Registrado</span>
                <strong><?= e(format_date($user['fecha_registro'] ?? null)) ?></strong>
            </span>
        </div>
        <div class="stats-row">
            <div><strong><?= e($stats['friends']) ?></strong><span>Amigos</span></div>
            <div><strong><?= e($stats['events_created']) ?></strong><span>Eventos creados</span></div>
            <div><strong><?= e($stats['events_joined']) ?></strong><span>Asistencias</span></div>
            <div><strong><?= e($stats['interests']) ?></strong><span>Intereses</span></div>
        </div>
    </div>
</section>

<?php foreach ($errors ?? [] as $error): ?>
    <div class="alert alert--error"><?= e($error) ?></div>
<?php endforeach; ?>

<div class="grid two">
    <section class="card">
        <h2>Editar datos personales</h2>
        <p class="muted">Actualiza tu nombre, apellidos y ciudad.</p>
        <form method="post" class="form">
            <input type="hidden" name="action" value="update_profile">

            <div class="field">
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" name="nombre" required value="<?= e($user['nombre'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="apellidos">Apellidos</label>
                <input id="apellidos" type="text" name="apellidos" required value="<?= e($user['apellidos'] ?? '') ?>">
            </div>

            <div class="field">
                <label for="correo">Correo electrónico</label>
                <input id="correo" type="email" value="<?= e($user['correo'] ?? '') ?>" disabled>
                <small class="muted">El correo no se puede modificar.</small>
            </div>

            <div class="field">
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" type="text" name="ciudad" required value="<?= e($user['ciudad'] ?? '') ?>" placeholder="Ej. Málaga">
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Mis intereses</h2>
        <p class="muted">Marca los temas que te gustan para encontrar gente afín.</p>
        <form method="post" class="form">
            <input type="hidden" name="action" value="update_interests">

            <div class="checkbox-list">
                <?php foreach ($interests as $interest): ?>
                    <label>
                        <input type="checkbox" name="intereses[]" value="<?= e($interest['id_interes']) ?>"
                            <?= in_array((int) $interest['id_interes'], $selectedInterests, true) ? 'checked' : '' ?>>
                        <?= e($interest['nombre']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Guardar intereses</button>
            </div>
        </form>

        <form method="post" class="form u-mt-md">
            <input type="hidden" name="action" value="create_interest">
            <div class="field">
                <label for="nombre_interes">Crear nuevo interés</label>
                <input id="nombre_interes" type="text" name="nombre_interes" maxlength="100"
                       placeholder="Ej. fotografía, cocina, idiomas...">
            </div>
            <div class="form-actions">
                <button class="button button--secondary" type="submit">Añadir interés</button>
            </div>
        </form>
    </section>
</div>

<section class="card card--full">
    <h2>Cambiar contraseña</h2>
    <p class="muted">Por seguridad, introduce tu contraseña actual antes de cambiarla.</p>
    <form method="post" class="form form--narrow">
        <input type="hidden" name="action" value="change_password">

        <div class="field">
            <label for="contrasena_actual">Contraseña actual</label>
            <input id="contrasena_actual" type="password" name="contrasena_actual" required autocomplete="current-password">
        </div>

        <div class="grid two">
            <div class="field">
                <label for="contrasena_nueva">Nueva contraseña <small class="muted">(mínimo 8)</small></label>
                <input id="contrasena_nueva" type="password" name="contrasena_nueva" minlength="8" required autocomplete="new-password">
            </div>

            <div class="field">
                <label for="contrasena_confirmar">Confirmar contraseña</label>
                <input id="contrasena_confirmar" type="password" name="contrasena_confirmar" minlength="8" required autocomplete="new-password">
            </div>
        </div>

        <div class="form-actions">
            <button class="button" type="submit">Cambiar contraseña</button>
        </div>
    </form>
</section>
