<?php
/**
 * Controlador del panel de administración.
 *
 * Solo accesible para el rol "administrador". Permite gestionar las cuatro
 * grandes entidades de la aplicación (usuarios, intereses, colaboradores
 * y eventos) y consulta los datos para el informe estadístico, que se pinta
 * al final del propio panel.
 */

/**
 * Página /admin: muestra el panel y procesa sus acciones.
 *
 * Exige rol "administrador". Si llega una petición POST se delega en
 * procesar_accion_admin. Después renderiza la vista con los datos
 * necesarios para todas las tablas y para el informe (totales de
 * asistencias, eventos por interés y usuarios por rol).
 *
 * @return void
 */
function controlador_admin()
{
    exigir_rol('administrador');

    $bd = obtener_bd();

    if (es_post()) {
        try {
            procesar_accion_admin($bd);
        } catch (PDOException $excepcion) {
            aviso('error', 'No se ha podido completar la operación de administración.');
            redirigir('admin');
        }
    }

    mostrar_vista('admin', [
        'roles' => obtener_roles($bd),
        'usuarios' => obtener_usuarios_admin($bd),
        'intereses' => obtener_intereses($bd),
        'colaboradores' => obtener_colaboradores_admin($bd),
        'usuarios_sin_colaborador' => obtener_usuarios_sin_colaborador($bd),
        'eventos' => obtener_eventos_admin($bd),
        // Datos del informe estadístico, que se pinta al final del propio panel.
        'total_asistencias_confirmadas' => obtener_total_asistencias_confirmadas($bd),
        'eventos_por_interes' => obtener_eventos_por_interes($bd),
        'usuarios_por_rol' => obtener_usuarios_por_rol($bd),
    ]);
}

