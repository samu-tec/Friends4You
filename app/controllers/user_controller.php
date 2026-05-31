<?php
/**
 * Controlador del perfil del usuario y de la búsqueda de personas.
 *
 * Aquí están las acciones de la página /profile (editar datos, intereses
 * y contraseña) y de la página /users (búsqueda de personas, solicitudes
 * de amistad y ver el perfil público). Las amistades son siempre entre
 * cuentas con rol "usuario": ni administradores ni colaboradores pueden
 * enviar ni recibir solicitudes de amistad.
 */

/**
 * Página /profile: editar el perfil, los intereses y la contraseña.
 *
 * Exige sesión iniciada. Si llega por POST, decide qué acción procesar
 * según el campo oculto "accion" del formulario. Después renderiza la
 * vista con los datos del usuario.
 *
 * @return void
 */
function controlador_perfil()
{
    exigir_sesion();

    $bd = obtener_bd();
    $errores = [];
    $id_usuario = id_usuario_actual();

    if (es_post()) {
        $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

        try {
            if ($accion === 'actualizar_perfil') {
                $errores = actualizar_perfil($bd, $id_usuario);
            }
            // Los intereses solo los tiene el rol "usuario" (las personas).
            if ($accion === 'actualizar_intereses' && tiene_rol('usuario')) {
                actualizar_intereses_usuario($bd, $id_usuario);
            }
            if ($accion === 'crear_interes' && tiene_rol('usuario')) {
                $errores = crear_interes_desde_perfil($bd, $id_usuario);
            }
            if ($accion === 'cambiar_contrasena') {
                $errores = cambiar_contrasena($bd, $id_usuario);
            }
        } catch (PDOException $excepcion) {
            $errores[] = 'No se han podido guardar los cambios.';
        }
    }

    // La lista de intereses, los intereses marcados y las estadísticas
    // solo las pinta la vista para el rol "usuario"; para admin y colaborador
    // no hace falta consultarlas (ahorramos 3 consultas innecesarias).
    mostrar_vista('profile', [
        'errores' => $errores,
        'perfil' => obtener_perfil_usuario($bd, $id_usuario),
        'intereses' => tiene_rol('usuario') ? obtener_intereses($bd) : [],
        'intereses_seleccionados' => tiene_rol('usuario') ? obtener_ids_intereses_usuario($bd, $id_usuario) : [],
        'estadisticas' => tiene_rol('usuario') ? obtener_estadisticas_usuario($bd, $id_usuario) : [],
    ]);
}

/**
 * Actualiza el nombre, los apellidos y la ciudad del usuario.
 *
 * Valida que los tres campos están rellenos. Si hay errores los devuelve
 * para mostrarlos en la vista; si todo está bien, guarda los cambios,
 * actualiza también el nombre en la sesión y redirige al perfil.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Lista de errores (vacía si todo va bien y redirige).
 */
function actualizar_perfil($bd, $id_usuario)
{
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $apellidos = trim(isset($_POST['apellidos']) ? $_POST['apellidos'] : '');
    $ciudad = trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');

    $errores = validar_campos_obligatorios($_POST, [
        'nombre' => 'nombre',
        'apellidos' => 'apellidos',
        'ciudad' => 'ciudad',
    ]);

    if ($errores) {
        return $errores;
    }

    $consulta = $bd->prepare(
        'UPDATE usuario SET nombre = ?, apellidos = ?, ciudad = ? WHERE id_usuario = ?'
    );
    $consulta->execute([$nombre, $apellidos, $ciudad, $id_usuario]);

    $_SESSION['usuario']['nombre'] = $nombre;
    aviso('success', 'Perfil actualizado correctamente.');
    redirigir('profile');
}

/**
 * Sustituye los intereses del usuario por los marcados en el formulario.
 *
 * Borra todas las preferencias actuales y vuelve a insertar las nuevas.
 * Se hace dentro de una transacción para que, si algo falla, no quede el
 * usuario sin intereses a medio camino.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return void
 */
