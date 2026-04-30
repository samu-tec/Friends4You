<?php
// Controlador del perfil del usuario y de la busqueda de personas.
// Gestiona la edicion de datos, intereses, contrasena, las solicitudes
// de amistad y la consulta de perfiles publicos.

// Pagina /profile: editar perfil, intereses y contrasena del usuario.
function profile_controller()
{
    require_login();

    $pdo = get_db();
    $errors = [];
    $userId = current_user_id();

    if (is_post()) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        try {
            if ($action === 'update_profile') {
                $errors = update_profile($pdo, $userId);
            }
            if ($action === 'update_interests') {
                update_user_interests($pdo, $userId);
            }
            if ($action === 'create_interest') {
                $errors = create_interest_from_profile($pdo, $userId);
            }
            if ($action === 'change_password') {
                $errors = change_password($pdo, $userId);
            }
        } catch (PDOException $e) {
            $errors[] = 'No se han podido guardar los cambios.';
        }
    }

    render('profile', [
        'errors' => $errors,
        'user' => fetch_user_profile($pdo, $userId),
        'interests' => fetch_all_interests($pdo),
        'selectedInterests' => fetch_user_interest_ids($pdo, $userId),
        'stats' => fetch_user_stats($pdo, $userId),
    ]);
}

// Actualiza nombre, apellidos y ciudad. Devuelve errores de validacion.
function update_profile($pdo, $userId)
{
    $nombre = clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $apellidos = clean_text(isset($_POST['apellidos']) ? $_POST['apellidos'] : '');
    $ciudad = clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');

    $errors = validate_required_fields($_POST, [
        'nombre' => 'nombre',
        'apellidos' => 'apellidos',
        'ciudad' => 'ciudad',
    ]);

    if ($errors) {
        return $errors;
    }

    $stmt = $pdo->prepare(
        'UPDATE usuario SET nombre = ?, apellidos = ?, ciudad = ? WHERE id_usuario = ?'
    );
    $stmt->execute([$nombre, $apellidos, $ciudad, $userId]);

    $_SESSION['user']['nombre'] = $nombre;
    flash('success', 'Perfil actualizado correctamente.');
    redirect_to('profile');
}

// Sustituye los intereses del usuario por los marcados en el formulario.
function update_user_interests($pdo, $userId)
{
    $interestIds = [];
    $enviados = isset($_POST['intereses']) ? $_POST['intereses'] : [];
    foreach ($enviados as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $interestIds[] = $id;
        }
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare('DELETE FROM preferencia WHERE id_usuario = ?');
    $stmt->execute([$userId]);

    if ($interestIds) {
        $stmt = $pdo->prepare(
            'INSERT INTO preferencia (id_usuario, id_interes) VALUES (?, ?)'
        );
        foreach (array_unique($interestIds) as $interestId) {
            $stmt->execute([$userId, $interestId]);
        }
    }

    $pdo->commit();
    flash('success', 'Intereses actualizados correctamente.');
    redirect_to('profile');
}

// Crea un interes nuevo desde el perfil y lo asocia al usuario.
function create_interest_from_profile($pdo, $userId)
{
    $nombre = clean_text(isset($_POST['nombre_interes']) ? $_POST['nombre_interes'] : '');

    if ($nombre === '') {
        return ['El nombre del nuevo interes es obligatorio.'];
    }

    if (mb_strlen($nombre, 'UTF-8') > 100) {
        return ['El nombre del interes no puede superar los 100 caracteres.'];
    }

    $stmt = $pdo->prepare('SELECT id_interes FROM interes WHERE nombre = ? LIMIT 1');
    $stmt->execute([$nombre]);
    $interestId = $stmt->fetchColumn();

    if (!$interestId) {
        $stmt = $pdo->prepare('INSERT INTO interes (nombre) VALUES (?)');
        $stmt->execute([$nombre]);
        $interestId = (int) $pdo->lastInsertId();
    }

    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO preferencia (id_usuario, id_interes) VALUES (?, ?)'
    );
    $stmt->execute([$userId, (int) $interestId]);

    flash('success', 'Interes creado y anadido a tu perfil.');
    redirect_to('profile');
}

// Pagina /users: busqueda de usuarios, solicitudes y perfil publico.
function users_controller()
{
    require_login();

    $pdo = get_db();
    $userId = current_user_id();

    if (is_post()) {
        try {
            handle_friendship_action($pdo, $userId);
        } catch (PDOException $e) {
            flash('error', 'No se ha podido completar la operacion de amistad.');
            redirect_to('users');
        }
    }

    $ciudad = clean_text(isset($_GET['ciudad']) ? $_GET['ciudad'] : '');
    $interestId = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
    $profileUserId = (int) (isset($_GET['id_usuario']) ? $_GET['id_usuario'] : 0);

    render('users', [
        'interests' => fetch_all_interests($pdo),
        'searchCity' => $ciudad,
        'searchInterest' => $interestId,
        'users' => search_users($pdo, $userId, $ciudad, $interestId),
        'friendshipStatus' => fetch_friendship_status($pdo, $userId),
        'pendingRequests' => fetch_pending_requests($pdo, $userId),
        'friends' => fetch_accepted_friends($pdo, $userId),
        'publicProfile' => $profileUserId > 0 ? fetch_public_user_profile($pdo, $profileUserId) : null,
    ]);
}

