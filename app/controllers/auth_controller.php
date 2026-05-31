<?php
/**
 * Controlador de acceso: registro, inicio y cierre de sesión.
 *
 * Aquí van las tres acciones de la página /access (mostrar formularios,
 * procesar login y procesar registro) y la acción de /logout. Todo el
 * acceso a la base de datos se hace mediante PDO con sentencias preparadas.
 */

/**
 * Página /access: muestra los formularios y procesa login o registro.
 *
 * Si ya hay sesión iniciada y la petición no es un envío de formulario,
 * redirige directamente al perfil. Si llega por POST, decide qué acción
 * procesar a partir del campo oculto "accion" del formulario.
 *
 * @return void
 */
function controlador_acceso()
{
    if (hay_sesion() && !es_post()) {
        redirigir('profile');
    }

    $errores = [];
    $formulario_activo = 'iniciar_sesion';

    if (es_post()) {
        $accion = isset($_POST['accion']) ? $_POST['accion'] : '';
        $formulario_activo = $accion === 'registrar' ? 'registrar' : 'iniciar_sesion';

        try {
            if ($accion === 'iniciar_sesion') {
                $errores = procesar_inicio_sesion();
            }
            if ($accion === 'registrar') {
                $errores = procesar_registro();
            }
        } catch (PDOException $excepcion) {
            $errores[] = 'No se ha podido completar la operación. Revisa la base de datos.';
        }
    }

    mostrar_vista('access', [
        'errores' => $errores,
        'formulario_activo' => $formulario_activo,
    ]);
}

/**
 * Procesa el inicio de sesión.
 *
 * Valida los campos, busca al usuario por correo y comprueba la contraseña
 * con password_verify. Si todo es correcto, inicia la sesión y redirige al
 * perfil; si no, devuelve la lista de errores para mostrarlos en la vista.
 *
 * @return array Lista de errores (vacía si el login es correcto y redirige).
 */
function procesar_inicio_sesion()
{
    $correo = trim(isset($_POST['correo']) ? $_POST['correo'] : '');
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    $errores = validar_campos_obligatorios($_POST, [
        'correo' => 'correo',
        'contrasena' => 'contraseña',
    ]);

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato válido.';
    }

    if ($errores) {
        return $errores;
    }

    $bd = obtener_bd();
    $consulta = $bd->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND u.correo = ?
         LIMIT 1'
    );
    $consulta->execute([$correo]);
    $usuario = $consulta->fetch();

    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        return ['Correo o contraseña incorrectos.'];
    }

    iniciar_sesion_usuario($usuario);
    aviso('success', 'Has iniciado sesión correctamente.');
    redirigir('profile');
}

/**
 * Procesa el registro de un nuevo usuario.
 *
 * Valida los campos obligatorios, el formato del correo y la longitud de
 * la contraseña. Comprueba que el correo no esté ya en uso. Inserta el
 * usuario con rol "usuario" (las cuentas de administrador y colaborador
 * solo se crean desde la base de datos o desde el panel de admin) y
 * arranca su sesión.
 *
 * @return array Lista de errores (vacía si el registro es correcto y redirige).
 */
function procesar_registro()
{
    $nombre = trim(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $apellidos = trim(isset($_POST['apellidos']) ? $_POST['apellidos'] : '');
    $correo = trim(isset($_POST['correo']) ? $_POST['correo'] : '');
    $ciudad = trim(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    $errores = validar_campos_obligatorios($_POST, [
        'nombre' => 'nombre',
        'apellidos' => 'apellidos',
        'correo' => 'correo',
        'ciudad' => 'ciudad',
        'contrasena' => 'contraseña',
    ]);

    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo no tiene un formato válido.';
    }

    if ($contrasena !== '' && strlen($contrasena) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if ($errores) {
        return $errores;
    }

    $bd = obtener_bd();

    $consulta = $bd->prepare('SELECT COUNT(*) FROM usuario WHERE correo = ?');
    $consulta->execute([$correo]);
    if ((int) $consulta->fetchColumn() > 0) {
        return ['Ya existe una cuenta con ese correo.'];
    }

    $consulta = $bd->prepare('SELECT id_rol FROM rol WHERE nombre = ? LIMIT 1');
    $consulta->execute(['usuario']);
    $id_rol = $consulta->fetchColumn();

    if (!$id_rol) {
        return ['No existe el rol usuario. Importa primero los datos iniciales.'];
    }

    $consulta = $bd->prepare(
        'INSERT INTO usuario (nombre, apellidos, correo, contrasena, ciudad, id_rol)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $consulta->execute([
        $nombre,
        $apellidos,
        $correo,
        password_hash($contrasena, PASSWORD_DEFAULT),
        $ciudad,
        (int) $id_rol,
    ]);

    $id_nuevo = (int) $bd->lastInsertId();
    $consulta = $bd->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u, rol r
         WHERE r.id_rol = u.id_rol
           AND u.id_usuario = ?'
    );
    $consulta->execute([$id_nuevo]);
    $usuario = $consulta->fetch();

    iniciar_sesion_usuario($usuario);
    aviso('success', 'Cuenta creada correctamente. ¡Bienvenido!');
    redirigir('profile');
}

/**
 * Página /logout: cierra la sesión y vuelve al inicio.
 *
 * @return void
 */
function controlador_salir()
{
    cerrar_sesion_usuario();
    redirigir('home');
}
