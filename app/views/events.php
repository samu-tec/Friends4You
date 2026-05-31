<?php /** Vista de eventos: filtro por interés, creación, listado y detalle con asistencia. */ ?>
<section class="section">
    <h1>Eventos y quedadas</h1>
    <p class="muted">Crea eventos, apúntate a los que te interesen y descubre quedadas en tu ciudad.</p>
</section>

<?php /* Crear eventos normales es solo del rol usuario (las personas).
   El colaborador los crea desde su página y el admin desde su panel. */ ?>
<?php $puede_crear_evento = tiene_rol('usuario'); ?>
<div class="<?= $puede_crear_evento ? 'grid two' : '' ?>">
    <?php if ($puede_crear_evento): ?>
    <section class="card">
        <h2>Crear nuevo evento</h2>
        <p class="muted">Indica los datos básicos. Tus eventos aparecerán en la lista.</p>
        <form method="post" class="form">
            <input type="hidden" name="accion" value="crear_evento">
            <?php require __DIR__ . '/event_form.php'; ?>
            <div class="form-actions">
                <button class="button" type="submit">Crear evento</button>
            </div>
        </form>
    </section>
    <?php endif; ?>

    <section class="card">
        <div class="card-head">
            <h2>Eventos activos</h2>
            <form method="get" class="filter-inline">
                <input type="hidden" name="page" value="events">
                <label for="filtro-interes">Interés</label>
                <select id="filtro-interes" name="id_interes">
                    <option value="0">Todos</option>
                    <?php foreach ($intereses as $interes): ?>
                        <option value="<?= escapar($interes['id_interes']) ?>" <?= valor_seleccionado($filtro_interes, $interes['id_interes']) ?>>
                            <?= escapar($interes['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
        <p class="muted">Pulsa sobre un evento para ver detalles, asistentes y apuntarte.</p>
        <div id="js-lista-eventos">
            <?php if (!$eventos): ?>
                <p class="muted">No hay eventos activos con ese filtro.</p>
            <?php else: ?>
                <ul class="event-list">
                    <?php foreach ($eventos as $evento): ?>
                        <li>
                            <a href="<?= escapar(enlace('events', ['id_evento' => $evento['id_evento']])) ?>">
                                <strong><?= escapar($evento['nombre']) ?></strong>
                            </a>
                            <span><?= escapar(formatear_fecha($evento['fecha_hora'])) ?> &middot; <span class="tag tag-flush"><?= escapar($evento['interes']) ?></span></span>
                            <span class="muted"><?= escapar($evento['punto_encuentro']) ?> &middot; <strong><?= escapar($evento['asistentes']) ?></strong> asistentes</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($evento_seleccionado): ?>
    <section class="detail">
        <div class="event-heading">
            <h2><?= escapar($evento_seleccionado['nombre']) ?></h2>
            <span class="badge badge--<?= escapar($evento_seleccionado['estado_evento']) ?>"><?= escapar(ucfirst($evento_seleccionado['estado_evento'])) ?></span>
        </div>

        <p><?= nl2br(escapar($evento_seleccionado['descripcion'] ?: 'Sin descripción.')) ?></p>

        <dl class="definition-list">
            <div><dt>Fecha</dt><dd><?= escapar(formatear_fecha($evento_seleccionado['fecha_hora'])) ?></dd></div>
            <div><dt>Punto de encuentro</dt><dd><?= escapar($evento_seleccionado['punto_encuentro']) ?></dd></div>
            <div><dt>Interés</dt><dd><span class="tag tag-flush"><?= escapar($evento_seleccionado['interes']) ?></span></dd></div>
            <div><dt>Creador</dt><dd><?= escapar($evento_seleccionado['creador_nombre'] . ' ' . $evento_seleccionado['creador_apellidos']) ?></dd></div>
            <div><dt>Colaborador</dt><dd><?= escapar($evento_seleccionado['colaborador_nombre'] ?: '— Sin colaborador —') ?></dd></div>
        </dl>

        <?php if (tiene_rol('usuario')): ?>
            <div class="actions">
                <?php if ($evento_seleccionado['estado_evento'] === 'activo' && $estado_asistencia !== 'confirmada'): ?>
                    <form method="post">
                        <input type="hidden" name="accion" value="apuntarse_evento">
                        <input type="hidden" name="id_evento" value="<?= escapar($evento_seleccionado['id_evento']) ?>">
                        <button class="button" type="submit">Apuntarme al evento</button>
                    </form>
                <?php endif; ?>

                <?php if ($estado_asistencia === 'confirmada'): ?>
                    <span class="badge badge--aceptada">Apuntado</span>
                    <form method="post">
                        <input type="hidden" name="accion" value="cancelar_asistencia">
                        <input type="hidden" name="id_evento" value="<?= escapar($evento_seleccionado['id_evento']) ?>">
                        <button class="button button-danger" type="submit">Cancelar asistencia</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="grid two u-mt-lg">
            <section class="card">
                <h3>Asistentes (<?= count($asistentes) ?>)</h3>
                <?php if (!$asistentes): ?>
                    <p class="muted">Aún no hay asistentes. ¡Sé el primero!</p>
                <?php else: ?>
                    <ul class="user-list">
                        <?php foreach ($asistentes as $asistente): ?>
                            <li>
                                <div class="avatar"><?= escapar(iniciales($asistente['nombre'], $asistente['apellidos'])) ?></div>
                                <div class="user-info">
                                    <strong><?= escapar($asistente['nombre'] . ' ' . $asistente['apellidos']) ?></strong>
                                </div>
                                <span class="badge badge--<?= escapar($asistente['estado_asistencia']) ?>"><?= escapar(ucfirst($asistente['estado_asistencia'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php if (puede_editar_evento($evento_seleccionado)): ?>
                <section class="card">
                    <h3>Modificar evento</h3>
                    <p class="muted">Eres el creador o administrador, puedes editar este evento.</p>
                    <?php
                        $datos_evento_formulario = $evento_seleccionado;
                        $datos_evento_formulario['fecha_hora_entrada'] = date('Y-m-d\TH:i', strtotime($evento_seleccionado['fecha_hora']));
                    ?>
                    <form method="post" class="form">
                        <input type="hidden" name="accion" value="actualizar_evento">
                        <input type="hidden" name="id_evento" value="<?= escapar($evento_seleccionado['id_evento']) ?>">
                        <?php require __DIR__ . '/event_form.php'; ?>

                        <div class="field">
                            <label for="estado_evento">Estado</label>
                            <select id="estado_evento" name="estado_evento">
                                <option value="activo" <?= valor_seleccionado($evento_seleccionado['estado_evento'], 'activo') ?>>Activo</option>
                                <option value="cancelado" <?= valor_seleccionado($evento_seleccionado['estado_evento'], 'cancelado') ?>>Cancelado</option>
                                <option value="finalizado" <?= valor_seleccionado($evento_seleccionado['estado_evento'], 'finalizado') ?>>Finalizado</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button class="button" type="submit">Guardar cambios</button>
                        </div>
                    </form>

                    <form method="post" class="form u-mt-sm" data-confirm="¿Eliminar el evento?">
                        <input type="hidden" name="accion" value="eliminar_evento">
                        <input type="hidden" name="id_evento" value="<?= escapar($evento_seleccionado['id_evento']) ?>">
                        <div class="form-actions">
                            <button class="button button-danger" type="submit">Eliminar evento</button>
                        </div>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
