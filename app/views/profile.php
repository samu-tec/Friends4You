<?php /** Vista del perfil: edición de datos personales, intereses y contraseña. */ ?>
<?php
// Sacamos los valores del usuario a variables locales con valor por defecto,
// así las usamos varias veces en la vista sin tener que comprobar isset cada vez.
$nombre = isset($perfil['nombre']) ? $perfil['nombre'] : '';
$apellidos = isset($perfil['apellidos']) ? $perfil['apellidos'] : '';
$correo = isset($perfil['correo']) ? $perfil['correo'] : '';
$ciudad = isset($perfil['ciudad']) ? $perfil['ciudad'] : '';
$rol = isset($perfil['rol']) ? $perfil['rol'] : '';
$fecha_registro = isset($perfil['fecha_registro']) ? $perfil['fecha_registro'] : '';
?>
<section class="profile-hero">
    <div class="avatar avatar-xlarge"><?= escapar(iniciales($nombre, $apellidos)) ?></div>
    <div class="profile-body">
        <h1><?= escapar(trim($nombre . ' ' . $apellidos)) ?></h1>
        <p class="profile-email"><?= escapar($correo) ?></p>
        <div class="profile-meta">
            <span class="badge badge--<?= escapar($rol) ?>"><?= escapar(ucfirst($rol)) ?></span>
            <span class="meta-pill">
                <span class="meta-label">Ciudad</span>
                <strong><?= escapar($ciudad !== '' ? $ciudad : 'Sin definir') ?></strong>
            </span>
            <span class="meta-pill">
                <span class="meta-label">Registrado</span>
                <strong><?= escapar(formatear_fecha($fecha_registro)) ?></strong>
            </span>
        </div>
        <?php if (tiene_rol('usuario')): ?>
            <div class="stats-row">
                <div><strong><?= escapar($estadisticas['amigos']) ?></strong><span>Amigos</span></div>
                <div><strong><?= escapar($estadisticas['eventos_creados']) ?></strong><span>Eventos creados</span></div>
                <div><strong><?= escapar($estadisticas['eventos_asistidos']) ?></strong><span>Asistencias</span></div>
                <div><strong><?= escapar($estadisticas['intereses']) ?></strong><span>Intereses</span></div>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php $lista_errores = isset($errores) ? $errores : []; ?>
<?php foreach ($lista_errores as $error): ?>
    <div class="alert alert--error"><?= escapar($error) ?></div>
<?php endforeach; ?>

<div class="<?= tiene_rol('usuario') ? 'grid two' : '' ?>">
    <section class="card">
        <h2>Editar datos personales</h2>
        <p class="muted">Actualiza tu nombre, apellidos y ciudad.</p>
        <form method="post" class="form">
            <input type="hidden" name="accion" value="actualizar_perfil">

            <div class="field">
                <label for="nombre">Nombre</label>
                <input id="nombre" type="text" name="nombre" required value="<?= escapar($nombre) ?>">
            </div>

            <div class="field">
                <label for="apellidos">Apellidos</label>
                <input id="apellidos" type="text" name="apellidos" required value="<?= escapar($apellidos) ?>">
            </div>

            <div class="field">
                <label for="correo">Correo electrónico</label>
                <input id="correo" type="email" value="<?= escapar($correo) ?>" disabled>
                <small class="muted">El correo no se puede modificar.</small>
            </div>

            <div class="field">
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" type="text" name="ciudad" required value="<?= escapar($ciudad) ?>" placeholder="Ej. Almería">
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Guardar cambios</button>
            </div>
        </form>
    </section>

    <?php if (tiene_rol('usuario')): ?>
    <section class="card">
        <h2>Mis intereses</h2>
        <p class="muted">Marca los temas que te gustan para encontrar gente afín.</p>
        <form method="post" class="form">
            <input type="hidden" name="accion" value="actualizar_intereses">

            <div class="checkbox-list">
                <?php foreach ($intereses as $interes): ?>
                    <label>
                        <input type="checkbox" name="intereses[]" value="<?= escapar($interes['id_interes']) ?>"
                            <?= in_array((int) $interes['id_interes'], $intereses_seleccionados, true) ? 'checked' : '' ?>>
                        <?= escapar($interes['nombre']) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Guardar intereses</button>
            </div>
        </form>

        <form method="post" class="form u-mt-md">
            <input type="hidden" name="accion" value="crear_interes">
            <div class="field">
                <label for="nombre_interes">Crear nuevo interés</label>
                <input id="nombre_interes" type="text" name="nombre_interes" maxlength="100"
                       placeholder="Ej. fotografía, cocina, idiomas...">
            </div>
            <div class="form-actions">
                <button class="button button-secondary" type="submit">Añadir interés</button>
            </div>
        </form>
    </section>
    <?php endif; ?>
</div>

<section class="card card-full">
    <div class="grid two grid-center">
        <div>
            <h2>Cambiar contraseña</h2>
            <p class="muted">Por seguridad, introduce tu contraseña actual antes de cambiarla.</p>
            <form method="post" class="form">
                <input type="hidden" name="accion" value="cambiar_contrasena">

                <div class="field">
                    <label for="contrasena_actual">Contraseña actual</label>
                    <input id="contrasena_actual" type="password" name="contrasena_actual" required>
                </div>

                <div class="field">
                    <label for="contrasena_nueva">Nueva contraseña <small class="muted">(mínimo 8)</small></label>
                    <input id="contrasena_nueva" type="password" name="contrasena_nueva" minlength="8" required>
                </div>

                <div class="field">
                    <label for="contrasena_confirmar">Confirmar contraseña</label>
                    <input id="contrasena_confirmar" type="password" name="contrasena_confirmar" minlength="8" required>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Cambiar contraseña</button>
                </div>
            </form>
        </div>

        <div class="security-tips">
            <h3>Consejos de seguridad</h3>
            <ul class="help-steps">
                <li>Usa al menos 8 caracteres.</li>
                <li>Combina letras, números y algún símbolo.</li>
                <li>No reutilices la misma contraseña de otras webs.</li>
                <li>No la compartas con nadie.</li>
            </ul>
            <p class="muted">Tu contraseña se guarda cifrada: nadie, ni el administrador, puede verla.</p>
        </div>
    </div>
</section>
