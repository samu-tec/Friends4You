<?php /** Vista de acceso: contiene los formularios de inicio de sesión y de registro. */ ?>
<?php
// Si el formulario se envió y hubo error, volvemos a mostrar los valores
// introducidos por el usuario (excepto la contraseña) para que no tenga
// que rellenarlos otra vez.
$correo_enviado = isset($_POST['correo']) ? $_POST['correo'] : '';
$nombre_enviado = isset($_POST['nombre']) ? $_POST['nombre'] : '';
$apellidos_enviados = isset($_POST['apellidos']) ? $_POST['apellidos'] : '';
$ciudad_enviada = isset($_POST['ciudad']) ? $_POST['ciudad'] : '';
$lista_errores = isset($errores) ? $errores : [];
?>
<section class="section">
    <h1>Bienvenido a Friends4You</h1>
    <p class="muted">Inicia sesión con tu cuenta o crea una nueva para empezar a hacer amistades.</p>

    <?php foreach ($lista_errores as $error): ?>
        <div class="alert alert--error"><?= escapar($error) ?></div>
    <?php endforeach; ?>

    <div class="grid two">
        <section class="card">
            <h2>Iniciar sesión</h2>
            <p class="muted">Accede con tu correo y contraseña.</p>
            <form method="post" class="form">
                <input type="hidden" name="accion" value="iniciar_sesion">

                <div class="field">
                    <label for="acceso-correo">Correo electrónico</label>
                    <input id="acceso-correo" type="email" name="correo" required
                           value="<?= escapar($correo_enviado) ?>" placeholder="tucorreo@email.com">
                </div>

                <div class="field">
                    <label for="acceso-contrasena">Contraseña</label>
                    <input id="acceso-contrasena" type="password" name="contrasena" required>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Entrar</button>
                </div>
            </form>
            <p class="muted u-mt-md">
                ¿Primera vez? Crea una cuenta en el formulario de al lado.
                Para probar la aplicación, en <strong>Ayuda</strong> tienes
                cuentas de ejemplo.
            </p>
        </section>

        <section class="card">
            <h2>Crear cuenta</h2>
            <p class="muted">Regístrate gratis y empieza a conocer gente.</p>
            <form method="post" class="form">
                <input type="hidden" name="accion" value="registrar">

                <div class="grid two">
                    <div class="field">
                        <label for="registro-nombre">Nombre</label>
                        <input id="registro-nombre" type="text" name="nombre" required
                               value="<?= escapar($nombre_enviado) ?>">
                    </div>

                    <div class="field">
                        <label for="registro-apellidos">Apellidos</label>
                        <input id="registro-apellidos" type="text" name="apellidos" required
                               value="<?= escapar($apellidos_enviados) ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="registro-correo">Correo electrónico</label>
                    <input id="registro-correo" type="email" name="correo" required
                           value="<?= escapar($correo_enviado) ?>" placeholder="tucorreo@email.com">
                </div>

                <div class="field">
                    <label for="registro-ciudad">Ciudad</label>
                    <input id="registro-ciudad" type="text" name="ciudad" required value="<?= escapar($ciudad_enviada) ?>"
                           placeholder="Ej. Almería">
                </div>

                <div class="field">
                    <label for="registro-contrasena">Contraseña <small class="muted">(mínimo 8 caracteres)</small></label>
                    <input id="registro-contrasena" type="password" name="contrasena" minlength="8" required>
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Crear cuenta</button>
                </div>
            </form>
        </section>
    </div>
</section>
