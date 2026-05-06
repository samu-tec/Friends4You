<?php
// Campos comunes del formulario de evento. Se incluye desde events.php y
// collaborator.php. Si el usuario es colaborador, su establecimiento se
// asocia automaticamente al evento.
$eventFormData = isset($eventFormData) ? $eventFormData : [];
$esColaborador = has_role('colaborador');
$colaboradorActual = isset($myCollaborator['id_colaborador'])
    ? $myCollaborator['id_colaborador']
    : (isset($eventFormData['id_colaborador']) ? $eventFormData['id_colaborador'] : '');
?>

<div class="field">
    <label>Nombre del evento</label>
    <input type="text" name="nombre" required maxlength="120"
           value="<?= e($eventFormData['nombre'] ?? '') ?>"
           placeholder="Ej. Quedada para tomar un cafe">
</div>

<div class="field">
    <label>Descripcion</label>
    <textarea name="descripcion" rows="3"
              placeholder="Cuenta de que va la quedada"><?= e($eventFormData['descripcion'] ?? '') ?></textarea>
</div>

<div class="grid two">
    <div class="field">
        <label>Fecha y hora</label>
        <input type="datetime-local" name="fecha_hora" required
               value="<?= e($eventFormData['fecha_hora_input'] ?? '') ?>">
    </div>

    <div class="field">
        <label>Interes principal</label>
        <select name="id_interes" required>
            <option value="">Selecciona un interes</option>
            <?php foreach ($interests as $interest): ?>
                <option value="<?= e($interest['id_interes']) ?>" <?= selected_value($eventFormData['id_interes'] ?? '', $interest['id_interes']) ?>>
                    <?= e($interest['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="field">
    <label>Punto de encuentro</label>
    <input type="text" name="punto_encuentro" required maxlength="200"
           value="<?= e($eventFormData['punto_encuentro'] ?? '') ?>"
           placeholder="Ej. Plaza Mayor, frente al kiosco">
</div>

<div class="field">
    <?php if ($esColaborador && $myCollaborator): ?>
        <label>Colaborador</label>
        <input type="text" value="<?= e($myCollaborator['nombre']) ?>" disabled>
        <input type="hidden" name="id_colaborador" value="<?= e($myCollaborator['id_colaborador']) ?>">
        <small class="muted">Tu establecimiento se asocia automaticamente.</small>
    <?php else: ?>
        <label>Colaborador asociado <small class="muted">(opcional)</small></label>
        <select name="id_colaborador">
            <option value="">Sin colaborador</option>
            <?php foreach ($collaborators as $collaborator): ?>
                <option value="<?= e($collaborator['id_colaborador']) ?>" <?= selected_value($colaboradorActual, $collaborator['id_colaborador']) ?>>
                    <?= e($collaborator['nombre']) ?> (<?= e($collaborator['ciudad']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</div>