// Mira la accion POST recibida y llama a la funcion que toca.
function handle_friendship_action($pdo, $userId)
{
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'send_friend_request') {
        $destinoId = (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0);
        send_friend_request($pdo, $userId, $destinoId);
    }

    if ($action === 'answer_friend_request') {
        $origenId = (int) (isset($_POST['usuario_origen']) ? $_POST['usuario_origen'] : 0);
        $respuesta = isset($_POST['respuesta']) ? $_POST['respuesta'] : 'rechazada';
        answer_friend_request($pdo, $userId, $origenId, $respuesta);
    }

    redirect_to('users');
}

// Envia una solicitud de amistad evitando duplicados y auto-solicitudes.
function send_friend_request($pdo, $userId, $destinoId)
{
    if ($destinoId <= 0 || $destinoId === $userId) {
        flash('error', 'No puedes enviarte una solicitud a ti mismo.');
        return;
    }

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE id_usuario = ?');
    $stmt->execute([$destinoId]);
    if ((int) $stmt->fetchColumn() === 0) {
        flash('error', 'El usuario seleccionado no existe.');
        return;
    }

    $stmt = $pdo->prepare(
        'SELECT estado FROM amistad
         WHERE (usuario_origen = ? AND usuario_destino = ?)
            OR (usuario_origen = ? AND usuario_destino = ?)
         LIMIT 1'
    );
    $stmt->execute([$userId, $destinoId, $destinoId, $userId]);

    if ($stmt->fetch()) {
        flash('info', 'Ya existe una solicitud o amistad con ese usuario.');
        return;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO amistad (usuario_origen, usuario_destino, estado) VALUES (?, ?, ?)'
    );
    $stmt->execute([$userId, $destinoId, 'pendiente']);
    flash('success', 'Solicitud de amistad enviada.');
}

// Acepta o rechaza una solicitud de amistad recibida.
function answer_friend_request($pdo, $userId, $origenId, $respuesta)
{
    $estado = $respuesta === 'aceptada' ? 'aceptada' : 'rechazada';
    $stmt = $pdo->prepare(
        'UPDATE amistad SET estado = ?
         WHERE usuario_origen = ? AND usuario_destino = ? AND estado = ?'
    );
    $stmt->execute([$estado, $origenId, $userId, 'pendiente']);

    flash('success', $estado === 'aceptada' ? 'Solicitud aceptada.' : 'Solicitud rechazada.');
}

// Estadisticas del usuario: amigos, eventos creados, asistencias e intereses.
function fetch_user_stats($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM amistad
         WHERE estado = ? AND (usuario_origen = ? OR usuario_destino = ?)'
    );
    $stmt->execute(['aceptada', $userId, $userId]);
    $amigos = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM evento WHERE id_creador = ?');
    $stmt->execute([$userId]);
    $eventosCreados = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM asistencia WHERE id_usuario = ? AND estado_asistencia = ?'
    );
    $stmt->execute([$userId, 'confirmada']);
    $eventosApuntado = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM preferencia WHERE id_usuario = ?');
    $stmt->execute([$userId]);
    $numIntereses = (int) $stmt->fetchColumn();

    return [
        'friends' => $amigos,
        'events_created' => $eventosCreados,
        'events_joined' => $eventosApuntado,
        'interests' => $numIntereses,
    ];
}

// Datos del usuario junto con el nombre de su rol.
function fetch_user_profile($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u
         INNER JOIN rol r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = ?'
    );
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch();
    return $usuario ? $usuario : [];
}

// Lista de todos los intereses ordenados por nombre.
function fetch_all_interests($pdo)
{
    $stmt = $pdo->query('SELECT * FROM interes ORDER BY nombre');
    return $stmt->fetchAll();
}

// Ids de los intereses marcados por el usuario.
function fetch_user_interest_ids($pdo, $userId)
{
    $stmt = $pdo->prepare('SELECT id_interes FROM preferencia WHERE id_usuario = ?');
    $stmt->execute([$userId]);
    $ids = [];
    foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $ids[] = (int) $id;
    }
    return $ids;
}

