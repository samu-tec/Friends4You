<?php
// Controlador del panel de administracion y del informe estadistico.
// Gestiona usuarios, intereses, colaboradores y eventos del sistema.

// Pagina /admin: panel de administracion (solo administradores).
function admin_controller()
{
    require_role('administrador');

    $pdo = get_db();

    if (is_post()) {
        try {
            handle_admin_action($pdo);
        } catch (PDOException $e) {
            flash('error', 'No se ha podido completar la operacion de administracion.');
            redirect_to('admin');
        }
    }

    render('admin', [
        'roles' => fetch_roles($pdo),
        'users' => fetch_admin_users($pdo),
        'interests' => fetch_all_interests($pdo),
        'collaborators' => fetch_admin_collaborators($pdo),
        'eligibleCollaboratorUsers' => fetch_users_without_collaborator($pdo),
        'events' => fetch_admin_events($pdo),
    ]);
}

// Mira la accion POST recibida y llama a la funcion que toca.
function handle_admin_action($pdo)
{
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'admin_update_user') {
        admin_update_user($pdo);
    }
    if ($action === 'admin_add_interest') {
        admin_add_interest($pdo);
    }
    if ($action === 'admin_delete_interest') {
        admin_delete_interest($pdo);
    }
    if ($action === 'admin_update_collaborator') {
        admin_update_collaborator($pdo);
    }
    if ($action === 'admin_create_collaborator') {
        admin_create_collaborator($pdo);
    }
    if ($action === 'admin_update_event_status') {
        admin_update_event_status($pdo);
    }

    redirect_to('admin');
}

// Actualiza datos basicos y rol de un usuario.
function admin_update_user($pdo)
{
    $stmt = $pdo->prepare(
        'UPDATE usuario SET nombre = ?, apellidos = ?, ciudad = ?, id_rol = ?
         WHERE id_usuario = ?'
    );
    $stmt->execute([
        clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        clean_text(isset($_POST['apellidos']) ? $_POST['apellidos'] : ''),
        clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        (int) (isset($_POST['id_rol']) ? $_POST['id_rol'] : 0),
        (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0),
    ]);

    flash('success', 'Usuario actualizado.');
}

// Crea un interes nuevo evitando duplicados por nombre.
function admin_add_interest($pdo)
{
    $nombre = clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : '');

    if ($nombre === '') {
        flash('error', 'El nombre del interes es obligatorio.');
        return;
    }

    if (mb_strlen($nombre, 'UTF-8') > 100) {
        flash('error', 'El nombre del interes no puede superar los 100 caracteres.');
        return;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM interes WHERE nombre = ?');
    $stmt->execute([$nombre]);

    if ((int) $stmt->fetchColumn() > 0) {
        flash('info', 'Ese interes ya existe.');
        return;
    }

    $stmt = $pdo->prepare('INSERT INTO interes (nombre) VALUES (?)');
    $stmt->execute([$nombre]);
    flash('success', 'Interes creado.');
}

// Elimina un interes si no esta usado por ningun evento.
function admin_delete_interest($pdo)
{
    $interesId = (int) (isset($_POST['id_interes']) ? $_POST['id_interes'] : 0);

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM evento WHERE id_interes = ?');
    $stmt->execute([$interesId]);
    $usados = (int) $stmt->fetchColumn();

    if ($usados > 0) {
        flash('error', 'No se puede eliminar: hay ' . $usados . ' evento(s) usando este interes.');
        return;
    }

    $stmt = $pdo->prepare('DELETE FROM interes WHERE id_interes = ?');
    $stmt->execute([$interesId]);
    flash('success', 'Interes eliminado.');
}