function actualizar_intereses_usuario($bd, $id_usuario)
{
    $ids_intereses = [];
    $enviados = isset($_POST['intereses']) ? $_POST['intereses'] : [];
    foreach ($enviados as $id) {
        $id = (int) $id;
        if ($id > 0) {
            $ids_intereses[] = $id;
        }
    }

    $bd->beginTransaction();

    $consulta = $bd->prepare('DELETE FROM preferencia WHERE id_usuario = ?');
    $consulta->execute([$id_usuario]);

    if ($ids_intereses) {
        $consulta = $bd->prepare(
            'INSERT INTO preferencia (id_usuario, id_interes) VALUES (?, ?)'
        );
        foreach (array_unique($ids_intereses) as $id_interes) {
            $consulta->execute([$id_usuario, $id_interes]);
        }
    }

    $bd->commit();
    aviso('success', 'Intereses actualizados correctamente.');
    redirigir('profile');
}

/**
 * Crea un interés nuevo desde el perfil y lo añade a las preferencias.
 *
 * Si ya existe un interés con el mismo nombre, no lo duplica: se reutiliza
 * el existente y se asocia al usuario. Antes de insertar la preferencia se
 * comprueba que no exista ya para no chocar con la clave primaria compuesta.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Lista de errores (vacía si todo va bien y redirige).
 */
function crear_interes_desde_perfil($bd, $id_usuario)
{
    $nombre = trim(isset($_POST['nombre_interes']) ? $_POST['nombre_interes'] : '');

    if ($nombre === '') {
        return ['El nombre del nuevo interés es obligatorio.'];
    }

    if (strlen($nombre) > 100) {
        return ['El nombre del interés no puede superar los 100 caracteres.'];
    }

    // Si ya existe un interés con ese nombre, reutilizamos su id; si no,
    // lo creamos.
    $consulta = $bd->prepare('SELECT id_interes FROM interes WHERE nombre = ? LIMIT 1');
    $consulta->execute([$nombre]);
    $id_interes = $consulta->fetchColumn();

    if (!$id_interes) {
        $consulta = $bd->prepare('INSERT INTO interes (nombre) VALUES (?)');
        $consulta->execute([$nombre]);
        $id_interes = (int) $bd->lastInsertId();
    }

    // Antes de añadir la preferencia, comprobamos que no la tenga ya: si la
    // tuviera, intentar insertarla rompería la clave primaria compuesta.
    $consulta = $bd->prepare(
        'SELECT COUNT(*) FROM preferencia WHERE id_usuario = ? AND id_interes = ?'
    );
    $consulta->execute([$id_usuario, (int) $id_interes]);

    if ((int) $consulta->fetchColumn() === 0) {
        $consulta = $bd->prepare(
            'INSERT INTO preferencia (id_usuario, id_interes) VALUES (?, ?)'
        );
        $consulta->execute([$id_usuario, (int) $id_interes]);
    }

    aviso('success', 'Interés creado y añadido a tu perfil.');
    redirigir('profile');
}

/**
 * Página /users: búsqueda de personas, solicitudes y perfil público.
 *
 * Solo accesible para el rol "usuario": las amistades son cosa de personas,
 * no de administradores (cuentas de supervisión) ni de colaboradores
 * (que representan un local, no a una persona física).
 *
 * @return void
 */
function controlador_usuarios()
{
    exigir_rol('usuario');

    $bd = obtener_bd();
    $id_usuario = id_usuario_actual();

    if (es_post()) {
        try {
            procesar_accion_amistad($bd, $id_usuario);
        } catch (PDOException $excepcion) {
            aviso('error', 'No se ha podido completar la operación de amistad.');
            redirigir('users');
        }
    }

    $ciudad = trim(isset($_GET['ciudad']) ? $_GET['ciudad'] : '');
    $id_interes = (int) (isset($_GET['id_interes']) ? $_GET['id_interes'] : 0);
    $id_usuario_perfil = (int) (isset($_GET['id_usuario']) ? $_GET['id_usuario'] : 0);

    mostrar_vista('users', [
        'intereses' => obtener_intereses($bd),
        'ciudad_busqueda' => $ciudad,
        'interes_busqueda' => $id_interes,
        'usuarios' => buscar_usuarios($bd, $id_usuario, $ciudad, $id_interes),
        'estado_amistad' => obtener_estado_amistad($bd, $id_usuario),
        'solicitudes_pendientes' => obtener_solicitudes_pendientes($bd, $id_usuario),
        'amigos' => obtener_amigos_aceptados($bd, $id_usuario),
        'perfil_publico' => $id_usuario_perfil > 0 ? obtener_perfil_publico($bd, $id_usuario_perfil) : null,
    ]);
}

