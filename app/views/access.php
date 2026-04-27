<?php /** Vista de acceso: contiene los formularios de inicio de sesión y de registro. */ ?>
<section class="section">
    <h1>Bienvenido a Friends4You</h1>
    <p class="muted">Inicia sesión con tu cuenta o crea una nueva para empezar a hacer amistades.</p>

    <?php foreach ($errors ?? [] as $error): ?>
        <div class="alert alert--error"><?= e($error) ?></div>
    <?php endforeach; ?>

    <div class="grid two">
        <section class="card">
            <h2>Iniciar sesión</h2>
            <p class="muted">Accede con tu correo y contraseña.</p>
            <form method="post" class="form">
                <input type="hidden" name="action" value="login">

                <div class="field">
                    <label for="login-correo">Correo electrónico</label>
                    <input id="login-correo" type="email" name="correo" required autocomplete="email"
                           value="<?= e($_POST['correo'] ?? '') ?>" placeholder="tucorreo@email.com">
                </div>

                <div class="field">
                    <label for="login-contrasena">Contraseña</label>
                    <input id="login-contrasena" type="password" name="contrasena" required autocomplete="current-password">
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Entrar</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Crear cuenta</h2>
            <p class="muted">Regístrate gratis y empieza a conocer gente.</p>
            <form method="post" class="form">
                <input type="hidden" name="action" value="register">

                <div class="grid two">
                    <div class="field">
                        <label for="reg-nombre">Nombre</label>
                        <input id="reg-nombre" type="text" name="nombre" required autocomplete="given-name"
                               value="<?= e($_POST['nombre'] ?? '') ?>">
                    </div>

                    <div class="field">
                        <label for="reg-apellidos">Apellidos</label>
                        <input id="reg-apellidos" type="text" name="apellidos" required autocomplete="family-name"
                               value="<?= e($_POST['apellidos'] ?? '') ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="reg-correo">Correo electrónico</label>
                    <input id="reg-correo" type="email" name="correo" required autocomplete="email"
                           value="<?= e($_POST['correo'] ?? '') ?>" placeholder="tucorreo@email.com">
                </div>

                <div class="field">
                    <label for="reg-ciudad">Ciudad</label>
                    <input id="reg-ciudad" type="text" name="ciudad" required value="<?= e($_POST['ciudad'] ?? '') ?>"
                           placeholder="Ej. Málaga">
                </div>

                <div class="field">
                    <label for="reg-contrasena">Contraseña <small class="muted">(mínimo 8 caracteres)</small></label>
                    <input id="reg-contrasena" type="password" name="contrasena" minlength="8" required autocomplete="new-password">
                </div>

                <div class="form-actions">
                    <button class="button" type="submit">Crear cuenta</button>
                </div>
            </form>
        </section>
    </div>
</section>
