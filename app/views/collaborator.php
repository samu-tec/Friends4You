<?php /** Vista de la zona del colaborador: ficha del establecimiento y eventos asociados. */ ?>
<section class="section">
    <h1>Zona de colaborador</h1>
    <p class="muted">Gestiona los datos de tu establecimiento y crea eventos vinculados a él.</p>

    <?php foreach ($errors ?? [] as $error): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <?php if ($collaborator): ?>
        <section class="profile-hero profile-hero--compact u-mt-none u-mb-md">
            <div class="avatar avatar--xl"><?= e(initials($collaborator['nombre'], '')) ?></div>
            <div class="profile-hero__body">
                <h2><?= e($collaborator['nombre']) ?></h2>
                <p class="profile-hero__email"><?= e($collaborator['descripcion'] ?: 'Sin descripción definida.') ?></p>
                <div class="profile-hero__meta">
                    <span class="badge badge--colaborador">Colaborador</span>
                    <span class="meta-pill">
                        <span class="meta-pill__label">Dirección</span>
                        <strong><?= e($collaborator['direccion']) ?></strong>
                    </span>
                    <span class="meta-pill">
                        <span class="meta-pill__label">Ciudad</span>
                        <strong><?= e($collaborator['ciudad']) ?></strong>
                    </span>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <div class="grid two">
        <section class="card">
            <h2><?= $collaborator ? 'Editar establecimiento' : 'Crear establecimiento' ?></h2>
            <p class="muted">Estos datos serán visibles cuando se asocien eventos al local.</p>
            <form method="post" class="form">
                <input type="hidden" name="action" value="update_collaborator">

                <div class="field">
                    <label for="nombre">Nombre del local</label>
                    <input id="nombre" type="text" name="nombre" required value="<?= e($collaborator['nombre'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="direccion">Dirección</label>
                    <input id="direccion" type="text" name="direccion" required value="<?= e($collaborator['direccion'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="ciudad">Ciudad</label>
                    <input id="ciudad" type="text" name="ciudad" required value="<?= e($collaborator['ciudad'] ?? '') ?>">
                </div>

                <div class="field">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4" placeholder="Cuenta a los usuarios qué tipo de local es y qué ofreces..."><?= e($collaborator['descripcion'] ?? '') ?></textarea>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Guardar establecimiento</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Crear evento del local</h2>
            <?php if (!$collaborator): ?>
                <p class="muted">Primero debes guardar los datos del establecimiento. Una vez guardes podrás crear eventos vinculados a tu local.</p>
            <?php else: ?>
                <p class="muted">El evento se asocia automáticamente a tu establecimiento.</p>
                <?php
                    $myCollaborator = $collaborator;
                    $collaborators = [$collaborator];
                    $eventFormData = [];
                ?>
                <form method="post" class="form">
                    <input type="hidden" name="action" value="create_collaborator_event">
                    <?php require __DIR__ . '/partials/event_form_fields.php'; ?>
                    <div class="form-actions">
                        <button class="button" type="submit">Crear evento</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </div>

    <section class="section">
        <h2>Eventos del establecimiento</h2>
        <?php if (!$events): ?>
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
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                            <tr>
                                <td><strong><?= e($event['nombre']) ?></strong></td>
                                <td><?= e(format_date($event['fecha_hora'])) ?></td>
                                <td><span class="tag tag--flush"><?= e($event['interes']) ?></span></td>
                                <td><span class="badge badge--<?= e($event['estado_evento']) ?>"><?= e(ucfirst($event['estado_evento'])) ?></span></td>
                                <td><a class="button button--small button--secondary" href="<?= e(url('events', ['id_evento' => $event['id_evento']])) ?>">Ver</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</section>