/**
 * Procesa la acción POST recibida en el panel de administración.
 *
 * Decide qué función ejecutar según el campo oculto "accion" del
 * formulario y, al terminar, redirige al panel para refrescar la vista.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function procesar_accion_admin($bd)
{
    $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

    if ($accion === 'admin_actualizar_usuario') {
        admin_actualizar_usuario($bd);
    }
    if ($accion === 'admin_agregar_interes') {
        admin_agregar_interes($bd);
    }
    if ($accion === 'admin_eliminar_interes') {
        admin_eliminar_interes($bd);
    }
    if ($accion === 'admin_actualizar_colaborador') {
        admin_actualizar_colaborador($bd);
    }
    if ($accion === 'admin_crear_colaborador') {
        admin_crear_colaborador($bd);
    }
    if ($accion === 'admin_actualizar_estado_evento') {
        admin_actualizar_estado_evento($bd);
    }
    if ($accion === 'admin_eliminar_usuario') {
        admin_eliminar_usuario($bd);
    }
    if ($accion === 'admin_crear_evento') {
        // Reutiliza la misma función que usan usuarios y colaboradores.
        crear_evento($bd, id_usuario_actual());
    }
    if ($accion === 'admin_eliminar_evento') {
        admin_eliminar_evento($bd);
    }

    redirigir('admin');
}

/**
 * Borra un usuario y todos sus datos asociados.
 *
 * Las amistades, preferencias, asistencias, eventos creados y datos de
 * colaborador se borran solos por las claves foráneas ON DELETE CASCADE.
 * El administrador no puede borrarse a sí mismo (para no quedarse fuera
 * de la aplicación).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_eliminar_usuario($bd)
{
    $id_usuario = (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0);

    if ($id_usuario === id_usuario_actual()) {
        aviso('error', 'No puedes eliminar tu propia cuenta de administrador.');
        return;
    }

    $consulta = $bd->prepare('DELETE FROM usuario WHERE id_usuario = ?');
    $consulta->execute([$id_usuario]);
    aviso('success', 'Usuario eliminado.');
}

/**
 * Borra un evento desde el panel de administración.
 *
 * Las asistencias se borran solas por la clave foránea ON DELETE CASCADE.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_eliminar_evento($bd)
{
    $consulta = $bd->prepare('DELETE FROM evento WHERE id_evento = ?');
    $consulta->execute([(int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0)]);
    aviso('success', 'Evento eliminado.');
}

/**
 * Actualiza los datos básicos y el rol de un usuario.
 *
 * El administrador puede cambiar nombre, apellidos, ciudad y rol desde la
 * tabla de gestión. No se puede cambiar el correo desde aquí.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_actualizar_usuario($bd)
{
    $consulta = $bd->prepare(
        'UPDATE usuario SET nombre = ?, apellidos = ?, ciudad = ?, id_rol = ?
         WHERE id_usuario = ?'
    );
    $consulta->execute([
        trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        trim(isset($_POST['apellidos']) ? $_POST['apellidos'] : ''),
        trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        (int) (isset($_POST['id_rol']) ? $_POST['id_rol'] : 0),
        (int) (isset($_POST['id_usuario']) ? $_POST['id_usuario'] : 0),
    ]);

    aviso('success', 'Usuario actualizado.');
}

/**
 * Crea un interés nuevo evitando duplicados por nombre.
 *
 * Valida que el nombre no esté vacío y que no supere los 100 caracteres
 * (límite de la columna en la tabla). Si ya existe un interés con el
 * mismo nombre lo informa al administrador.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_agregar_interes($bd)
{
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');

    if ($nombre === '') {
        aviso('error', 'El nombre del interés es obligatorio.');
        return;
    }

    if (strlen($nombre) > 100) {
        aviso('error', 'El nombre del interés no puede superar los 100 caracteres.');
        return;
    }

    $consulta = $bd->prepare('SELECT COUNT(*) FROM interes WHERE nombre = ?');
    $consulta->execute([$nombre]);

    if ((int) $consulta->fetchColumn() > 0) {
        aviso('info', 'Ese interés ya existe.');
        return;
    }

    $consulta = $bd->prepare('INSERT INTO interes (nombre) VALUES (?)');
    $consulta->execute([$nombre]);
    aviso('success', 'Interés creado.');
}

/**
 * Elimina un interés si no está usado por ningún evento.
 *
 * Si hay eventos que usan ese interés no se borra (para no romper su
 * referencia con la base de datos) y se informa de cuántos eventos lo
 * impiden.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_eliminar_interes($bd)
{
    $id_interes = (int) (isset($_POST['id_interes']) ? $_POST['id_interes'] : 0);

    $consulta = $bd->prepare('SELECT COUNT(*) FROM evento WHERE id_interes = ?');
    $consulta->execute([$id_interes]);
    $total_eventos_con_interes = (int) $consulta->fetchColumn();

    if ($total_eventos_con_interes > 0) {
        aviso('error', 'No se puede eliminar: hay ' . $total_eventos_con_interes . ' evento(s) usando este interés.');
        return;
    }

    $consulta = $bd->prepare('DELETE FROM interes WHERE id_interes = ?');
    $consulta->execute([$id_interes]);
    aviso('success', 'Interés eliminado.');
}

/**
 * Actualiza los datos de un colaborador existente.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_actualizar_colaborador($bd)
{
    $consulta = $bd->prepare(
        'UPDATE colaborador SET nombre = ?, direccion = ?, ciudad = ?, descripcion = ?
         WHERE id_colaborador = ?'
    );
    $consulta->execute([
        trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        trim(isset($_POST['direccion']) ? $_POST['direccion'] : ''),
        trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        (int) (isset($_POST['id_colaborador']) ? $_POST['id_colaborador'] : 0),
    ]);

    aviso('success', 'Colaborador actualizado.');
}

/**
 * Crea una ficha de colaborador asociada a una cuenta con rol "colaborador".
 *
 * El administrador elige a qué cuenta se asocia (de las que tienen rol
 * "colaborador" y aún no tienen ficha) y rellena los datos del local.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_crear_colaborador($bd)
{
    $consulta = $bd->prepare(
        'INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
         VALUES (?, ?, ?, ?, ?)'
    );
    $consulta->execute([
        trim(isset($_POST['nombre']) ? $_POST['nombre'] : ''),
        trim(isset($_POST['direccion']) ? $_POST['direccion'] : ''),
        trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : ''),
        trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : ''),
        (int) (isset($_POST['id_usuario_colaborador']) ? $_POST['id_usuario_colaborador'] : 0),
    ]);

    aviso('success', 'Colaborador creado.');
}

/**
 * Cambia el estado de un evento entre activo, cancelado o finalizado.
 *
 * Si el valor recibido no es uno de los tres permitidos lo deja en
 * "activo" para no romper la integridad de la tabla.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return void
 */
function admin_actualizar_estado_evento($bd)
{
    $estados_validos = ['activo', 'cancelado', 'finalizado'];
    $estado = isset($_POST['estado_evento']) ? $_POST['estado_evento'] : 'activo';

    if (!in_array($estado, $estados_validos)) {
        $estado = 'activo';
    }

    $consulta = $bd->prepare('UPDATE evento SET estado_evento = ? WHERE id_evento = ?');
    $consulta->execute([$estado, (int) (isset($_POST['id_evento']) ? $_POST['id_evento'] : 0)]);
    aviso('success', 'Estado del evento actualizado.');
}

