<?php /** Vista del informe estadístico: totales y agrupaciones (eventos por interés, usuarios por rol). */ ?>
<section class="section">
    <h1>Informe estadístico</h1>
    <p class="muted">Resumen general de la actividad almacenada en Friends4You.</p>

    <div class="stats-grid">
        <article class="stat">
            <span>Total de usuarios</span>
            <strong><?= e($totalUsers) ?></strong>
        </article>
        <article class="stat">
            <span>Total de eventos</span>
            <strong><?= e($totalEvents) ?></strong>
        </article>
        <article class="stat">
            <span>Colaboradores</span>
            <strong><?= e($totalCollaborators) ?></strong>
        </article>
        <article class="stat">
            <span>Asistencias confirmadas</span>
            <strong><?= e($totalConfirmedAttendance) ?></strong>
        </article>
    </div>
</section>

<div class="grid two">
    <section class="card">
        <h2>Eventos por interés</h2>
        <p class="muted">Distribución de eventos según el interés principal asignado.</p>
        <?php if (!$eventsByInterest): ?>
            <p class="muted">Sin datos disponibles.</p>
        <?php else: ?>
            <ul class="bar-list">
                <?php
                    $maxEvents = 1;
                    foreach ($eventsByInterest as $row) {
                        if ((int) $row['total'] > $maxEvents) {
                            $maxEvents = (int) $row['total'];
                        }
                    }
                ?>
                <?php foreach ($eventsByInterest as $row): ?>
                    <?php $pct = ((int) $row['total'] / $maxEvents) * 100; ?>
                    <li>
                        <div class="bar-list__head">
                            <span><?= e($row['nombre']) ?></span>
                            <strong><?= e($row['total']) ?></strong>
                        </div>
                        <div class="bar"><div class="bar__fill" style="--bar-width: <?= e($pct) ?>%"></div></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <section class="card">
        <h2>Usuarios por rol</h2>
        <p class="muted">Cuántos usuarios hay en cada rol del sistema.</p>
        <?php if (!$usersByRole): ?>
            <p class="muted">Sin datos disponibles.</p>
        <?php else: ?>
            <ul class="bar-list">
                <?php
                    $maxRole = 1;
                    foreach ($usersByRole as $row) {
                        if ((int) $row['total'] > $maxRole) {
                            $maxRole = (int) $row['total'];
                        }
                    }
                ?>
                <?php foreach ($usersByRole as $row): ?>
                    <?php $pct = ((int) $row['total'] / $maxRole) * 100; ?>
                    <li>
                        <div class="bar-list__head">
                            <span><span class="badge badge--<?= e($row['nombre']) ?>"><?= e(ucfirst($row['nombre'])) ?></span></span>
                            <strong><?= e($row['total']) ?></strong>
                        </div>
                        <div class="bar"><div class="bar__fill" style="--bar-width: <?= e($pct) ?>%"></div></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</div>
