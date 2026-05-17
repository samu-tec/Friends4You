<?php
// Controlador de eventos: listado, filtrado, creacion, edicion,
// cancelacion y asistencia de los usuarios a los eventos.

// Pagina /events: lista, formulario y, si se pide, el detalle de un evento.
function events_controller()
{
    require_login();

    $pdo = get_db();
    $userId = current_user_id();

    if (is_post()) {
        try {
            handle_event_action($pdo, $userId);
        } catch (PDOException $e) {
            flash('error', 'No se ha podido completar la operacion del evento.');
            redirect_to('events');
        }
    }

    $filtroInteres = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
    $eventoId = (int) (isset($_GET['id_evento']) ? $_GET['id_evento'] : 0);
    $eventoSeleccionado = $eventoId > 0 ? fetch_event_detail($pdo, $eventoId) : null;

    render('events', [
        'interests' => fetch_all_interests($pdo),
        'collaborators' => fetch_all_collaborators($pdo),
        'myCollaborator' => find_collaborator_by_user($pdo, $userId),
        'events' => fetch_events($pdo, $filtroInteres),
        'filterInterest' => $filtroInteres,
        'selectedEvent' => $eventoSeleccionado,
        'attendees' => $eventoSeleccionado ? fetch_event_attendees($pdo, (int) $eventoSeleccionado['id_evento']) : [],
        'attendanceStatus' => $eventoSeleccionado ? fetch_attendance_status($pdo, $userId, (int) $eventoSeleccionado['id_evento']) : null,
    ]);
}

// Mira la accion POST recibida y llama a la funcion que toca.
function handle_event_action($pdo, $userId)
{
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'create_event') {
        create_event($pdo, $userId);
        redirect_to('events');
    }

    if ($action === 'update_event') {
        $eventoId = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        update_event($pdo, $userId, $eventoId);
        redirect_to('events', ['id_evento' => $eventoId]);
    }

    if ($action === 'cancel_event') {
        $eventoId = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        cancel_event($pdo, $userId, $eventoId);
        redirect_to('events', ['id_evento' => $eventoId]);
    }

    if ($action === 'join_event') {
        $eventoId = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        join_event($pdo, $userId, $eventoId);
        redirect_to('events', ['id_evento' => $eventoId]);
    }

    if ($action === 'cancel_attendance') {
        $eventoId = (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0);
        cancel_attendance($pdo, $userId, $eventoId);
        redirect_to('events', ['id_evento' => $eventoId]);
    }
}

// Crea un evento nuevo con los datos del formulario.
function create_event($pdo, $userId)
{
    $datos = collect_event_form_data($pdo, $userId);
    $errores = validate_event_form($datos);

    if ($errores) {
        foreach ($errores as $error) {
            flash('error', $error);
        }
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO evento
            (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $datos['nombre'],
        $datos['descripcion'],
        $datos['fecha_hora'],
        $datos['punto_encuentro'],
        $datos['id_interes'],
        $datos['id_colaborador'],
        'activo',
    ]);

    flash('success', 'Evento creado correctamente.');
}

// Actualiza un evento. Solo el creador o un administrador pueden editarlo.
function update_event($pdo, $userId, $eventoId)
{
    $evento = fetch_event_detail($pdo, $eventoId);
    if (!$evento || !can_edit_event($evento)) {
        flash('error', 'No puedes modificar este evento.');
        return;
    }

    $datos = collect_event_form_data($pdo, $userId);
    $errores = validate_event_form($datos);

    $estadosValidos = ['activo', 'cancelado', 'finalizado'];
    $estado = isset($_POST['estado_evento']) ? $_POST['estado_evento'] : 'activo';
    if (!in_array($estado, $estadosValidos)) {
        $estado = 'activo';
    }

    if ($errores) {
        foreach ($errores as $error) {
            flash('error', $error);
        }
        return;
    }

    $stmt = $pdo->prepare(
        'UPDATE evento
         SET nombre = ?, descripcion = ?, fecha_hora = ?, punto_encuentro = ?,
             id_interes = ?, id_colaborador = ?, estado_evento = ?
         WHERE id_evento = ?'
    );
    $stmt->execute([
        $datos['nombre'],
        $datos['descripcion'],
        $datos['fecha_hora'],
        $datos['punto_encuentro'],
        $datos['id_interes'],
        $datos['id_colaborador'],
        $estado,
        $eventoId,
    ]);

    flash('success', 'Evento actualizado correctamente.');
}

// Marca un evento como cancelado (solo creador o administrador).
function cancel_event($pdo, $userId, $eventoId)
{
    $evento = fetch_event_detail($pdo, $eventoId);
    if (!$evento || !can_edit_event($evento)) {
        flash('error', 'No puedes cancelar este evento.');
        return;
    }

    $stmt = $pdo->prepare('UPDATE evento SET estado_evento = ? WHERE id_evento = ?');
    $stmt->execute(['cancelado', $eventoId]);
    flash('success', 'Evento cancelado.');
}

// Apunta al usuario al evento si esta activo.
function join_event($pdo, $userId, $eventoId)
{
    $evento = fetch_event_detail($pdo, $eventoId);
    if (!$evento || $evento['estado_evento'] !== 'activo') {
        flash('error', 'Solo puedes apuntarte a eventos activos.');
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO asistencia (id_usuario, id_evento, estado_asistencia)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE estado_asistencia = VALUES(estado_asistencia)'
    );
    $stmt->execute([$userId, $eventoId, 'confirmada']);
    flash('success', 'Te has apuntado al evento.');
}

