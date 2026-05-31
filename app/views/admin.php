<?php /** Vista del panel de administración: gestión de usuarios, intereses, colaboradores y eventos. */ ?>
<section class="section">
    <h1>Panel de administración</h1>
    <p class="muted">Gestión de usuarios, intereses, colaboradores y eventos del sistema.</p>

    <div class="stats-grid u-mt-md">
        <article class="stat">
            <strong><?= count($usuarios) ?></strong>
            <span>Usuarios</span>
        </article>
        <article class="stat">
            <strong><?= count($intereses) ?></strong>
            <span>Intereses</span>
        </article>
        <article class="stat">
            <strong><?= count($colaboradores) ?></strong>
            <span>Colaboradores</span>
        </article>
        <article class="stat">
            <strong><?= count($eventos) ?></strong>
            <span>Eventos</span>
        </article>
    </div>
</section>

<section class="section section-left">
    <h2>Gestión de usuarios</h2>
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
                <?php foreach ($usuarios as $usuario_admin): ?>
                    <?php
                        // En HTML un <form> no puede abarcar varias celdas <td>, así
                        // que ponemos un form vacío con id único en la primera celda y
                        // cada input de las demás celdas se asocia a él con el
                        // atributo HTML5 form="id". Así enviamos los cuatro campos
                        // (nombre, apellidos, ciudad, rol) en una sola petición.
                        $id_formulario_usuario = 'user-form-' . $usuario_admin['id_usuario'];
                    ?>
                    <tr>
                        <td>
                            <form id="<?= escapar($id_formulario_usuario) ?>" method="post">
                                <input type="hidden" name="accion" value="admin_actualizar_usuario">
                                <input type="hidden" name="id_usuario" value="<?= escapar($usuario_admin['id_usuario']) ?>">
                            </form>
                            <div class="entity-inline">
                                <div class="avatar"><?= escapar(iniciales($usuario_admin['nombre'], $usuario_admin['apellidos'])) ?></div>
                                <span class="muted">#<?= escapar($usuario_admin['id_usuario']) ?></span>
                            </div>
                        </td>
                        <td><?= escapar($usuario_admin['correo']) ?></td>
                        <td><input form="<?= escapar($id_formulario_usuario) ?>" type="text" name="nombre" value="<?= escapar($usuario_admin['nombre']) ?>" required></td>
                        <td><input form="<?= escapar($id_formulario_usuario) ?>" type="text" name="apellidos" value="<?= escapar($usuario_admin['apellidos']) ?>" required></td>
                        <td><input form="<?= escapar($id_formulario_usuario) ?>" type="text" name="ciudad" value="<?= escapar($usuario_admin['ciudad']) ?>" required></td>
                        <td>
                            <select form="<?= escapar($id_formulario_usuario) ?>" name="id_rol">
                                <?php foreach ($roles as $rol): ?>
                                    <option value="<?= escapar($rol['id_rol']) ?>" <?= valor_seleccionado($usuario_admin['id_rol'], $rol['id_rol']) ?>>
                                        <?= escapar(ucfirst($rol['nombre'])) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td class="actions-cell">
                            <button form="<?= escapar($id_formulario_usuario) ?>" class="button button-small" type="submit">Guardar</button>
                            <form method="post" data-confirm="¿Eliminar el usuario y todos sus datos?">
                                <input type="hidden" name="accion" value="admin_eliminar_usuario">
                                <input type="hidden" name="id_usuario" value="<?= escapar($usuario_admin['id_usuario']) ?>">
                                <button class="button button-small button-danger" type="submit">Eliminar</button>
                            </form>
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
        <form method="post" class="form form-inline u-mb-md">
            <input type="hidden" name="accion" value="admin_agregar_interes">
            <div class="field field-inline">
                <input type="text" name="nombre" placeholder="Nuevo interés" required maxlength="100">
            </div>
            <button class="button" type="submit">Añadir</button>
        </form>
        <?php if (!$intereses): ?>
            <p class="muted">No hay intereses creados.</p>
        <?php else: ?>
            <div class="chip-list">
                <?php foreach ($intereses as $interes): ?>
                    <span class="chip">
                        <?= escapar($interes['nombre']) ?>
                        <form method="post" data-confirm="¿Eliminar el interés «<?= escapar($interes['nombre']) ?>»?">
                            <input type="hidden" name="accion" value="admin_eliminar_interes">
                            <input type="hidden" name="id_interes" value="<?= escapar($interes['id_interes']) ?>">
                            <button type="submit" class="chip-close" aria-label="Eliminar <?= escapar($interes['nombre']) ?>">&times;</button>
                        </form>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Nuevo colaborador</h2>
        <?php if (!$usuarios_sin_colaborador): ?>
            <p class="muted">No hay cuentas con rol colaborador sin establecimiento. Cambia el rol de un usuario a «colaborador» antes de crear su establecimiento.</p>
        <?php else: ?>
            <p class="muted">Crea el establecimiento de una cuenta colaboradora.</p>
            <form method="post" class="form">
                <input type="hidden" name="accion" value="admin_crear_colaborador">

                <div class="field">
                    <label for="id_usuario_colaborador">Cuenta de usuario</label>
                    <select id="id_usuario_colaborador" name="id_usuario_colaborador" required>
                        <?php foreach ($usuarios_sin_colaborador as $candidato): ?>
                            <option value="<?= escapar($candidato['id_usuario']) ?>">
                                <?= escapar($candidato['nombre'] . ' ' . $candidato['apellidos'] . ' · ' . $candidato['correo']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="grid two">
                    <div class="field">
                        <label for="colaborador-nombre">Nombre del local</label>
                        <input id="colaborador-nombre" type="text" name="nombre" required>
                    </div>

                    <div class="field">
                        <label for="colaborador-ciudad">Ciudad</label>
                        <input id="colaborador-ciudad" type="text" name="ciudad" required>
                    </div>
                </div>

                <div class="field">
                    <label for="colaborador-direccion">Dirección</label>
                    <input id="colaborador-direccion" type="text" name="direccion" required>
                </div>

                <div class="field">
                    <label for="colaborador-descripcion">Descripción</label>
                    <textarea id="colaborador-descripcion" name="descripcion" rows="3"></textarea>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Crear colaborador</button>
                </div>
            </form>
        <?php endif; ?>
    </section>
</div>

<section class="section section-left">
    <h2>Gestión de colaboradores</h2>
    <?php if (!$colaboradores): ?>
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
                    <?php foreach ($colaboradores as $colaborador): ?>
                        <?php
                            // Mismo patrón que la tabla de usuarios: un <form> vacío
                            // en la primera celda y los inputs de las otras celdas
                            // se asocian a él con el atributo HTML5 form="id".
                            $id_formulario_colaborador = 'collaborator-form-' . $colaborador['id_colaborador'];
                        ?>
                        <tr>
                            <td>
                                <form id="<?= escapar($id_formulario_colaborador) ?>" method="post">
                                    <input type="hidden" name="accion" value="admin_actualizar_colaborador">
                                    <input type="hidden" name="id_colaborador" value="<?= escapar($colaborador['id_colaborador']) ?>">
                                </form>
                                <div class="entity-inline">
                                    <div class="avatar"><?= escapar(iniciales($colaborador['nombre'], '')) ?></div>
                                    <div>
                                        <span class="muted">#<?= escapar($colaborador['id_colaborador']) ?></span><br>
                                        <small><?= escapar($colaborador['correo']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><input form="<?= escapar($id_formulario_colaborador) ?>" type="text" name="nombre" value="<?= escapar($colaborador['nombre']) ?>" required></td>
                            <td><input form="<?= escapar($id_formulario_colaborador) ?>" type="text" name="direccion" value="<?= escapar($colaborador['direccion']) ?>" required></td>
                            <td><input form="<?= escapar($id_formulario_colaborador) ?>" type="text" name="ciudad" value="<?= escapar($colaborador['ciudad']) ?>" required></td>
                            <td><textarea form="<?= escapar($id_formulario_colaborador) ?>" name="descripcion" rows="2"><?= escapar($colaborador['descripcion']) ?></textarea></td>
                            <td class="actions-cell">
                                <button form="<?= escapar($id_formulario_colaborador) ?>" class="button button-small" type="submit">Guardar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section">
    <h2>Crear evento</h2>
    <p class="muted">Como administrador puedes crear un evento; se gestiona y se elimina desde aquí.</p>
</section>

<section class="card">
    <form method="post" class="form">
        <input type="hidden" name="accion" value="admin_crear_evento">
        <?php require __DIR__ . '/event_form.php'; ?>
        <div class="form-actions">
            <button class="button" type="submit">Crear evento</button>
        </div>
    </form>
</section>

<section class="section section-left">
    <h2>Gestión de eventos</h2>
    <?php if (!$eventos): ?>
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
                        <th>Cambiar estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($eventos as $evento): ?>
                        <?php
                            // Mismo patrón que usuarios y colaboradores: el <form> que
                            // cambia el estado está en una celda y su botón Guardar
                            // se asocia con form="id" desde la celda de acciones,
                            // para que los tres botones (Ver / Guardar / Eliminar)
                            // queden juntos en la columna de la derecha.
                            $id_formulario_evento = 'event-form-' . $evento['id_evento'];
                        ?>
                        <tr>
                            <td><strong><?= escapar($evento['nombre']) ?></strong></td>
                            <td><small><?= escapar($evento['creador_correo']) ?></small></td>
                            <td><?= escapar(formatear_fecha($evento['fecha_hora'])) ?></td>
                            <td><span class="tag tag-flush"><?= escapar($evento['interes']) ?></span></td>
                            <td><?= escapar($evento['colaborador_nombre'] ?: '—') ?></td>
                            <td>
                                <form id="<?= escapar($id_formulario_evento) ?>" method="post">
                                    <input type="hidden" name="accion" value="admin_actualizar_estado_evento">
                                    <input type="hidden" name="id_evento" value="<?= escapar($evento['id_evento']) ?>">
                                    <select name="estado_evento">
                                        <option value="activo" <?= valor_seleccionado($evento['estado_evento'], 'activo') ?>>Activo</option>
                                        <option value="cancelado" <?= valor_seleccionado($evento['estado_evento'], 'cancelado') ?>>Cancelado</option>
                                        <option value="finalizado" <?= valor_seleccionado($evento['estado_evento'], 'finalizado') ?>>Finalizado</option>
                                    </select>
                                </form>
                            </td>
                            <td class="actions-cell">
                                <a class="button button-small button-secondary" href="<?= escapar(enlace('events', ['id_evento' => $evento['id_evento']])) ?>">Ver</a>
                                <button form="<?= escapar($id_formulario_evento) ?>" class="button button-small" type="submit">Guardar</button>
                                <form method="post"
                                      data-confirm="¿Eliminar el evento «<?= escapar($evento['nombre']) ?>»?">
                                    <input type="hidden" name="accion" value="admin_eliminar_evento">
                                    <input type="hidden" name="id_evento" value="<?= escapar($evento['id_evento']) ?>">
                                    <button class="button button-small button-danger" type="submit">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="section">
    <h2>Informe</h2>
    <p class="muted">Resumen de la actividad. Asistencias confirmadas en total: <strong><?= escapar($total_asistencias_confirmadas) ?></strong>.</p>
</section>

<div class="grid two">
    <section class="card">
        <h2>Eventos por interés</h2>
        <p class="muted">Intereses que tienen al menos un evento creado.</p>
        <?php
            $tiene_eventos = false;
            foreach ($eventos_por_interes as $fila) {
                if ((int) $fila['total'] > 0) {
                    $tiene_eventos = true;
                }
            }
        ?>
        <?php if (!$tiene_eventos): ?>
            <p class="muted">Todavía no hay eventos asignados a ningún interés.</p>
        <?php else: ?>
            <ul class="rank-list">
                <?php foreach ($eventos_por_interes as $fila): ?>
                    <?php if ((int) $fila['total'] === 0) { continue; } ?>
                    <li>
                        <span><?= escapar($fila['nombre']) ?></span>
                        <strong><?= escapar($fila['total']) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Usuarios por rol</h2>
        <p class="muted">Cuántos usuarios hay en cada rol del sistema.</p>
        <?php if (!$usuarios_por_rol): ?>
            <p class="muted">Sin datos disponibles.</p>
        <?php else: ?>
            <ul class="rank-list">
                <?php foreach ($usuarios_por_rol as $fila): ?>
                    <li>
                        <span class="badge badge--<?= escapar($fila['nombre']) ?>"><?= escapar(ucfirst($fila['nombre'])) ?></span>
                        <strong><?= escapar($fila['total']) ?></strong>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
