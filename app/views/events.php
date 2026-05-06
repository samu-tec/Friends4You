<?php /** Vista de eventos: filtro por interés, creación, listado y detalle con asistencia. */ ?>
<section class="section">
    <h1>Eventos y quedadas</h1>
    <p class="muted">Crea eventos, apúntate a los que te interesen y descubre quedadas en tu ciudad.</p>

    <section class="toolbar">
        <form method="get" class="form form--inline" id="event-filter-form">
            <input type="hidden" name="page" value="events">
            <div class="field field--inline">
                <label for="filtro-interes">Filtrar por interés</label>
                <select id="filtro-interes" name="id_interes">
                    <option value="0">Todos los intereses</option>
                    <?php foreach ($interests as $interest): ?>
                        <option value="<?= e($interest['id_interes']) ?>" <?= selected_value($filterInterest, $interest['id_interes']) ?>>
                            <?= e($interest['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="button" type="submit">Filtrar</button>
        </form>
    </section>
</section>

<div class="grid two">
    <section class="card">
        <h2>Crear nuevo evento</h2>
        <p class="muted">Indica los datos básicos. Tus eventos aparecerán en la lista.</p>
        <form method="post" class="form">
            <input type="hidden" name="action" value="create_event">
            <?php require __DIR__ . '/partials/event_form_fields.php'; ?>
            <div class="form-actions">
                <button class="button" type="submit">Crear evento</button>
            </div>
        </form>
    </section>

    <section class="card">
        <h2>Eventos activos</h2>
        <p class="muted">Pulsa sobre un evento para ver detalles, asistentes y apuntarte.</p>
        <div id="js-event-list">
            <?php if (!$events): ?>
                <p class="muted">No hay eventos activos con ese filtro.</p>
            <?php else: ?>
                <ul class="event-list">
                    <?php foreach ($events as $event): ?>
                        <li>
                            <a href="<?= e(url('events', ['id_evento' => $event['id_evento']])) ?>">
                                <strong><?= e($event['nombre']) ?></strong>
                            </a>
                            <span><?= e(format_date($event['fecha_hora'])) ?> &middot; <span class="tag tag--flush"><?= e($event['interes']) ?></span></span>
                            <span class="muted"><?= e($event['punto_encuentro']) ?> &middot; <strong><?= e($event['asistentes']) ?></strong> asistentes</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>
</div>

<?php if ($selectedEvent): ?>
    <section class="detail">
        <div class="event-heading">
            <h2><?= e($selectedEvent['nombre']) ?></h2>
            <span class="badge badge--<?= e($selectedEvent['estado_evento']) ?>"><?= e(ucfirst($selectedEvent['estado_evento'])) ?></span>
        </div>

        <p><?= nl2br(e($selectedEvent['descripcion'] ?: 'Sin descripción.')) ?></p>

        <dl class="definition-list">
            <div><dt>Fecha</dt><dd><?= e(format_date($selectedEvent['fecha_hora'])) ?></dd></div>
            <div><dt>Punto de encuentro</dt><dd><?= e($selectedEvent['punto_encuentro']) ?></dd></div>
            <div><dt>Interés</dt><dd><span class="tag tag--flush"><?= e($selectedEvent['interes']) ?></span></dd></div>
            <div><dt>Creador</dt><dd><?= e($selectedEvent['creador_nombre'] . ' ' . $selectedEvent['creador_apellidos']) ?></dd></div>
            <div><dt>Colaborador</dt><dd><?= e($selectedEvent['colaborador_nombre'] ?: '— Sin colaborador —') ?></dd></div>
            <div><dt>Estado</dt><dd><span class="badge badge--<?= e($selectedEvent['estado_evento']) ?>"><?= e(ucfirst($selectedEvent['estado_evento'])) ?></span></dd></div>
        </dl>

        <div class="actions">
            <?php if ($selectedEvent['estado_evento'] === 'activo' && $attendanceStatus !== 'confirmada'): ?>
                <form method="post">
                    <input type="hidden" name="action" value="join_event">
                    <input type="hidden" name="id_evento" value="<?= e($selectedEvent['id_evento']) ?>">
                    <button class="button" type="submit">Apuntarme al evento</button>
                </form>
            <?php endif; ?>

            <?php if ($attendanceStatus === 'confirmada'): ?>
                <span class="badge badge--aceptada">Apuntado</span>
                <form method="post">
                    <input type="hidden" name="action" value="cancel_attendance">
                    <input type="hidden" name="id_evento" value="<?= e($selectedEvent['id_evento']) ?>">
                    <button class="button button--danger" type="submit">Cancelar asistencia</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="grid two u-mt-lg">
            <section class="card">
                <h3>Asistentes (<?= count($attendees) ?>)</h3>
                <?php if (!$attendees): ?>
                    <p class="muted">Aún no hay asistentes. ¡Sé el primero!</p>
                <?php else: ?>
                    <ul class="user-list">
                        <?php foreach ($attendees as $attendee): ?>
                            <li>
                                <div class="avatar"><?= e(initials($attendee['nombre'], $attendee['apellidos'])) ?></div>
                                <div class="user-list__info">
                                    <strong><?= e($attendee['nombre'] . ' ' . $attendee['apellidos']) ?></strong>
                                </div>
                                <span class="badge badge--<?= e($attendee['estado_asistencia']) ?>"><?= e(ucfirst($attendee['estado_asistencia'])) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>

            <?php if (can_edit_event($selectedEvent)): ?>
                <section class="card">
                    <h3>Modificar evento</h3>
                    <p class="muted">Eres el creador o administrador, puedes editar este evento.</p>
                    <?php
                        $eventFormData = $selectedEvent;
                        $eventFormData['fecha_hora_input'] = date('Y-m-d\TH:i', strtotime($selectedEvent['fecha_hora']));
                    ?>
                    <form method="post" class="form">
                        <input type="hidden" name="action" value="update_event">
                        <input type="hidden" name="id_evento" value="<?= e($selectedEvent['id_evento']) ?>">
                        <?php require __DIR__ . '/partials/event_form_fields.php'; ?>

                        <div class="field">
                            <label for="estado_evento">Estado</label>
                            <select id="estado_evento" name="estado_evento">
                                <option value="activo" <?= selected_value($selectedEvent['estado_evento'], 'activo') ?>>Activo</option>
                                <option value="cancelado" <?= selected_value($selectedEvent['estado_evento'], 'cancelado') ?>>Cancelado</option>
                                <option value="finalizado" <?= selected_value($selectedEvent['estado_evento'], 'finalizado') ?>>Finalizado</option>
                            </select>
                        </div>

                        <div class="form-actions">
                            <button class="button" type="submit">Guardar cambios</button>
                        </div>
                    </form>

                    <form method="post" class="form u-mt-sm" data-confirm="Vas a cancelar este evento. Esta acción es irreversible. ¿Estás SEGURO?">
                        <input type="hidden" name="action" value="cancel_event">
                        <input type="hidden" name="id_evento" value="<?= e($selectedEvent['id_evento']) ?>">
                        <button class="button button--danger" type="submit">Cancelar evento</button>
                    </form>
                </section>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>