// Cancela la asistencia del usuario a un evento.
function cancel_attendance($pdo, $userId, $eventoId)
{
    $stmt = $pdo->prepare(
        'UPDATE asistencia SET estado_asistencia = ?
         WHERE id_usuario = ? AND id_evento = ?'
    );
    $stmt->execute(['cancelada', $userId, $eventoId]);
    flash('success', 'Asistencia cancelada.');
}

// Recoge los datos del formulario de evento.
// Si el usuario es colaborador, asocia automaticamente su establecimiento.
function collect_event_form_data($pdo, $userId)
{
    $fechaInput = clean_text(isset($_POST['fecha_hora']) ? $_POST['fecha_hora'] : '');
    $fechaSql = validate_datetime_value($fechaInput)
        ? str_replace('T', ' ', $fechaInput) . ':00'
        : '';

    $miColaborador = find_collaborator_by_user($pdo, $userId);
    $colaboradorPost = (int) (isset($_POST['id_colaborador']) ? $_POST['id_colaborador'] : 0);
    $idColaborador = $colaboradorPost > 0 ? $colaboradorPost : null;

    if (has_role('colaborador') && $miColaborador) {
        $idColaborador = (int) $miColaborador['id_colaborador'];
    }

    return [
        'nombre' => clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        'descripcion' => clean_text(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        'fecha_hora' => $fechaSql,
        'punto_encuentro' => clean_text(isset($_POST['punto_encuentro']) ? $_POST['punto_encuentro'] : ''),
        'id_interes' => (int) (isset($_POST['id_interes']) ? $_POST['id_interes'] : 0),
        'id_colaborador' => $idColaborador,
    ];
}

// Comprueba los datos del evento y devuelve la lista de errores.
function validate_event_form($datos)
{
    $errores = [];

    if ($datos['nombre'] === '') {
        $errores[] = 'El nombre del evento es obligatorio.';
    }

    if ($datos['fecha_hora'] === '') {
        $errores[] = 'La fecha y hora del evento no es valida.';
    } elseif (strtotime($datos['fecha_hora']) < time()) {
        $errores[] = 'La fecha y hora del evento no puede estar en el pasado.';
    }

    if ($datos['punto_encuentro'] === '') {
        $errores[] = 'El punto de encuentro es obligatorio.';
    }

    if ($datos['id_interes'] <= 0) {
        $errores[] = 'Debes seleccionar un interes.';
    }

    return $errores;
}

// Devuelve los eventos activos, opcionalmente filtrados por interes.
function fetch_events($pdo, $interestId = 0)
{
    $params = ['activo'];
    $condicion = 'WHERE i.id_interes = e.id_interes
                    AND u.id_usuario = e.id_creador
                    AND e.estado_evento = ?';

    if ($interestId > 0) {
        $condicion .= ' AND e.id_interes = ?';
        $params[] = $interestId;
    }

    $stmt = $pdo->prepare(
        'SELECT e.*, i.nombre AS interes, u.nombre AS creador_nombre, u.apellidos AS creador_apellidos,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre,
                (SELECT COUNT(*) FROM asistencia a
                 WHERE a.id_evento = e.id_evento AND a.estado_asistencia = "confirmada") AS asistentes
         FROM evento e, interes i, usuario u
         ' . $condicion . '
         ORDER BY e.fecha_hora ASC'
    );
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Datos completos de un evento por su id, o null si no existe.
function fetch_event_detail($pdo, $eventoId)
{
    $stmt = $pdo->prepare(
        'SELECT e.*, i.nombre AS interes, u.nombre AS creador_nombre, u.apellidos AS creador_apellidos,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre,
                (SELECT c.direccion FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_direccion
         FROM evento e, interes i, usuario u
         WHERE i.id_interes = e.id_interes
           AND u.id_usuario = e.id_creador
           AND e.id_evento = ?'
    );
    $stmt->execute([$eventoId]);
    $evento = $stmt->fetch();
    return $evento ? $evento : null;
}

// Lista de asistentes a un evento.
function fetch_event_attendees($pdo, $eventoId)
{
    $stmt = $pdo->prepare(
        'SELECT u.nombre, u.apellidos, a.estado_asistencia
         FROM asistencia a, usuario u
         WHERE u.id_usuario = a.id_usuario
           AND a.id_evento = ?
         ORDER BY a.estado_asistencia, u.nombre'
    );
    $stmt->execute([$eventoId]);
    return $stmt->fetchAll();
}

// Estado de asistencia del usuario a un evento, o null si no se ha apuntado.
function fetch_attendance_status($pdo, $userId, $eventoId)
{
    $stmt = $pdo->prepare(
        'SELECT estado_asistencia FROM asistencia
         WHERE id_usuario = ? AND id_evento = ?'
    );
    $stmt->execute([$userId, $eventoId]);
    $estado = $stmt->fetchColumn();
    return $estado ? $estado : null;
}

// Lista de todos los colaboradores ordenados por nombre.
function fetch_all_collaborators($pdo)
{
    $stmt = $pdo->query('SELECT * FROM colaborador ORDER BY nombre');
    return $stmt->fetchAll();
}

// Indica si el usuario puede editar el evento (creador o administrador).
function can_edit_event($evento)
{
    return has_role('administrador') || (int) $evento['id_creador'] === current_user_id();
}