/**
 * Procesa la acción POST recibida en la página /users.
 *
 * Mira el campo "accion" del formulario y llama a la función que toca
 * (enviar solicitud o responder a una solicitud recibida). Después
 * redirige a /users para que el listado se vea actualizado.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return void
 */
function procesar_accion_amistad($bd, $id_usuario)
{
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion === 'enviar_solicitud_amistad') {
        $id_destinatario = (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0);
        enviar_solicitud_amistad($bd, $id_usuario, $id_destinatario);
    }

    if ($accion === 'responder_solicitud_amistad') {
        $id_remitente = (int) (isset($_POST['usuario_origen']) ? $_POST['usuario_origen'] : 0);
        $respuesta = isset($_POST['respuesta']) ? $_POST['respuesta'] : 'rechazada';
        responder_solicitud_amistad($bd, $id_usuario, $id_remitente, $respuesta);
    }

    redirigir('users');
}

/**
 * Envía una solicitud de amistad evitando duplicados y auto-solicitudes.
 *
 * Solo se pueden enviar solicitudes a otras cuentas con rol "usuario";
 * los colaboradores y administradores no participan en amistades. También
 * se evita enviar dos veces la misma solicitud o repetirla con la amistad
 * ya aceptada.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que envía la solicitud.
 * @param int $id_destinatario Id del usuario destinatario.
 * @return void
 */
function enviar_solicitud_amistad($bd, $id_usuario, $id_destinatario)
{
    if ($id_destinatario <= 0 || $id_destinatario === $id_usuario) {
        aviso('error', 'No puedes enviarte una solicitud a ti mismo.');
        return;
    }

    // Solo se pueden enviar solicitudes a usuarios normales (las personas),
    // no a cuentas de colaborador (locales) ni de administrador.
    $consulta = $bd->prepare(
        'SELECT COUNT(*)
         FROM usuario u, rol r
         WHERE u.id_rol = r.id_rol
           AND u.id_usuario = ?
           AND r.nombre = ?'
    );
    $consulta->execute([$id_destinatario, 'usuario']);
    if ((int) $consulta->fetchColumn() === 0) {
        aviso('error', 'Solo puedes enviar solicitudes de amistad a usuarios.');
        return;
    }

    $consulta = $bd->prepare(
        'SELECT estado FROM amistad
         WHERE (usuario_origen = ? AND usuario_destino = ?)
            OR (usuario_origen = ? AND usuario_destino = ?)
         LIMIT 1'
    );
    $consulta->execute([$id_usuario, $id_destinatario, $id_destinatario, $id_usuario]);

    if ($consulta->fetch()) {
        aviso('info', 'Ya existe una solicitud o amistad con ese usuario.');
        return;
    }

    $consulta = $bd->prepare(
        'INSERT INTO amistad (usuario_origen, usuario_destino, estado) VALUES (?, ?, ?)'
    );
    $consulta->execute([$id_usuario, $id_destinatario, 'pendiente']);
    aviso('success', 'Solicitud de amistad enviada.');
}

/**
 * Acepta o rechaza una solicitud de amistad recibida.
 *
 * Solo cambia el estado si la solicitud existe y está en "pendiente".
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que responde (destinatario).
 * @param int $id_remitente Id del usuario que envió la solicitud.
 * @param string $respuesta "aceptada" o cualquier otro valor (rechazo).
 * @return void
 */
function responder_solicitud_amistad($bd, $id_usuario, $id_remitente, $respuesta)
{
    $estado = $respuesta === 'aceptada' ? 'aceptada' : 'rechazada';
    $consulta = $bd->prepare(
        'UPDATE amistad SET estado = ?
         WHERE usuario_origen = ? AND usuario_destino = ? AND estado = ?'
    );
    $consulta->execute([$estado, $id_remitente, $id_usuario, 'pendiente']);

    aviso('success', $estado === 'aceptada' ? 'Solicitud aceptada.' : 'Solicitud rechazada.');
}

