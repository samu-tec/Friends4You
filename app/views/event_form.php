<?php
/**
 * Campos comunes del formulario de evento.
 *
 * Se incluye desde events.php (creación y edición por parte del usuario)
 * y desde collaborator.php (creación de eventos del local). Si el usuario
 * es colaborador, su establecimiento se asocia automáticamente al evento
 * y no se muestra el desplegable de "colaborador".
 */

// Si no nos pasan datos del evento (estamos creando uno nuevo), partimos de
// un array vacío y luego sacamos los valores a variables con valor por defecto.
if (!isset($datos_evento_formulario)) {
    $datos_evento_formulario = [];
}

$nombre_evento = isset($datos_evento_formulario['nombre']) ? $datos_evento_formulario['nombre'] : '';
$descripcion_evento = isset($datos_evento_formulario['descripcion']) ? $datos_evento_formulario['descripcion'] : '';
$fecha_hora_entrada = isset($datos_evento_formulario['fecha_hora_entrada']) ? $datos_evento_formulario['fecha_hora_entrada'] : '';
$id_interes_evento = isset($datos_evento_formulario['id_interes']) ? $datos_evento_formulario['id_interes'] : '';

// Id del colaborador preseleccionado en el desplegable: si el usuario es un
// colaborador, su propio local; si estamos editando un evento ya creado, el
// que tuviera asociado.
if (isset($mi_colaborador['id_colaborador'])) {
    $id_colaborador_seleccionado = $mi_colaborador['id_colaborador'];
} elseif (isset($datos_evento_formulario['id_colaborador'])) {
    $id_colaborador_seleccionado = $datos_evento_formulario['id_colaborador'];
} else {
    $id_colaborador_seleccionado = '';
}

// Si el colaborador crea un evento de su local y aún no hay punto de
// encuentro escrito, se usa por defecto la dirección del establecimiento.
$punto_encuentro = isset($datos_evento_formulario['punto_encuentro']) ? $datos_evento_formulario['punto_encuentro'] : '';
if ($punto_encuentro === '' && tiene_rol('colaborador') && isset($mi_colaborador['direccion'])) {
    $punto_encuentro = $mi_colaborador['direccion'];
}
?>

<div class="field">
    <label>Nombre del evento</label>
    <input type="text" name="nombre" required maxlength="120"
           value="<?= escapar($nombre_evento) ?>"
           placeholder="Ej. Quedada para tomar un café">
</div>

<div class="field">
    <label>Descripción</label>
    <textarea name="descripcion" rows="3"
              placeholder="Cuenta de qué va la quedada"><?= escapar($descripcion_evento) ?></textarea>
</div>

<div class="grid two">
    <div class="field">
        <label>Fecha y hora</label>
        <input type="datetime-local" name="fecha_hora" required
               value="<?= escapar($fecha_hora_entrada) ?>">
    </div>

    <div class="field">
        <label>Interés principal</label>
        <select name="id_interes" required>
            <option value="">Selecciona un interés</option>
            <?php foreach ($intereses as $interes): ?>
                <option value="<?= escapar($interes['id_interes']) ?>" <?= valor_seleccionado($id_interes_evento, $interes['id_interes']) ?>>
                    <?= escapar($interes['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>

<div class="field">
    <label>Punto de encuentro</label>
    <input type="text" name="punto_encuentro" required maxlength="200"
           value="<?= escapar($punto_encuentro) ?>"
           placeholder="Ej. Plaza Mayor, frente al kiosco">
</div>

<div class="field">
    <?php if (tiene_rol('colaborador') && $mi_colaborador): ?>
        <label>Colaborador</label>
        <input type="text" value="<?= escapar($mi_colaborador['nombre']) ?>" disabled>
        <input type="hidden" name="id_colaborador" value="<?= escapar($mi_colaborador['id_colaborador']) ?>">
        <small class="muted">Tu establecimiento se asocia automáticamente.</small>
    <?php else: ?>
        <label>Colaborador asociado <small class="muted">(opcional)</small></label>
        <select name="id_colaborador">
            <option value="">Sin colaborador</option>
            <?php foreach ($colaboradores as $colaborador): ?>
                <option value="<?= escapar($colaborador['id_colaborador']) ?>" <?= valor_seleccionado($id_colaborador_seleccionado, $colaborador['id_colaborador']) ?>>
                    <?= escapar($colaborador['nombre']) ?> (<?= escapar($colaborador['ciudad']) ?>)
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</div>