// Busca usuarios filtrando por ciudad e interes (sin incluirse a si mismo).
function search_users($pdo, $userId, $ciudad, $interestId)
{
    $params = [$userId];
    $condiciones = ['u.id_usuario <> ?'];

    if ($ciudad !== '') {
        $condiciones[] = 'u.ciudad LIKE ?';
        $params[] = '%' . $ciudad . '%';
    }

    if ($interestId > 0) {
        $condiciones[] = 'EXISTS (
            SELECT 1 FROM preferencia p2
            WHERE p2.id_usuario = u.id_usuario AND p2.id_interes = ?
        )';
        $params[] = $interestId;
    }

    $sql = 'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad,
                   GROUP_CONCAT(DISTINCT i.nombre ORDER BY i.nombre SEPARATOR ", ") AS intereses
            FROM usuario u
            LEFT JOIN preferencia p ON p.id_usuario = u.id_usuario
            LEFT JOIN interes i ON i.id_interes = p.id_interes
            WHERE ' . implode(' AND ', $condiciones) . '
            GROUP BY u.id_usuario, u.nombre, u.apellidos, u.ciudad
            ORDER BY u.ciudad, u.nombre';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Estado de amistad del usuario con el resto, indexado por el id del otro.
function fetch_friendship_status($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT
             CASE WHEN usuario_origen = ? THEN usuario_destino ELSE usuario_origen END AS other_id,
             usuario_origen,
             estado
         FROM amistad
         WHERE usuario_origen = ? OR usuario_destino = ?'
    );
    $stmt->execute([$userId, $userId, $userId]);

    $estados = [];
    foreach ($stmt->fetchAll() as $fila) {
        $estados[(int) $fila['other_id']] = [
            'estado' => $fila['estado'],
            'usuario_origen' => (int) $fila['usuario_origen'],
        ];
    }

    return $estados;
}

// Solicitudes de amistad pendientes recibidas por el usuario.
function fetch_pending_requests($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT a.*, u.nombre, u.apellidos, u.ciudad
         FROM amistad a
         INNER JOIN usuario u ON u.id_usuario = a.usuario_origen
         WHERE a.usuario_destino = ? AND a.estado = ?
         ORDER BY a.fecha_solicitud DESC'
    );
    $stmt->execute([$userId, 'pendiente']);
    return $stmt->fetchAll();
}

// Lista de amistades aceptadas del usuario.
function fetch_accepted_friends($pdo, $userId)
{
    $stmt = $pdo->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad
         FROM amistad a
         INNER JOIN usuario u
            ON u.id_usuario = CASE
                WHEN a.usuario_origen = ? THEN a.usuario_destino
                ELSE a.usuario_origen
            END
         WHERE (a.usuario_origen = ? OR a.usuario_destino = ?) AND a.estado = ?
         ORDER BY u.nombre'
    );
    $stmt->execute([$userId, $userId, $userId, 'aceptada']);
    return $stmt->fetchAll();
}

// Cambia la contrasena comprobando la actual y la confirmacion.
function change_password($pdo, $userId)
{
    $actual = isset($_POST['contrasena_actual']) ? $_POST['contrasena_actual'] : '';
    $nueva = isset($_POST['contrasena_nueva']) ? $_POST['contrasena_nueva'] : '';
    $confirmar = isset($_POST['contrasena_confirmar']) ? $_POST['contrasena_confirmar'] : '';

    if ($actual === '' || $nueva === '' || $confirmar === '') {
        return ['Todos los campos de contrasena son obligatorios.'];
    }

    if (!validate_password_length($nueva)) {
        return ['La nueva contrasena debe tener al menos 8 caracteres.'];
    }

    if ($nueva !== $confirmar) {
        return ['Las contrasenas nuevas no coinciden.'];
    }

    $stmt = $pdo->prepare('SELECT contrasena FROM usuario WHERE id_usuario = ?');
    $stmt->execute([$userId]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($actual, $usuario['contrasena'])) {
        return ['La contrasena actual no es correcta.'];
    }

    $stmt = $pdo->prepare('UPDATE usuario SET contrasena = ? WHERE id_usuario = ?');
    $stmt->execute([password_hash($nueva, PASSWORD_DEFAULT), $userId]);

    flash('success', 'Contrasena cambiada correctamente.');
    redirect_to('profile');
}

// Datos publicos de un usuario (sin el correo) para ver su perfil.
function fetch_public_user_profile($pdo, $profileUserId)
{
    $stmt = $pdo->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad, r.nombre AS rol,
                GROUP_CONCAT(DISTINCT i.nombre ORDER BY i.nombre SEPARATOR ", ") AS intereses
         FROM usuario u
         INNER JOIN rol r ON r.id_rol = u.id_rol
         LEFT JOIN preferencia p ON p.id_usuario = u.id_usuario
         LEFT JOIN interes i ON i.id_interes = p.id_interes
         WHERE u.id_usuario = ?
         GROUP BY u.id_usuario, u.nombre, u.apellidos, u.ciudad, r.nombre'
    );
    $stmt->execute([$profileUserId]);
    $perfil = $stmt->fetch();
    return $perfil ? $perfil : null;
}