/**
 * Devuelve estadísticas del usuario para el resumen del perfil.
 *
 * Cuenta amigos aceptados, eventos creados, asistencias confirmadas y
 * cantidad de intereses marcados.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Array asociativo con "amigos", "eventos_creados", "eventos_asistidos" e "intereses".
 */
function obtener_estadisticas_usuario($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT COUNT(*) FROM amistad
         WHERE estado = ? AND (usuario_origen = ? OR usuario_destino = ?)'
    );
    $consulta->execute(['aceptada', $id_usuario, $id_usuario]);
    $amigos = (int) $consulta->fetchColumn();

    $consulta = $bd->prepare('SELECT COUNT(*) FROM evento WHERE id_creador = ?');
    $consulta->execute([$id_usuario]);
    $eventos_creados = (int) $consulta->fetchColumn();

    $consulta = $bd->prepare(
        'SELECT COUNT(*) FROM asistencia WHERE id_usuario = ? AND estado_asistencia = ?'
    );
    $consulta->execute([$id_usuario, 'confirmada']);
    $eventos_asistidos = (int) $consulta->fetchColumn();

    $consulta = $bd->prepare('SELECT COUNT(*) FROM preferencia WHERE id_usuario = ?');
    $consulta->execute([$id_usuario]);
    $total_intereses = (int) $consulta->fetchColumn();

    return [
        'amigos' => $amigos,
        'eventos_creados' => $eventos_creados,
        'eventos_asistidos' => $eventos_asistidos,
        'intereses' => $total_intereses,
    ];
}

/**
 * Devuelve los datos del usuario junto con el nombre de su rol.
 *
 * Se usa en la cabecera del perfil (badge del rol, ciudad, fecha de
 * registro...). Si no se encuentra el usuario devuelve array vacío para
 * que la vista no rompa al acceder a las claves.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario.
 * @return array Datos del usuario (con "rol") o array vacío si no existe.
 */
function obtener_perfil_usuario($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND u.id_usuario = ?'
    );
    $consulta->execute([$id_usuario]);
    $usuario = $consulta->fetch();
    return $usuario ? $usuario : [];
}

/**
 * Devuelve todos los intereses ordenados por nombre.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de intereses (id_interes y nombre).
 */
function obtener_intereses($bd)
{
    $consulta = $bd->query('SELECT * FROM interes ORDER BY nombre');
    return $consulta->fetchAll();
}

/**
 * Devuelve los ids de los intereses marcados por el usuario.
 *
 * Se usan para marcar los checkboxes correctos en la vista del perfil.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario.
 * @return int[] Lista de ids de intereses.
 */
function obtener_ids_intereses_usuario($bd, $id_usuario)
{
    $consulta = $bd->prepare('SELECT id_interes FROM preferencia WHERE id_usuario = ?');
    $consulta->execute([$id_usuario]);
    $ids = [];
    foreach ($consulta->fetchAll(PDO::FETCH_COLUMN) as $id) {
        $ids[] = (int) $id;
    }
    return $ids;
}

/**
 * Busca usuarios filtrando por ciudad e interés.
 *
 * Excluye al propio usuario y solo devuelve cuentas con rol "usuario":
 * los colaboradores (locales) y los administradores no aparecen en la
 * búsqueda de personas. El filtro de ciudad usa LIKE para coincidencias
 * parciales (por ejemplo "Mál" encontraría "Málaga").
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario que busca (para excluirlo).
 * @param string $ciudad Filtro por ciudad (opcional, vacío = sin filtro).
 * @param int $id_interes Filtro por interés (0 = sin filtro).
 * @return array Lista de usuarios encontrados con sus intereses concatenados.
 */
