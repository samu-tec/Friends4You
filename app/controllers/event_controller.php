<?php
/**
 * Controlador de eventos.
 *
 * Aquí están las acciones para listar, filtrar, crear, modificar, cancelar
 * y eliminar eventos, así como apuntarse y cancelar la asistencia. La parte
 * social (apuntarse a un evento, crear quedadas propias) es exclusiva del
 * rol "usuario"; el colaborador crea eventos asociados a su local desde su
 * propia página, y el administrador los crea y los gestiona desde el panel.
 */

/**
 * Página /events: lista, formulario y, si se pide, detalle de un evento.
 *
 * Exige sesión iniciada. Si la petición es POST se procesa la acción
 * correspondiente (crear, editar, borrar, apuntarse o cancelar
 * asistencia). Después renderiza la vista con la lista de eventos
 * activos y, si se pidió un evento concreto, su detalle con asistentes
 * y estado de asistencia.
 *
 * @return void
 */
function controlador_eventos()
{
    exigir_sesion();

    $bd = obtener_bd();
    $id_usuario = id_usuario_actual();

    if (es_post()) {
        try {
            procesar_accion_evento($bd, $id_usuario);
        } catch (PDOException $excepcion) {
            aviso('error', 'No se ha podido completar la operación del evento.');
            redirigir('events');
        }
    }

    $filtro_interes = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
    $id_evento = (int) (isset($_GET['id_evento']) ? $_GET['id_evento'] : 0);
    $evento_seleccionado = $id_evento > 0 ? obtener_detalle_evento($bd, $id_evento) : null;

    // mi_colaborador solo tiene sentido si el usuario conectado es un
    // colaborador (el formulario de evento solo lo usa en ese caso).
    $mi_colaborador = tiene_rol('colaborador') ? buscar_colaborador_por_usuario($bd, $id_usuario) : null;

    mostrar_vista('events', [
        'intereses' => obtener_intereses($bd),
        'colaboradores' => obtener_colaboradores($bd),
        'mi_colaborador' => $mi_colaborador,
        'eventos' => obtener_eventos($bd, $filtro_interes),
        'filtro_interes' => $filtro_interes,
        'evento_seleccionado' => $evento_seleccionado,
        'asistentes' => $evento_seleccionado ? obtener_asistentes_evento($bd, (int) $evento_seleccionado['id_evento']) : [],
        'estado_asistencia' => $evento_seleccionado ? obtener_estado_asistencia($bd, $id_usuario, (int) $evento_seleccionado['id_evento']) : null,
    ]);
}

/**
 * Procesa la acción POST recibida en la página /events.
 *
 * Decide qué función ejecutar según el campo oculto "accion" del
 * formulario. Antes de crear o apuntarse comprueba el rol: la parte
 * social (crear eventos propios, apuntarse) es solo del rol "usuario".
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return void
 */
function procesar_accion_evento($bd, $id_usuario)
{
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion === 'crear_evento') {
        // Crear eventos normales es solo del rol usuario (las personas).
        // El colaborador los crea desde su página; el admin desde el panel.
        if (!tiene_rol('usuario')) {
            aviso('error', 'Crea los eventos desde tu propia página de gestión.');
            redirigir(tiene_rol('colaborador') ? 'collaborator' : 'admin');
        }
        crear_evento($bd, $id_usuario);
        redirigir('events');
    }

    if ($accion === 'actualizar_evento') {
        $id_evento = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        actualizar_evento($bd, $id_usuario, $id_evento);
        redirigir('events', ['id_evento' => $id_evento]);
    }

    if ($accion === 'eliminar_evento') {
        $id_evento = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        eliminar_evento($bd, $id_evento);
        redirigir('events');
    }

    if ($accion === 'apuntarse_evento') {
        // Apuntarse a eventos es solo de las personas (rol usuario).
        if (!tiene_rol('usuario')) {
            aviso('error', 'Solo los usuarios pueden apuntarse a eventos.');
            redirigir('events', ['id_evento' => (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0)]);
        }
        $id_evento = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        apuntarse_evento($bd, $id_usuario, $id_evento);
        redirigir('events', ['id_evento' => $id_evento]);
    }

    if ($accion === 'cancelar_asistencia') {
        $id_evento = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        cancelar_asistencia($bd, $id_usuario, $id_evento);
        redirigir('events', ['id_evento' => $id_evento]);
    }
}

