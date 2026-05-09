<?php
// Controlador de la zona del colaborador: datos del establecimiento
// y creacion de eventos asociados a ese local.

// Pagina /collaborator: muestra y procesa la zona del colaborador.
function collaborator_controller()
{
    require_role('colaborador');

    $pdo = get_db();
    $userId = current_user_id();
    $errors = [];

    if (is_post()) {
        try {
            $action = isset($_POST['action']) ? $_POST['action'] : '';

            if ($action === 'update_collaborator') {
                $errors = update_collaborator_profile($pdo, $userId);
            }
            if ($action === 'create_collaborator_event') {
                create_collaborator_event($pdo, $userId);
            }
        } catch (PDOException $e) {
            $errors[] = 'No se han podido guardar los datos del colaborador.';
        }
    }

    $colaborador = find_collaborator_by_user($pdo, $userId);

    render('collaborator', [
        'errors' => $errors,
        'collaborator' => $colaborador,
        'interests' => fetch_all_interests($pdo),
        'events' => $colaborador ? fetch_collaborator_events($pdo, (int) $colaborador['id_colaborador']) : [],
    ]);
}

// Crea o actualiza la ficha del establecimiento del colaborador.
function update_collaborator_profile($pdo, $userId)
{
    $nombre = clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $direccion = clean_text(isset($_POST['direccion']) ? $_POST['direccion'] : '');
    $ciudad = clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');
    $descripcion = clean_text(isset($_POST['descripcion']) ? $_POST['descripcion'] : '');

    $errors = validate_required_fields($_POST, [
        'nombre' => 'nombre',
        'direccion' => 'direccion',
        'ciudad' => 'ciudad',
    ]);

    if ($errors) {
        return $errors;
    }

    $colaborador = find_collaborator_by_user($pdo, $userId);

    if ($colaborador) {
        $stmt = $pdo->prepare(
            'UPDATE colaborador
             SET nombre = ?, direccion = ?, ciudad = ?, descripcion = ?
             WHERE id_usuario_colaborador = ?'
        );
        $stmt->execute([$nombre, $direccion, $ciudad, $descripcion, $userId]);
    } else {
        $stmt = $pdo->prepare(
            'INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$nombre, $direccion, $ciudad, $descripcion, $userId]);
    }

    flash('success', 'Datos del establecimiento guardados.');
    redirect_to('collaborator');
}

// Crea un evento en nombre del colaborador, asociado a su establecimiento.
function create_collaborator_event($pdo, $userId)
{
    $colaborador = find_collaborator_by_user($pdo, $userId);

    if (!$colaborador) {
        flash('error', 'Primero debes guardar los datos del establecimiento.');
        return;
    }

    $_POST['id_colaborador'] = (string) $colaborador['id_colaborador'];
    create_event($pdo, $userId);
    redirect_to('collaborator');
}

// Devuelve la ficha del colaborador asociado a un usuario, o null.
function find_collaborator_by_user($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT * FROM colaborador WHERE id_usuario_colaborador = ? LIMIT 1');
    $stmt->execute([$userId]);
    $colaborador = $stmt->fetch();
    return $colaborador ? $colaborador : null;
}

// Eventos asociados a un establecimiento colaborador.
function fetch_collaborator_events($pdo, $colaboradorId)
{
    $stmt = $pdo->prepare(
        'SELECT e.*, i.nombre AS interes
         FROM evento e
         INNER JOIN interes i ON i.id_interes = e.id_interes
         WHERE e.id_colaborador = ?
         ORDER BY e.fecha_hora DESC'
    );
    $stmt->execute([$colaboradorId]);
    return $stmt->fetchAll();
}