function buscar_usuarios($bd, $id_usuario, $ciudad, $id_interes)
{
    // Si hay filtro de interés añadimos la tabla "preferencia" al FROM y
    // usamos DISTINCT para no repetir filas; si no, consulta directa.
    if ($id_interes > 0) {
        $sql = 'SELECT DISTINCT u.id_usuario, u.nombre, u.apellidos, u.ciudad
                FROM usuario u, rol r, preferencia p
                WHERE r.id_rol = u.id_rol
                  AND r.nombre = ?
                  AND u.id_usuario <> ?
                  AND p.id_usuario = u.id_usuario
                  AND p.id_interes = ?';
        $parametros = ['usuario', $id_usuario, $id_interes];
    } else {
        $sql = 'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad
                FROM usuario u, rol r
                WHERE r.id_rol = u.id_rol
                  AND r.nombre = ?
                  AND u.id_usuario <> ?';
        $parametros = ['usuario', $id_usuario];
    }

    // El filtro de ciudad se añade igual en los dos casos.
    if ($ciudad !== '') {
        $sql .= ' AND u.ciudad LIKE ?';
        $parametros[] = '%' . $ciudad . '%';
    }

    $sql .= ' ORDER BY u.ciudad, u.nombre';

    $consulta = $bd->prepare($sql);
    $consulta->execute($parametros);
    $usuarios = $consulta->fetchAll();

    // Para cada usuario buscamos sus intereses con una consulta aparte y los
    // unimos en una cadena separada por comas. Es una consulta más por
    // usuario, pero la búsqueda devolverá pocos resultados y se entiende
    // mejor que meter todo en una sola consulta con GROUP_CONCAT.
    foreach ($usuarios as $clave => $usuario) {
        $usuarios[$clave]['intereses'] = obtener_intereses_texto($bd, (int) $usuario['id_usuario']);
    }

    return $usuarios;
}

/**
 * Devuelve los intereses de un usuario en una cadena "Cine, Música, ...".
 *
 * Si no tiene intereses devuelve la cadena vacía.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario del que se quieren los intereses.
 * @return string Intereses separados por coma y espacio.
 */
function obtener_intereses_texto($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT i.nombre
         FROM preferencia p, interes i
         WHERE p.id_interes = i.id_interes
           AND p.id_usuario = ?
         ORDER BY i.nombre'
    );
    $consulta->execute([$id_usuario]);
    $nombres = $consulta->fetchAll(PDO::FETCH_COLUMN);
    return implode(', ', $nombres);
}

/**
 * Devuelve el estado de amistad del usuario con el resto, indexado por id.
 *
 * Recupera todas las filas de amistad donde participa el usuario (como
 * origen o como destino) y, para cada una, calcula con un if cuál es
 * "el otro usuario" (el que NO soy yo). El resultado se mete en un array
 * cuya clave es ese id, así la vista puede saber rápidamente si una
 * persona ya tiene solicitud pendiente, amistad aceptada o rechazada.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Mapa "id_otro_usuario" => ["estado" => ..., "usuario_origen" => ...].
 */
function obtener_estado_amistad($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT usuario_origen, usuario_destino, estado
         FROM amistad
         WHERE usuario_origen = ? OR usuario_destino = ?'
    );
    $consulta->execute([$id_usuario, $id_usuario]);

    $estados = [];
    foreach ($consulta->fetchAll() as $fila) {
        // Calculamos el id del otro usuario con un if sencillo:
        // si yo soy el origen, el otro es el destino; en caso contrario, al revés.
        if ((int) $fila['usuario_origen'] === $id_usuario) {
            $id_otro = (int) $fila['usuario_destino'];
        } else {
            $id_otro = (int) $fila['usuario_origen'];
        }

        $estados[$id_otro] = [
            'estado' => $fila['estado'],
            'usuario_origen' => (int) $fila['usuario_origen'],
        ];
    }

    return $estados;
}

/**
 * Devuelve las solicitudes de amistad pendientes recibidas por el usuario.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Lista de solicitudes con datos del usuario que las envió.
 */
function obtener_solicitudes_pendientes($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT a.*, u.nombre, u.apellidos, u.ciudad
         FROM amistad a, usuario u
         WHERE u.id_usuario = a.usuario_origen
           AND a.usuario_destino = ? AND a.estado = ?
         ORDER BY a.fecha_solicitud DESC'
    );
    $consulta->execute([$id_usuario, 'pendiente']);
    return $consulta->fetchAll();
}

