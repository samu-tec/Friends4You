<?php /** Vista de búsqueda de usuarios, gestión de solicitudes y consulta de perfil público. */ ?>
<section class="section">
    <h1>Buscar amistades</h1>
    <p class="muted">Encuentra usuarios por ciudad e intereses, envía solicitudes y gestiona las que recibas.</p>
</section>

<section class="card">
    <form method="get" class="form form-inline">
        <input type="hidden" name="page" value="users">

        <div class="field field-inline">
            <label for="ciudad">Ciudad</label>
            <input id="ciudad" type="text" name="ciudad" value="<?= escapar($ciudad_busqueda) ?>" placeholder="Ej. Almería">
        </div>

        <div class="field field-inline">
            <label for="id_interes">Interés</label>
            <select id="id_interes" name="id_interes">
                <option value="0">Todos los intereses</option>
                <?php foreach ($intereses as $interes): ?>
                    <option value="<?= escapar($interes['id_interes']) ?>" <?= valor_seleccionado($interes_busqueda, $interes['id_interes']) ?>>
                        <?= escapar($interes['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button class="button" type="submit">Buscar</button>
    </form>
</section>

<div class="grid two">
    <section class="card">
        <h2>Solicitudes recibidas <?php if ($solicitudes_pendientes): ?><span class="badge badge--pendiente"><?= count($solicitudes_pendientes) ?></span><?php endif; ?></h2>
        <?php if (!$solicitudes_pendientes): ?>
            <p class="muted">No tienes solicitudes pendientes.</p>
        <?php else: ?>
            <ul class="user-list">
                <?php foreach ($solicitudes_pendientes as $solicitud): ?>
                    <li>
                        <div class="avatar"><?= escapar(iniciales($solicitud['nombre'], $solicitud['apellidos'])) ?></div>
                        <div class="user-info">
                            <strong><?= escapar($solicitud['nombre'] . ' ' . $solicitud['apellidos']) ?></strong>
                            <span class="muted"><?= escapar($solicitud['ciudad']) ?></span>
                        </div>
                        <div class="user-actions">
                            <form method="post">
                                <input type="hidden" name="accion" value="responder_solicitud_amistad">
                                <input type="hidden" name="usuario_origen" value="<?= escapar($solicitud['usuario_origen']) ?>">
                                <input type="hidden" name="respuesta" value="aceptada">
                                <button class="button button-small" type="submit">Aceptar</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="accion" value="responder_solicitud_amistad">
                                <input type="hidden" name="usuario_origen" value="<?= escapar($solicitud['usuario_origen']) ?>">
                                <input type="hidden" name="respuesta" value="rechazada">
                                <button class="button button-small button-secondary" type="submit">Rechazar</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Mis amistades <?php if ($amigos): ?><span class="badge badge--aceptada"><?= count($amigos) ?></span><?php endif; ?></h2>
        <?php if (!$amigos): ?>
            <p class="muted">Todavía no tienes amistades aceptadas.</p>
        <?php else: ?>
            <ul class="user-list">
                <?php foreach ($amigos as $amigo): ?>
                    <li>
                        <div class="user-info">
                            <strong><?= escapar($amigo['nombre'] . ' ' . $amigo['apellidos']) ?></strong>
                            <span class="muted"><?= escapar($amigo['ciudad']) ?></span>
                        </div>
                        <div class="user-actions">
                            <a class="button button-small button-secondary" href="<?= escapar(enlace('users', ['id_usuario' => $amigo['id_usuario']])) ?>">Ver perfil</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="section section-left">
    <h2>Resultados de búsqueda</h2>
    <?php if (!$usuarios): ?>
        <div class="card">
            <p class="muted">No se han encontrado usuarios con esos filtros. Prueba a cambiar la ciudad o el interés.</p>
        </div>
    <?php else: ?>
        <div class="user-grid">
            <?php foreach ($usuarios as $usuario_encontrado): ?>
                <?php
                    // Si existe una amistad o solicitud con este usuario, cogemos
                    // su estado. Si no la hay, dejamos $estado a null.
                    $id_otro = (int) $usuario_encontrado['id_usuario'];
                    $estado = isset($estado_amistad[$id_otro]) ? $estado_amistad[$id_otro] : null;
                ?>
                <article class="user-card">
                    <div class="user-head">
                        <div class="avatar avatar-large"><?= escapar(iniciales($usuario_encontrado['nombre'], $usuario_encontrado['apellidos'])) ?></div>
                        <div>
                            <h3>
                                <a href="<?= escapar(enlace('users', ['id_usuario' => $usuario_encontrado['id_usuario']])) ?>">
                                    <?= escapar($usuario_encontrado['nombre'] . ' ' . $usuario_encontrado['apellidos']) ?>
                                </a>
                            </h3>
                            <p class="muted"><?= escapar($usuario_encontrado['ciudad']) ?: 'Sin ciudad' ?></p>
                        </div>
                    </div>
                    <p class="user-interests">
                        <?php if ($usuario_encontrado['intereses']): ?>
                            <?php foreach (explode(', ', $usuario_encontrado['intereses']) as $etiqueta): ?>
                                <span class="tag"><?= escapar($etiqueta) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="muted">Sin intereses definidos</span>
                        <?php endif; ?>
                    </p>
                    <div class="user-footer">
                        <?php if ($estado): ?>
                            <span class="badge badge--<?= escapar($estado['estado']) ?>"><?= escapar(ucfirst($estado['estado'])) ?></span>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="accion" value="enviar_solicitud_amistad">
                                <input type="hidden" name="id_usuario" value="<?= escapar($usuario_encontrado['id_usuario']) ?>">
                                <button class="button button-small" type="submit">Enviar solicitud</button>
                            </form>
                        <?php endif; ?>
                        <a class="button button-small button-secondary" href="<?= escapar(enlace('users', ['id_usuario' => $usuario_encontrado['id_usuario']])) ?>">Ver perfil</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($perfil_publico): ?>
    <section class="profile-hero profile-compact">
        <div class="avatar avatar-xlarge"><?= escapar(iniciales($perfil_publico['nombre'], $perfil_publico['apellidos'])) ?></div>
        <div class="profile-body">
            <h2>Perfil de <?= escapar($perfil_publico['nombre'] . ' ' . $perfil_publico['apellidos']) ?></h2>
            <p class="profile-email">Información pública del usuario</p>
            <div class="profile-meta">
                <span class="badge badge--<?= escapar($perfil_publico['rol']) ?>"><?= escapar(ucfirst($perfil_publico['rol'])) ?></span>
                <span class="meta-pill">
                    <span class="meta-label">Ciudad</span>
                    <strong><?= escapar($perfil_publico['ciudad']) ?: 'No definida' ?></strong>
                </span>
            </div>
            <div class="user-interests u-mt-sm">
                <?php if ($perfil_publico['intereses']): ?>
                    <?php foreach (explode(', ', $perfil_publico['intereses']) as $etiqueta): ?>
                        <span class="tag"><?= escapar($etiqueta) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="muted">Este usuario no ha definido intereses todavía.</span>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
