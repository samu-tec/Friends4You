<?php /** Vista del panel de administración: gestión de usuarios, intereses, colaboradores y eventos. */ ?>
<section class="section">
    <h1>Panel de administración</h1>
    <p class="muted">Gestión de usuarios, intereses, colaboradores y eventos del sistema.</p>

    <div class="stats-grid u-mt-md">
        <article class="stat">
            <span>Usuarios</span>
            <strong><?= count($users) ?></strong>
        </article>
        <article class="stat">
            <span>Intereses</span>
            <strong><?= count($interests) ?></strong>
        </article>
        <article class="stat">
            <span>Colaboradores</span>
            <strong><?= count($collaborators) ?></strong>
        </article>
        <article class="stat">
            <span>Eventos</span>
            <strong><?= count($events) ?></strong>
        </article>
    </div>
</section>

<section class="section">
    <h2>Gestión de usuarios</h2>
    <p class="muted">Modifica los datos de los usuarios y cambia su rol cuando sea necesario.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th>Nombre</th>
                    <th>Apellidos</th>
                    <th>Ciudad</th>
                    <th>Rol</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $adminUser): ?>
                    <?php $userFormId = 'user-form-' . $adminUser['id_usuario']; ?>
                    <tr>
                        <td>
                            <form id="<?= e($userFormId) ?>" method="post">
                                <input type="hidden" name="action" value="admin_update_user">
                                <input type="hidden" name="id_usuario" value="<?= e($adminUser['id_usuario']) ?>">
                            </form>
                            <div class="entity-inline">
                                <div class="avatar"><?= e(initials($adminUser['nombre'], $adminUser['apellidos'])) ?></div>
                                <span class="muted">#<?= e($adminUser['id_usuario']) ?></span>
                            </div>
                        </td>
                        <td><?= e($adminUser['correo']) ?></td>
                        <td><input form="<?= e($userFormId) ?>" type="text" name="nombre" value="<?= e($adminUser['nombre']) ?>" required></td>
                        <td><input form="<?= e($userFormId) ?>" type="text" name="apellidos" value="<?= e($adminUser['apellidos']) ?>" required></td>
                        <td><input form="<?= e($userFormId) ?>" type="text" name="ciudad" value="<?= e($adminUser['ciudad']) ?>" required></td>
                        <td>
                            <select form="<?= e($userFormId) ?>" name="id_rol">
                                <?php foreach ($roles as $role): ?>
                                    <option value="<?= e($role['id_rol']) ?>" <?= selected_value($adminUser['id_rol'], $role['id_rol']) ?>>
                                        <?= e(ucfirst($role['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td>
                            <button form="<?= e($userFormId) ?>" class="button button--small" type="submit">Guardar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<div class="grid two">
    <section class="card">
        <h2>Intereses</h2>
        <p class="muted">Categorías que los usuarios pueden seleccionar en su perfil.</p>
        <form method="post" class="form form--inline u-mb-md">
            <input type="hidden" name="action" value="admin_add_interest">
            <div class="field field--inline">
                <input type="text" name="nombre" placeholder="Nuevo interés" required maxlength="100">
            </div>
            <button class="button" type="submit">Añadir</button>
        </form>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Interés</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($interests as $interest): ?>
                        <tr>
                            <td><span class="tag tag--flush"><?= e($interest['nombre']) ?></span></td>
                            <td>
                                <form method="post" data-confirm="Vas a eliminar el interés «<?= e($interest['nombre']) ?>». Esta acción es irreversible. ¿Estás SEGURO?">
                                    <input type="hidden" name="action" value="admin_delete_interest">
                                    <input type="hidden" name="id_interes" value="<?= e($interest['id_interes']) ?>">
                                    <button class="button button--small button--danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$interests): ?>
                        <tr><td colspan="2" class="muted">No hay intereses creados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="card">
        <h2>Nuevo colaborador</h2>
        <?php if (!$eligibleCollaboratorUsers): ?>
            <p class="muted">No hay cuentas con rol colaborador sin establecimiento. Cambia el rol de un usuario a «colaborador» antes de crear su establecimiento.</p>
        <?php else: ?>
            <p class="muted">Crea el establecimiento de una cuenta colaboradora.</p>
            <form method="post" class="form">
                <input type="hidden" name="action" value="admin_create_collaborator">

                <div class="field">
                    <label for="id_usuario_colaborador">Cuenta de usuario</label>
                    <select id="id_usuario_colaborador" name="id_usuario_colaborador" required>
                        <?php foreach ($eligibleCollaboratorUsers as $candidate): ?>
                            <option value="<?= e($candidate['id_usuario']) ?>">
                                <?= e($candidate['nombre'] . ' ' . $candidate['apellidos'] . ' · ' . $candidate['correo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid two">
                    <div class="field">
                        <label for="col-nombre">Nombre del local</label>
                        <input id="col-nombre" type="text" name="nombre" required>
                    </div>

                    <div class="field">
                        <label for="col-ciudad">Ciudad</label>
                        <input id="col-ciudad" type="text" name="ciudad" required>
                    </div>
                </div>

                <div class="field">
                    <label for="col-direccion">Dirección</label>
                    <input id="col-direccion" type="text" name="direccion" required>
                </div>

                <div class="field">
                    <label for="col-descripcion">Descripción</label>
                    <textarea id="col-descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Crear colaborador</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<section class="section">
    <h2>Gestión de colaboradores</h2>
    <?php if (!$collaborators): ?>
        <div class="card"><p class="muted">No hay colaboradores creados.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Cuenta</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Ciudad</th>
                        <th>Descripción</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($collaborators as $collaborator): ?>
                        <?php $collaboratorFormId = 'collaborator-form-' . $collaborator['id_colaborador']; ?>
                        <tr>
                            <td>
                                <form id="<?= e($collaboratorFormId) ?>" method="post">
                                    <input type="hidden" name="action" value="admin_update_collaborator">
                                    <input type="hidden" name="id_colaborador" value="<?= e($collaborator['id_colaborador']) ?>">
                                </form>
                                <small><?= e($collaborator['correo']) ?></small>
                            </td>
                            <td><input form="<?= e($collaboratorFormId) ?>" type="text" name="nombre" value="<?= e($collaborator['nombre']) ?>" required></td>
                            <td><input form="<?= e($collaboratorFormId) ?>" type="text" name="direccion" value="<?= e($collaborator['direccion']) ?>" required></td>
                            <td><input form="<?= e($collaboratorFormId) ?>" type="text" name="ciudad" value="<?= e($collaborator['ciudad']) ?>" required></td>
                            <td><textarea form="<?= e($collaboratorFormId) ?>" name="descripcion" rows="2"><?= e($collaborator['descripcion']) ?></textarea></td>
                            <td>
                                <button form="<?= e($collaboratorFormId) ?>" class="button button--small" type="submit">Guardar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section">
    <h2>Gestión de eventos</h2>
    <p class="muted">Cambia el estado de cualquier evento (activo / cancelado / finalizado).</p>
    <?php if (!$events): ?>
        <div class="card"><p class="muted">No hay eventos creados.</p></div>
    <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Evento</th>
                        <th>Creador</th>
                        <th>Fecha</th>
                        <th>Interés</th>
                        <th>Colaborador</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <?php $eventFormId = 'event-form-' . $event['id_evento']; ?>
                        <tr>
                            <td>
                                <form id="<?= e($eventFormId) ?>" method="post">
                                    <input type="hidden" name="action" value="admin_update_event_status">
                                    <input type="hidden" name="id_evento" value="<?= e($event['id_evento']) ?>">
                                </form>
                                <strong><?= e($event['nombre']) ?></strong>
                            </td>
                            <td><small><?= e($event['creador_correo']) ?></small></td>
                            <td><?= e(format_date($event['fecha_hora'])) ?></td>
                            <td><span class="tag tag--flush"><?= e($event['interes']) ?></span></td>
                            <td><?= e($event['colaborador_nombre'] ?: '—') ?></td>
                            <td>
                                <select form="<?= e($eventFormId) ?>" name="estado_evento">
                                    <option value="activo" <?= selected_value($event['estado_evento'], 'activo') ?>>Activo</option>
                                    <option value="cancelado" <?= selected_value($event['estado_evento'], 'cancelado') ?>>Cancelado</option>
                                    <option value="finalizado" <?= selected_value($event['estado_evento'], 'finalizado') ?>>Finalizado</option>
                                </select>
                            </td>
                            <td class="actions-cell">
                                <button form="<?= e($eventFormId) ?>" class="button button--small" type="submit">Guardar</button>
                                <a class="button button--small button--secondary" href="<?= e(url('events', ['id_evento' => $event['id_evento']])) ?>">Ver</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