/**
 * Devuelve la lista de amistades aceptadas del usuario.
 *
 * Funciona en los dos sentidos: el usuario puede haber sido el que envió
 * la solicitud (usuario_origen) o el que la recibió (usuario_destino).
 * La condición OR del WHERE expresa exactamente esas dos posibilidades:
 *   - Yo soy el origen y el otro usuario es el destino, o
 *   - yo soy el destino y el otro usuario es el origen.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Lista de amigos con id, nombre, apellidos y ciudad.
 */
function obtener_amigos_aceptados($bd, $id_usuario)
{
    $consulta = $bd->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad
         FROM amistad a, usuario u
         WHERE a.estado = ?
           AND (
                (a.usuario_origen = ?  AND u.id_usuario = a.usuario_destino)
             OR (a.usuario_destino = ? AND u.id_usuario = a.usuario_origen)
           )
         ORDER BY u.nombre'
    );
    $consulta->execute(['aceptada', $id_usuario, $id_usuario]);
    return $consulta->fetchAll();
}

/**
 * Cambia la contraseña del usuario tras comprobar la actual.
 *
 * Pide la contraseña actual (verificada con password_verify), la nueva y
 * una confirmación. Si todo es correcto, guarda el nuevo hash con
 * password_hash y redirige al perfil.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id del usuario conectado.
 * @return array Lista de errores (vacía si todo va bien y redirige).
 */
function cambiar_contrasena($bd, $id_usuario)
{
    $actual = isset($_POST['contrasena_actual']) ? $_POST['contrasena_actual'] : '';
    $nueva = isset($_POST['contrasena_nueva']) ? $_POST['contrasena_nueva'] : '';
    $confirmacion = isset($_POST['contrasena_confirmar']) ? $_POST['contrasena_confirmar'] : '';

    if ($actual === '' || $nueva === '' || $confirmacion === '') {
        return ['Todos los campos de contraseña son obligatorios.'];
    }

    if (strlen($nueva) < 8) {
        return ['La nueva contraseña debe tener al menos 8 caracteres.'];
    }

    if ($nueva !== $confirmacion) {
        return ['Las contraseñas nuevas no coinciden.'];
    }

    $consulta = $bd->prepare('SELECT contrasena FROM usuario WHERE id_usuario = ?');
    $consulta->execute([$id_usuario]);
    $usuario = $consulta->fetch();

    if (!$usuario || !password_verify($actual, $usuario['contrasena'])) {
        return ['La contraseña actual no es correcta.'];
    }

    $consulta = $bd->prepare('UPDATE usuario SET contrasena = ? WHERE id_usuario = ?');
    $consulta->execute([password_hash($nueva, PASSWORD_DEFAULT), $id_usuario]);

    aviso('success', 'Contraseña cambiada correctamente.');
    redirigir('profile');
}

/**
 * Devuelve los datos públicos de un usuario para ver su perfil.
 *
 * No incluye el correo (información privada) y solo permite ver perfiles
 * de cuentas con rol "usuario": no se puede curiosear el perfil de un
 * colaborador (que es un local) ni de un administrador.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario_perfil Id del usuario cuyo perfil se quiere ver.
 * @return array|null Datos públicos del usuario, o null si no se puede mostrar.
 */
function obtener_perfil_publico($bd, $id_usuario_perfil)
{
    // Primero los datos básicos. Si no hay usuario o no es del rol "usuario",
    // devolvemos null (no se debe poder cotillear cuentas de colaborador
    // ni de administrador).
    $consulta = $bd->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.ciudad, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND r.nombre = ?
           AND u.id_usuario = ?'
    );
    $consulta->execute(['usuario', $id_usuario_perfil]);
    $perfil = $consulta->fetch();

    if (!$perfil) {
        return null;
    }

    // Aparte añadimos sus intereses concatenados como texto (misma forma que
    // en la búsqueda, para que la vista los pinte igual).
    $perfil['intereses'] = obtener_intereses_texto($bd, (int) $perfil['id_usuario']);
    return $perfil;
}
