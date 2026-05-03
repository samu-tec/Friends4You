<?php /** Vista de búsqueda de usuarios, gestión de solicitudes y consulta de perfil público. */ ?>
<section class="section">
    <h1>Buscar amistades</h1>
    <p class="muted">Encuentra usuarios por ciudad e intereses, envía solicitudes y gestiona las que recibas.</p>

    <section class="toolbar">
        <form method="get" class="form form--inline">
            <input type="hidden" name="page" value="users">

            <div class="field field--inline">
                <label for="ciudad">Ciudad</label>
                <input id="ciudad" type="text" name="ciudad" value="<?= e($searchCity) ?>" placeholder="Ej. Málaga">
            </div>

            <div class="field field--inline">
                <label for="id_interes">Interés</label>
                <select id="id_interes" name="id_interes">
                    <option value="0">Todos los intereses</option>
                    <?php foreach ($interests as $interest): ?>
                        <option value="<?= e($interest['id_interes']) ?>" <?= selected_value($searchInterest, $interest['id_interes']) ?>>
                            <?= e($interest['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="button" type="submit">Buscar</button>
        </form>
    </section>
</section>

<div class="grid two">
    <section class="card">
        <h2>Solicitudes recibidas <?php if ($pendingRequests): ?><span class="badge badge--pendiente"><?= count($pendingRequests) ?></span><?php endif; ?></h2>
        <?php if (!$pendingRequests): ?>
            <p class="muted">No tienes solicitudes pendientes.</p>
        <?php else: ?>
            <ul class="user-list">
                <?php foreach ($pendingRequests as $request): ?>
                    <li>
                        <div class="avatar"><?= e(initials($request['nombre'], $request['apellidos'])) ?></div>
                        <div class="user-list__info">
                            <strong><?= e($request['nombre'] . ' ' . $request['apellidos']) ?></strong>
                            <span class="muted"><?= e($request['ciudad']) ?></span>
                        </div>
                        <div class="user-list__actions">
                            <form method="post">
                                <input type="hidden" name="action" value="answer_friend_request">
                                <input type="hidden" name="usuario_origen" value="<?= e($request['usuario_origen']) ?>">
                                <input type="hidden" name="respuesta" value="aceptada">
                                <button class="button button--small" type="submit">Aceptar</button>
                            </form>
                            <form method="post">
                                <input type="hidden" name="action" value="answer_friend_request">
                                <input type="hidden" name="usuario_origen" value="<?= e($request['usuario_origen']) ?>">
                                <input type="hidden" name="respuesta" value="rechazada">
                                <button class="button button--small button--secondary" type="submit">Rechazar</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Mis amistades <?php if ($friends): ?><span class="badge badge--aceptada"><?= count($friends) ?></span><?php endif; ?></h2>
        <?php if (!$friends): ?>
            <p class="muted">Todavía no tienes amistades aceptadas.</p>
        <?php else: ?>
            <ul class="user-list">
                <?php foreach ($friends as $friend): ?>
                    <li>
                        <div class="user-list__info">
                            <strong><?= e($friend['nombre'] . ' ' . $friend['apellidos']) ?></strong>
                            <span class="muted"><?= e($friend['ciudad']) ?></span>
                        </div>
                        <div class="user-list__actions">
                            <a class="button button--small button--secondary" href="<?= e(url('users', ['id_usuario' => $friend['id_usuario']])) ?>">Ver perfil</a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>

<section class="section">
    <h2>Resultados de búsqueda</h2>
    <?php if (!$users): ?>
        <div class="card">
            <p class="muted">No se han encontrado usuarios con esos filtros. Prueba a cambiar la ciudad o el interés.</p>
        </div>
    <?php else: ?>
        <div class="user-grid">
            <?php foreach ($users as $foundUser): ?>
                <?php $status = $friendshipStatus[(int) $foundUser['id_usuario']] ?? null; ?>
                <article class="user-card">
                    <div class="user-card__head">
                        <div class="avatar avatar--lg"><?= e(initials($foundUser['nombre'], $foundUser['apellidos'])) ?></div>
                        <div>
                            <h3>
                                <a href="<?= e(url('users', ['id_usuario' => $foundUser['id_usuario']])) ?>">
                                    <?= e($foundUser['nombre'] . ' ' . $foundUser['apellidos']) ?>
                                </a>
                            </h3>
                            <p class="muted"><?= e($foundUser['ciudad']) ?: 'Sin ciudad' ?></p>
                        </div>
                    </div>
                    <p class="user-card__interests">
                        <?php if ($foundUser['intereses']): ?>
                            <?php foreach (explode(', ', $foundUser['intereses']) as $tag): ?>
                                <span class="tag"><?= e($tag) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="muted">Sin intereses definidos</span>
                        <?php endif; ?>
                    </p>
                    <div class="user-card__footer">
                        <?php if ($status): ?>
                            <span class="badge badge--<?= e($status['estado']) ?>"><?= e(ucfirst($status['estado'])) ?></span>
                        <?php else: ?>
                            <form method="post">
                                <input type="hidden" name="action" value="send_friend_request">
                                <input type="hidden" name="id_usuario" value="<?= e($foundUser['id_usuario']) ?>">
                                <button class="button button--small" type="submit">Enviar solicitud</button>
                            </form>
                        <?php endif; ?>
                        <a class="button button--small button--secondary" href="<?= e(url('users', ['id_usuario' => $foundUser['id_usuario']])) ?>">Ver perfil</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php if ($publicProfile): ?>
    <section class="profile-hero profile-hero--compact">
        <div class="avatar avatar--xl"><?= e(initials($publicProfile['nombre'], $publicProfile['apellidos'])) ?></div>
        <div class="profile-hero__body">
            <h2>Perfil de <?= e($publicProfile['nombre'] . ' ' . $publicProfile['apellidos']) ?></h2>
            <p class="profile-hero__email">Información pública del usuario</p>
            <div class="profile-hero__meta">
                <span class="badge badge--rol"><?= e(ucfirst($publicProfile['rol'])) ?></span>
                <span class="meta-pill">
                    <span class="meta-pill__label">Ciudad</span>
                    <strong><?= e($publicProfile['ciudad']) ?: 'No definida' ?></strong>
                </span>
            </div>
            <div class="user-card__interests u-mt-sm">
                <?php if ($publicProfile['intereses']): ?>
                    <?php foreach (explode(', ', $publicProfile['intereses']) as $tag): ?>
                        <span class="tag"><?= e($tag) ?></span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <span class="muted">Este usuario no ha definido intereses todavía.</span>
                <?php endif; ?>
            </div>
        </div>
    </section>
<?php endif; ?>