// Actualiza los datos de un colaborador.
function admin_update_collaborator($pdo)
{
    $stmt = $pdo->prepare(
        'UPDATE colaborador SET nombre = ?, direccion = ?, ciudad = ?, descripcion = ?
         WHERE id_colaborador = ?'
    );
    $stmt->execute([
        clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        clean_text(isset($_POST['direccion']) ? $_POST['direccion'] : ''),
        clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        clean_text(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        (int) (isset($_POST['id_colaborador']) ? $_POST['id_colaborador'] : 0),
    ]);

    flash('success', 'Colaborador actualizado.');
}

// Crea un colaborador para una cuenta con rol "colaborador".
function admin_create_collaborator($pdo)
{
    $stmt = $pdo->prepare(
        'INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        clean_text(isset($_POST['direccion']) ? $_POST['direccion'] : ''),
        clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        clean_text(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        (int) (isset($_POST['id_usuario_colaborador']) ? $_POST['id_usuario_colaborador'] : 0),
    ]);

    flash('success', 'Colaborador creado.');
}

// Cambia el estado de un evento (activo, cancelado o finalizado).
function admin_update_event_status($pdo)
{
    $estadosValidos = ['activo', 'cancelado', 'finalizado'];
    $estado = isset($_POST['estado_evento']) ? $_POST['estado_evento'] : 'activo';

    if (!in_array($estado, $estadosValidos)) {
        $estado = 'activo';
    }

    $stmt = $pdo->prepare('UPDATE evento SET estado_evento = ? WHERE id_evento = ?');
    $stmt->execute([$estado, (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0)]);
    flash('success', 'Estado del evento actualizado.');
}

// Pagina /report: informe estadistico (solo administradores).
function report_controller()
{
    require_role('administrador');

    $pdo = get_db();

    render('report', [
        'totalUsers' => (int) $pdo->query('SELECT COUNT(*) FROM usuario')->fetchColumn(),
        'totalEvents' => (int) $pdo->query('SELECT COUNT(*) FROM evento')->fetchColumn(),
        'totalCollaborators' => (int) $pdo->query('SELECT COUNT(*) FROM colaborador')->fetchColumn(),
        'totalConfirmedAttendance' => (int) $pdo->query(
            'SELECT COUNT(*) FROM asistencia WHERE estado_asistencia = "confirmada"'
        )->fetchColumn(),
        'eventsByInterest' => fetch_events_by_interest($pdo),
        'usersByRole' => fetch_users_by_role($pdo),
    ]);
}

// Todos los roles disponibles (para el desplegable del admin).
function fetch_roles($pdo)
{
    return $pdo->query('SELECT * FROM rol ORDER BY id_rol')->fetchAll();
}

// Todos los usuarios con su rol.
function fetch_admin_users($pdo)
{
    $stmt = $pdo->query(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
         ORDER BY u.id_usuario'
    );
    return $stmt->fetchAll();
}

// Colaboradores junto con el correo de la cuenta asociada.
function fetch_admin_collaborators($pdo)
{
    $stmt = $pdo->query(
        'SELECT c.*, u.correo
         FROM colaborador c, usuario u
         WHERE u.id_usuario = c.id_usuario_colaborador
         ORDER BY c.nombre'
    );
    return $stmt->fetchAll();
}

// Usuarios con rol "colaborador" que aun no tienen ficha de establecimiento.
function fetch_users_without_collaborator($pdo)
{
    $stmt = $pdo->query(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.correo
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND r.nombre = "colaborador"
           AND NOT EXISTS (SELECT 1 FROM colaborador c
                           WHERE c.id_usuario_colaborador = u.id_usuario)
         ORDER BY u.nombre'
    );
    return $stmt->fetchAll();
}

// Todos los eventos con interes, creador y colaborador.
function fetch_admin_events($pdo)
{
    $stmt = $pdo->query(
        'SELECT e.*, i.nombre AS interes, u.correo AS creador_correo,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre
         FROM evento e, interes i, usuario u
         WHERE i.id_interes = e.id_interes
           AND u.id_usuario = e.id_creador
         ORDER BY e.fecha_hora DESC'
    );
    return $stmt->fetchAll();
}

// Numero de eventos agrupados por interes (para el informe).
function fetch_events_by_interest($pdo)
{
    $stmt = $pdo->query(
        'SELECT i.nombre,
                (SELECT COUNT(*) FROM evento e
                 WHERE e.id_interes = i.id_interes) AS total
         FROM interes i
         ORDER BY total DESC, i.nombre'
    );
    return $stmt->fetchAll();
}

// Numero de usuarios agrupados por rol (para el informe).
function fetch_users_by_role($pdo)
{
    $stmt = $pdo->query(
        'SELECT r.nombre,
                (SELECT COUNT(*) FROM usuario u
                 WHERE u.id_rol = r.id_rol) AS total
         FROM rol r
         ORDER BY r.id_rol'
    );
    return $stmt->fetchAll();
}