/**
 * Crea un evento nuevo con los datos del formulario.
 *
 * Recoge los datos, los valida y, si todo está bien, inserta el evento en
 * la base de datos en estado "activo". Los errores se guardan como avisos
 * para mostrarlos al volver a la página.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que será el creador del evento.
 * @return void
 */
function crear_evento($bd, $id_usuario)
{
    $datos = recoger_datos_evento($bd, $id_usuario);
    $errores = validar_formulario_evento($datos);

    if ($errores) {
        foreach ($errores as $error) {
            aviso('error', $error);
        }
        return;
    }

    $consulta = $bd->prepare(
        'INSERT INTO evento
            (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $consulta->execute([
        $id_usuario,
        $datos['nombre'],
        $datos['descripcion'],
        $datos['fecha_hora'],
        $datos['punto_encuentro'],
        $datos['id_interes'],
        $datos['id_colaborador'],
        'activo',
    ]);

    aviso('success', 'Evento creado correctamente.');
}

/**
 * Actualiza un evento existente.
 *
 * Solo el creador del evento o un administrador pueden editarlo (lo
 * decide puede_editar_evento). Valida los datos y permite además cambiar el
 * estado del evento entre activo, cancelado y finalizado.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado (lo necesita recoger_datos_evento para asociar el colaborador si es un local).
 * @param int $id_evento Id del evento que se quiere editar.
 * @return void
 */
function actualizar_evento($bd, $id_usuario, $id_evento)
{
    $evento = obtener_detalle_evento($bd, $id_evento);
    if (!$evento || !puede_editar_evento($evento)) {
        aviso('error', 'No puedes modificar este evento.');
        return;
    }

    $datos = recoger_datos_evento($bd, $id_usuario);
    $errores = validar_formulario_evento($datos);

    $estados_validos = ['activo', 'cancelado', 'finalizado'];
    $estado = isset($_POST['estado_evento']) ? $_POST['estado_evento'] : 'activo';
    if (!in_array($estado, $estados_validos)) {
        $estado = 'activo';
    }

    if ($errores) {
        foreach ($errores as $error) {
            aviso('error', $error);
        }
        return;
    }

    $consulta = $bd->prepare(
        'UPDATE evento
         SET nombre = ?, descripcion = ?, fecha_hora = ?, punto_encuentro = ?,
             id_interes = ?, id_colaborador = ?, estado_evento = ?
         WHERE id_evento = ?'
    );
    $consulta->execute([
        $datos['nombre'],
        $datos['descripcion'],
        $datos['fecha_hora'],
        $datos['punto_encuentro'],
        $datos['id_interes'],
        $datos['id_colaborador'],
        $estado,
        $id_evento,
    ]);

    aviso('success', 'Evento actualizado correctamente.');
}

/**
 * Borra un evento por completo (solo el creador o un administrador).
 *
 * Las asistencias asociadas se borran solas por la clave foránea con
 * ON DELETE CASCADE, así no hace falta borrar nada manualmente.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_evento Id del evento a borrar.
 * @return void
 */
function eliminar_evento($bd, $id_evento)
{
    $evento = obtener_detalle_evento($bd, $id_evento);
    if (!$evento || !puede_editar_evento($evento)) {
        aviso('error', 'No puedes eliminar este evento.');
        return;
    }

    $consulta = $bd->prepare('DELETE FROM evento WHERE id_evento = ?');
    $consulta->execute([$id_evento]);
    aviso('success', 'Evento eliminado.');
}

/**
 * Apunta al usuario a un evento si está activo.
 *
 * Si el usuario nunca se había apuntado antes, inserta una fila nueva.
 * Si ya existía (por ejemplo, había cancelado y ahora se vuelve a apuntar),
 * solo actualiza el estado a "confirmada". Hay que distinguir los dos casos
 * porque la clave primaria compuesta (id_usuario, id_evento) impide repetir
 * la inserción.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que se apunta.
 * @param int $id_evento Id del evento al que se apunta.
 * @return void
 */
function apuntarse_evento($bd, $id_usuario, $id_evento)
{
    $evento = obtener_detalle_evento($bd, $id_evento);
    if (!$evento || $evento['estado_evento'] !== 'activo') {
        aviso('error', 'Solo puedes apuntarte a eventos activos.');
        return;
    }

    $consulta = $bd->prepare(
        'SELECT COUNT(*) FROM asistencia WHERE id_usuario = ? AND id_evento = ?'
    );
    $consulta->execute([$id_usuario, $id_evento]);

    if ((int) $consulta->fetchColumn() > 0) {
        $consulta = $bd->prepare(
            'UPDATE asistencia SET estado_asistencia = ?
             WHERE id_usuario = ? AND id_evento = ?'
        );
        $consulta->execute(['confirmada', $id_usuario, $id_evento]);
    } else {
        $consulta = $bd->prepare(
            'INSERT INTO asistencia (id_usuario, id_evento, estado_asistencia)
             VALUES (?, ?, ?)'
        );
        $consulta->execute([$id_usuario, $id_evento, 'confirmada']);
    }

    aviso('success', 'Te has apuntado al evento.');
}

/**
 * Cancela la asistencia del usuario a un evento.
 *
 * No borra la fila de asistencia: cambia el estado a "cancelada" para
 * conservar el historial de quién se apuntó al evento.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que cancela.
 * @param int $id_evento Id del evento.
 * @return void
 */
function cancelar_asistencia($bd, $id_usuario, $id_evento)
{
    $consulta = $bd->prepare(
        'UPDATE asistencia SET estado_asistencia = ?
         WHERE id_usuario = ? AND id_evento = ?'
    );
    $consulta->execute(['cancelada', $id_usuario, $id_evento]);
    aviso('success', 'Asistencia cancelada.');
}

/**
 * Recoge los datos del formulario de evento y los normaliza.
 *
 * Convierte la fecha del input datetime-local al formato MySQL y, si el
 * usuario es colaborador, fuerza que el evento quede asociado a su propio
 * establecimiento (no se le permite asignarlo a otro local).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que envía el formulario.
 * @return array Datos del evento listos para usar en INSERT/UPDATE.
 */
function recoger_datos_evento($bd, $id_usuario)
{
    $fecha_entrada = trim(isset($_POST['fecha_hora']) ? $_POST['fecha_hora'] : '');
    $fecha_sql = validar_fecha_hora($fecha_entrada)
        ? str_replace('T', ' ', $fecha_entrada) . ':00'
        : '';

    $mi_colaborador = buscar_colaborador_por_usuario($bd, $id_usuario);
    $id_colaborador_enviado = (int) (isset($_POST['id_colaborador']) ? $_POST['id_colaborador'] : 0);
    $id_colaborador = $id_colaborador_enviado > 0 ? $id_colaborador_enviado : null;

    if (tiene_rol('colaborador') && $mi_colaborador) {
        $id_colaborador = (int) $mi_colaborador['id_colaborador'];
    }

    return [
        'nombre' => trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        'descripcion' => trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        'fecha_hora' => $fecha_sql,
        'punto_encuentro' => trim(isset($_POST['punto_encuentro']) ? $_POST['punto_encuentro'] : ''),
        'id_interes' => (int) (isset($_POST['id_interes']) ? $_POST['id_interes'] : 0),
        'id_colaborador' => $id_colaborador,
    ];
}

/**
 * Valida los datos del formulario de evento.
 *
 * Comprueba que el nombre, la fecha y el punto de encuentro están
 * rellenos, que la fecha es futura y que se ha elegido un interés.
 *
 * @param array $datos Datos recogidos por recoger_datos_evento.
 * @return array Lista de mensajes de error (vacía si todo está bien).
 */
function validar_formulario_evento($datos)
{
    $errores = [];

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre del evento es obligatorio.';
    }

    if ($datos['fecha_hora'] === '') {
        $errores[] = 'La fecha y hora del evento no es válida.';
    } elseif (strtotime($datos['fecha_hora']) < time()) {
        $errores[] = 'La fecha y hora del evento no puede estar en el pasado.';
    }

    if ($datos['punto_encuentro'] === '') {
        $errores[] = 'El punto de encuentro es obligatorio.';
    }

    if ($datos['id_interes'] <= 0) {
        $errores[] = 'Debes seleccionar un interés.';
    }

    return $errores;
}

/**
 * Devuelve los eventos activos para la lista pública.
 *
 * Permite filtrar opcionalmente por interés. Para cada evento incluye el
 * nombre del interés, los datos del creador, el nombre del colaborador
 * (si lo hay) y la cuenta de asistentes confirmados.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_interes Id del interés para filtrar (0 = sin filtro).
 * @return array Lista de eventos ordenados por fecha ascendente.
 */
function obtener_eventos($bd, $id_interes = 0)
{
    // El primer parámetro ("confirmada") es el de la subconsulta que cuenta
    // asistentes, que aparece antes del WHERE en la consulta final.
    $parametros = ['confirmada', 'activo'];
    $condicion = 'WHERE i.id_interes = e.id_interes
                    AND u.id_usuario = e.id_creador
                    AND e.estado_evento = ?';

    if ($id_interes > 0) {
        $condicion .= ' AND e.id_interes = ?';
        $parametros[] = $id_interes;
    }

    $consulta = $bd->prepare(
        'SELECT e.*, i.nombre AS interes, u.nombre AS creador_nombre, u.apellidos AS creador_apellidos,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre,
                (SELECT COUNT(*) FROM asistencia a
                 WHERE a.id_evento = e.id_evento AND a.estado_asistencia = ?) AS asistentes
         FROM evento e, interes i, usuario u
         ' . $condicion . '
         ORDER BY e.fecha_hora ASC'
    );
    $consulta->execute($parametros);
    return $consulta->fetchAll();
}

/**
 * Devuelve los datos completos de un evento por su id.
 *
 * Además de los campos del evento incluye el interés, los datos del
 * creador y el nombre del colaborador (si existe).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_evento Id del evento.
 * @return array|null Datos del evento, o null si no existe.
 */
function obtener_detalle_evento($bd, $id_evento)
{
    $consulta = $bd->prepare(
        'SELECT e.*, i.nombre AS interes, u.nombre AS creador_nombre, u.apellidos AS creador_apellidos,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre
         FROM evento e, interes i, usuario u
         WHERE i.id_interes = e.id_interes
           AND u.id_usuario = e.id_creador
           AND e.id_evento = ?'
    );
    $consulta->execute([$id_evento]);
    $evento = $consulta->fetch();
    return $evento ? $evento : null;
}

/**
 * Devuelve la lista de asistentes a un evento.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_evento Id del evento.
 * @return array Lista de asistentes con su nombre y estado de asistencia.
 */
function obtener_asistentes_evento($bd, $id_evento)
{
    $consulta = $bd->prepare(
        'SELECT u.nombre, u.apellidos, a.estado_asistencia
         FROM asistencia a, usuario u
         WHERE u.id_usuario = a.id_usuario
           AND a.id_evento = ?
         ORDER BY u.nombre'
    );
    $consulta->execute([$id_evento]);
    return $consulta->fetchAll();
}

/**
 * Devuelve el estado de asistencia del usuario a un evento concreto.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario.
 * @param int $id_evento Id del evento.
 * @return string|null "confirmada", "cancelada"... o null si no se ha apuntado nunca.
 */
function obtener_estado_asistencia($bd, $id_usuario, $id_evento)
{
    $consulta = $bd->prepare(
        'SELECT estado_asistencia FROM asistencia
         WHERE id_usuario = ? AND id_evento = ?'
    );
    $consulta->execute([$id_usuario, $id_evento]);
    $estado = $consulta->fetchColumn();
    return $estado ? $estado : null;
}

/**
 * Devuelve todos los colaboradores ordenados por nombre.
 *
 * Se usa para pintar el desplegable "Colaborador asociado" del formulario
 * de evento (el usuario puede asociar opcionalmente su quedada a un local).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de colaboradores.
 */
function obtener_colaboradores($bd)
{
    $consulta = $bd->query('SELECT * FROM colaborador ORDER BY nombre');
    return $consulta->fetchAll();
}

/**
 * Indica si el usuario actual puede editar el evento.
 *
 * Pueden editar (modificar, cancelar o eliminar) un evento únicamente su
 * creador y los administradores; el resto solo puede verlo.
 *
 * @param array $evento Datos del evento (debe incluir id_creador).
 * @return bool true si el usuario tiene permiso, false si no.
 */
function puede_editar_evento($evento)
{
    return tiene_rol('administrador') || (int) $evento['id_creador'] === id_usuario_actual();
}
