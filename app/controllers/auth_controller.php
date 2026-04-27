<?php
// Controlador de acceso: muestra los formularios de login y registro,
// procesa el envio y cierra la sesion.

// Pagina /access: muestra los formularios y procesa login o registro.
function access_controller()
{
    if (is_logged_in() && !is_post()) {
        redirect_to('profile');
    }

    $errors = [];
    $activeForm = 'login';

    if (is_post()) {
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $activeForm = $action === 'register' ? 'register' : 'login';

        try {
            if ($action === 'login') {
                $errors = handle_login();
            }
            if ($action === 'register') {
                $errors = handle_register();
            }
        } catch (PDOException $e) {
            $errors[] = 'No se ha podido completar la operacion. Revisa la base de datos.';
        }
    }

    render('access', [
        'errors' => $errors,
        'activeForm' => $activeForm,
    ]);
}

// Procesa el inicio de sesion. Devuelve un array de errores;
// si todo va bien, inicia sesion y redirige al perfil.
function handle_login()
{
    $correo = clean_text(isset($_POST['correo']) ? $_POST['correo'] : '');
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    $errors = validate_required_fields($_POST, [
        'correo' => 'correo',
        'contrasena' => 'contrasena',
    ]);

    if ($correo !== '' && !validate_email_address($correo)) {
        $errors[] = 'El correo no tiene un formato valido.';
    }

    if ($errors) {
        return $errors;
    }

    $pdo = get_db();
    $stmt = $pdo->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u
         INNER JOIN rol r ON r.id_rol = u.id_rol
         WHERE u.correo = ?
         LIMIT 1'
    );
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($contrasena, $usuario['contrasena'])) {
        return ['Correo o contrasena incorrectos.'];
    }

    login_user($usuario);
    flash('success', 'Has iniciado sesion correctamente.');
    redirect_to('profile');
}

// Procesa el registro. Crea un usuario con rol "usuario" y le inicia sesion.
function handle_register()
{
    $nombre = clean_text(isset($_POST['nombre']) ? $_POST['nombre'] : '');
    $apellidos = clean_text(isset($_POST['apellidos']) ? $_POST['apellidos'] : '');
    $correo = clean_text(isset($_POST['correo']) ? $_POST['correo'] : '');
    $ciudad = clean_text(isset($_POST['ciudad']) ? $_POST['ciudad'] : '');
    $contrasena = isset($_POST['contrasena']) ? $_POST['contrasena'] : '';

    $errors = validate_required_fields($_POST, [
        'nombre' => 'nombre',
        'apellidos' => 'apellidos',
        'correo' => 'correo',
        'ciudad' => 'ciudad',
        'contrasena' => 'contrasena',
    ]);

    if ($correo !== '' && !validate_email_address($correo)) {
        $errors[] = 'El correo no tiene un formato valido.';
    }

    if ($contrasena !== '' && !validate_password_length($contrasena)) {
        $errors[] = 'La contrasena debe tener al menos 8 caracteres.';
    }

    if ($errors) {
        return $errors;
    }

    $pdo = get_db();

    $stmt = $pdo->prepare('SELECT COUNT(*) FROM usuario WHERE correo = ?');
    $stmt->execute([$correo]);
    if ((int) $stmt->fetchColumn() > 0) {
        return ['Ya existe una cuenta con ese correo.'];
    }

    $stmt = $pdo->prepare('SELECT id_rol FROM rol WHERE nombre = ? LIMIT 1');
    $stmt->execute(['usuario']);
    $idRol = $stmt->fetchColumn();

    if (!$idRol) {
        return ['No existe el rol usuario. Importa primero los datos iniciales.'];
    }

    $stmt = $pdo->prepare(
        'INSERT INTO usuario (nombre, apellidos, correo, contrasena, ciudad, id_rol)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $nombre,
        $apellidos,
        $correo,
        password_hash($contrasena, PASSWORD_DEFAULT),
        $ciudad,
        (int) $idRol,
    ]);

    $nuevoId = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare(
        'SELECT u.*, r.nombre AS rol
         FROM usuario u
         INNER JOIN rol r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = ?'
    );
    $stmt->execute([$nuevoId]);
    $usuario = $stmt->fetch();

    login_user($usuario);
    flash('success', 'Cuenta creada correctamente. Bienvenido!');
    redirect_to('profile');
}

// Pagina /logout: cierra la sesion y vuelve al inicio.
function logout_controller()
{
    logout_user();
    redirect_to('home');
}