/**
 * Devuelve los roles disponibles en la aplicación.
 *
 * Se usa para pintar el desplegable que permite cambiar el rol de un
 * usuario desde la tabla de administración.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de roles ordenados por id.
 */
function obtener_roles($bd)
{
    return $bd->query('SELECT * FROM rol ORDER BY id_rol')->fetchAll();
}

/**
 * Devuelve todos los usuarios con el nombre de su rol.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de usuarios con la columna extra "rol".
 */
function obtener_usuarios_admin($bd)
{
    $consulta = $bd->query(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
         ORDER BY u.id_usuario'
    );
    return $consulta->fetchAll();
}

/**
 * Devuelve los colaboradores junto con el correo de su cuenta asociada.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de colaboradores con la columna extra "correo".
 */
function obtener_colaboradores_admin($bd)
{
    $consulta = $bd->query(
        'SELECT c.*, u.correo
         FROM colaborador c, usuario u
         WHERE u.id_usuario = c.id_usuario_colaborador
         ORDER BY c.nombre'
    );
    return $consulta->fetchAll();
}

/**
 * Devuelve las cuentas con rol "colaborador" que aún no tienen ficha de local.
 *
 * Se usan para el desplegable del formulario de creación de colaborador:
 * solo se pueden asociar fichas a cuentas con rol "colaborador" que
 * todavía no tengan establecimiento creado.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de usuarios candidatos.
 */
function obtener_usuarios_sin_colaborador($bd)
{
    $consulta = $bd->prepare(
        'SELECT u.id_usuario, u.nombre, u.apellidos, u.correo
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND r.nombre = ?
           AND NOT EXISTS (SELECT 1 FROM colaborador c
                           WHERE c.id_usuario_colaborador = u.id_usuario)
         ORDER BY u.nombre'
    );
    $consulta->execute(['colaborador']);
    return $consulta->fetchAll();
}

/**
 * Devuelve todos los eventos para la tabla de gestión del administrador.
 *
 * Para cada evento se incluye el nombre del interés, el correo del
 * creador y el nombre del colaborador (si lo hay).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista de eventos ordenados por fecha descendente.
 */
function obtener_eventos_admin($bd)
{
    $consulta = $bd->query(
        'SELECT e.*, i.nombre AS interes, u.correo AS creador_correo,
                (SELECT c.nombre FROM colaborador c
                 WHERE c.id_colaborador = e.id_colaborador) AS colaborador_nombre
         FROM evento e, interes i, usuario u
         WHERE i.id_interes = e.id_interes
           AND u.id_usuario = e.id_creador
         ORDER BY e.fecha_hora DESC'
    );
    return $consulta->fetchAll();
}

/**
 * Devuelve el número total de asistencias confirmadas.
 *
 * Se usa en el informe como dato global de participación en eventos.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return int Total de asistencias en estado "confirmada".
 */
function obtener_total_asistencias_confirmadas($bd)
{
    $consulta = $bd->prepare('SELECT COUNT(*) FROM asistencia WHERE estado_asistencia = ?');
    $consulta->execute(['confirmada']);
    return (int) $consulta->fetchColumn();
}

/**
 * Devuelve el total de eventos agrupados por interés.
 *
 * Se usa en el informe para ver qué temáticas tienen más quedadas.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista con "nombre" del interés y "total" de eventos.
 */
function obtener_eventos_por_interes($bd)
{
    $consulta = $bd->query(
        'SELECT i.nombre,
                (SELECT COUNT(*) FROM evento e
                 WHERE e.id_interes = i.id_interes) AS total
         FROM interes i
         ORDER BY total DESC, i.nombre'
    );
    return $consulta->fetchAll();
}

/**
 * Devuelve el total de usuarios agrupados por rol.
 *
 * Se usa en el informe para ver cuántas cuentas hay de cada tipo.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @return array Lista con "nombre" del rol y "total" de usuarios.
 */
function obtener_usuarios_por_rol($bd)
{
    $consulta = $bd->query(
        'SELECT r.nombre,
                (SELECT COUNT(*) FROM usuario u
                 WHERE u.id_rol = r.id_rol) AS total
         FROM rol r
         ORDER BY r.id_rol'
    );
    return $consulta->fetchAll();
}
