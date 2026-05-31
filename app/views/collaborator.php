<?php /** Vista de la zona del colaborador: ficha del establecimiento y eventos asociados. */ ?>
<?php
// Si el colaborador todavía no ha creado la ficha, $colaborador será null
// y todos los campos se muestran vacíos en el formulario.
$nombre_local = isset($colaborador['nombre']) ? $colaborador['nombre'] : '';
$direccion_local = isset($colaborador['direccion']) ? $colaborador['direccion'] : '';
$ciudad_local = isset($colaborador['ciudad']) ? $colaborador['ciudad'] : '';
$descripcion_local = isset($colaborador['descripcion']) ? $colaborador['descripcion'] : '';
$lista_errores = isset($errores) ? $errores : [];
?>
<section class="section">
    <h1>Zona de colaborador</h1>
    <p class="muted">Gestiona los datos de tu establecimiento y crea eventos vinculados a él.</p>
</section>

<?php foreach ($lista_errores as $error): ?>
    <div class="alert alert--error"><?= escapar($error) ?></div>
<?php endforeach; ?>

<?php if ($colaborador): ?>
    <section class="profile-hero profile-compact u-mt-none u-mb-md">
        <div class="avatar avatar-xlarge"><?= escapar(iniciales($nombre_local, '')) ?></div>
        <div class="profile-body">
            <h2><?= escapar($nombre_local) ?></h2>
            <p class="profile-email"><?= escapar($descripcion_local !== '' ? $descripcion_local : 'Sin descripción definida.') ?></p>
            <div class="profile-meta">
                <span class="badge badge--colaborador">Colaborador</span>
                <span class="meta-pill">
                    <span class="meta-label">Dirección</span>
                    <strong><?= escapar($direccion_local) ?></strong>
                </span>
                <span class="meta-pill">
                    <span class="meta-label">Ciudad</span>
                    <strong><?= escapar($ciudad_local) ?></strong>
                </span>
            </div>
        </div>
    </section>
<?php endif; ?>

<div class="grid two">
    <section class="card">
        <h2><?= $colaborador ? 'Editar establecimiento' : 'Crear establecimiento' ?></h2>
        <p class="muted">Estos datos serán visibles cuando se asocien eventos al local.</p>
        <form method="post" class="form">
            <input type="hidden" name="accion" value="actualizar_colaborador">

            <div class="field">
                <label for="nombre">Nombre del local</label>
                <input id="nombre" type="text" name="nombre" required value="<?= escapar($nombre_local) ?>">
            </div>

            <div class="field">
                <label for="direccion">Dirección</label>
                <input id="direccion" type="text" name="direccion" required value="<?= escapar($direccion_local) ?>">
            </div>

            <div class="field">
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" type="text" name="ciudad" required value="<?= escapar($ciudad_local) ?>">
            </div>

            <div class="field">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="4" placeholder="Cuenta a los usuarios qué tipo de local es y qué ofreces..."><?= escapar($descripcion_local) ?></textarea>
            </div>

            <div class="form-actions">
                <button class="button" type="submit">Guardar establecimiento</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Crear evento del local</h2>
        <?php if (!$colaborador): ?>
            <p class="muted">Primero debes guardar los datos del establecimiento. Una vez guardes podrás crear eventos vinculados a tu local.</p>
        <?php else: ?>
            <p class="muted">El evento se asocia automáticamente a tu establecimiento.</p>
            <?php $mi_colaborador = $colaborador; ?>
            <form method="post" class="form">
                <input type="hidden" name="accion" value="crear_evento_colaborador">
                <?php require __DIR__ . '/event_form.php'; ?>
                <div class="form-actions">
                    <button class="button" type="submit">Crear evento</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<section class="section section-left">
    <h2>Eventos del establecimiento</h2>
    <?php if (!$eventos): ?>
        <div class="card">
            <p class="muted">Todavía no hay eventos asociados a este establecimiento.</p>
        </div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Fecha</th>
                        <th>Interés</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <tr>
                            <td><strong><?= escapar($evento['nombre']) ?></strong></td>
                            <td><?= escapar(formatear_fecha($evento['fecha_hora'])) ?></td>
                            <td><span class="tag tag-flush"><?= escapar($evento['interes']) ?></span></td>
                            <td><span class="badge badge--<?= escapar($evento['estado_evento']) ?>"><?= escapar(ucfirst($evento['estado_evento'])) ?></span></td>
                            <td><a class="button button-small button-secondary" href="<?= escapar(enlace('events', ['id_evento' => $evento['id_evento']])) ?>">Ver</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>