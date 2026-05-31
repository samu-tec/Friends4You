<?php
/**
 * Controlador de la zona del colaborador.
 *
 * El colaborador representa a un local o negocio (no a una persona) y
 * desde esta sección gestiona la ficha de su establecimiento y crea los
 * eventos asociados a su local. No tiene intereses, ni amistades, ni se
 * apunta a eventos.
 */

/**
 * Página /collaborator: ficha del local y eventos asociados.
 *
 * Exige rol "colaborador". Si llega una petición POST decide qué acción
 * ejecutar según el campo oculto "accion": actualizar los datos del
 * establecimiento o crear un evento del local.
 *
 * @return void
 */
function controlador_colaborador()
{
    exigir_rol('colaborador');

    $bd = obtener_bd();
    $id_usuario = id_usuario_actual();
    $errores = [];

    if (es_post()) {
        try {
            $accion = isset($_POST['accion']) ? $_POST['accion'] : '';

            if ($accion === 'actualizar_colaborador') {
                $errores = actualizar_perfil_colaborador($bd, $id_usuario);
            }
            if ($accion === 'crear_evento_colaborador') {
                crear_evento_colaborador($bd, $id_usuario);
            }
        } catch (PDOException $excepcion) {
            $errores[] = 'No se han podido guardar los datos del colaborador.';
        }
    }

    $colaborador = buscar_colaborador_por_usuario($bd, $id_usuario);

    mostrar_vista('collaborator', [
        'errores' => $errores,
        'colaborador' => $colaborador,
        'intereses' => obtener_intereses($bd),
        'eventos' => $colaborador ? obtener_eventos_colaborador($bd, (int) $colaborador['id_colaborador']) : [],
    ]);
}

/**
 * Crea o actualiza la ficha del establecimiento del colaborador.
 *
 * Si todavía no había ficha asociada a la cuenta, hace un INSERT; si ya
 * existía, hace un UPDATE. Así la misma acción del formulario sirve para
 * crear y editar.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id de la cuenta de colaborador conectada.
 * @return array Lista de errores (vacía si todo va bien y redirige).
 */
function actualizar_perfil_colaborador($bd, $id_usuario)
{
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $direccion = trim(isset($_POST['direccion']) ? $_POST['direccion'] : '');
    $ciudad = trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');
    $descripcion = trim(isset($_POST['descripcion']) ? $_POST['descripcion'] : '');

    $errores = validar_campos_obligatorios($_POST, [
        'nombre' => 'nombre',
        'direccion' => 'direccion',
        'ciudad' => 'ciudad',
    ]);

    if ($errores) {
        return $errores;
    }

    $colaborador = buscar_colaborador_por_usuario($bd, $id_usuario);

    if ($colaborador) {
        $consulta = $bd->prepare(
            'UPDATE colaborador
             SET nombre = ?, direccion = ?, ciudad = ?, descripcion = ?
             WHERE id_usuario_colaborador = ?'
        );
        $consulta->execute([$nombre, $direccion, $ciudad, $descripcion, $id_usuario]);
    } else {
        $consulta = $bd->prepare(
            'INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
             VALUES (?, ?, ?, ?, ?)'
        );
        $consulta->execute([$nombre, $direccion, $ciudad, $descripcion, $id_usuario]);
    }

    aviso('success', 'Datos del establecimiento guardados.');
    redirigir('collaborator');
}

/**
 * Crea un evento en nombre del colaborador, vinculado a su establecimiento.
 *
 * Reutiliza crear_evento para no duplicar la validación y la inserción. La
 * función recoger_datos_evento ya se encarga de fijar id_colaborador al
 * del establecimiento del propio usuario cuando el rol es "colaborador",
 * así que no hace falta hacer nada especial aquí.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id de la cuenta de colaborador conectada.
 * @return void
 */
function crear_evento_colaborador($bd, $id_usuario)
{
    $colaborador = buscar_colaborador_por_usuario($bd, $id_usuario);

    if (!$colaborador) {
        aviso('error', 'Primero debes guardar los datos del establecimiento.');
        return;
    }

    crear_evento($bd, $id_usuario);
    redirigir('collaborator');
}

/**
 * Devuelve la ficha del colaborador asociada a una cuenta, o null.
 *
 * Cada cuenta con rol "colaborador" tiene como mucho una ficha asociada
 * (la columna id_usuario_colaborador es UNIQUE en la tabla colaborador).
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_usuario Id de la cuenta de usuario.
 * @return array|null Datos del colaborador o null si la cuenta no tiene ficha.
 */
function buscar_colaborador_por_usuario($bd, $id_usuario)
{
    $consulta = $bd->prepare('SELECT * FROM colaborador WHERE id_usuario_colaborador = ? LIMIT 1');
    $consulta->execute([$id_usuario]);
    $colaborador = $consulta->fetch();
    return $colaborador ? $colaborador : null;
}

/**
 * Devuelve los eventos asociados al establecimiento de un colaborador.
 *
 * @param PDO $bd Conexión a la base de datos.
 * @param int $id_colaborador Id del colaborador (no del usuario).
 * @return array Lista de eventos del local ordenados por fecha descendente.
 */
function obtener_eventos_colaborador($bd, $id_colaborador)
{
    $consulta = $bd->prepare(
        'SELECT e.*, i.nombre AS interes
         FROM evento e, interes i
         WHERE i.id_interes = e.id_interes
           AND e.id_colaborador = ?
         ORDER BY e.fecha_hora DESC'
    );
    $consulta->execute([$id_colaborador]);
    return $consulta->fetchAll();
}
