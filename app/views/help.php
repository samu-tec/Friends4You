<?php /** Vista de la página de ayuda: qué es la aplicación, usuarios de prueba y guía rápida de uso. */ ?>

<section class="section">
    <h1>Ayuda</h1>
    <p class="muted">
        Friends4You es una aplicación para conocer gente con intereses
        parecidos, hacer amistades y organizar quedadas o eventos. En esta
        página encontrarás cómo usarla y las cuentas de prueba.
    </p>
</section>

<section class="section">
    <h2>Usuarios de prueba</h2>
    <p class="muted">Contraseña inicial de los datos de ejemplo. Si cambias la
        contraseña de una cuenta, aquí se seguirá viendo <code>1234</code>: las
        contraseñas se guardan cifradas y no se pueden mostrar.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Correo</th>
                    <th>Contraseña</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="badge badge--administrador">Administrador</span></td>
                    <td>admin@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge--usuario">Usuario</span></td>
                    <td>lucia@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge--usuario">Usuario</span></td>
                    <td>carlos@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge--usuario">Usuario</span></td>
                    <td>marta@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge--colaborador">Colaborador</span></td>
                    <td>padelclub@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
                <tr>
                    <td><span class="badge badge--colaborador">Colaborador</span></td>
                    <td>cafeteriaplaza@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <h2>Los tres roles</h2>
    <div class="grid three">
        <article class="card">
            <h3><span class="badge badge--usuario">Usuario</span></h3>
            <p class="muted">
                Edita su perfil e intereses, busca personas por ciudad o
                interés, envía y acepta solicitudes de amistad y se apunta
                a eventos.
            </p>
        </article>
        <article class="card">
            <h3><span class="badge badge--colaborador">Colaborador</span></h3>
            <p class="muted">
                Es un <strong>local o negocio</strong>, no una persona. Solo
                gestiona los datos de su establecimiento y crea eventos de su
                local desde su página. No envía solicitudes de amistad ni se
                apunta a eventos.
            </p>
        </article>
        <article class="card">
            <h3><span class="badge badge--administrador">Administrador</span></h3>
            <p class="muted">
                Cuenta de <strong>supervisión</strong>, no una persona: no
                tiene intereses, ni amistades, ni se apunta a eventos. Desde el
                panel gestiona y elimina usuarios, intereses, colaboradores y
                eventos, crea eventos y consulta el informe. Para usar la red
                como persona, hay que usar una cuenta de usuario normal.
            </p>
        </article>
    </div>
</section>

<section class="section">
    <h2>Cómo se usa, paso a paso</h2>
    <div class="grid two">
        <article class="card">
            <h3>Como usuario</h3>
            <ol class="help-steps">
                <li>Crea una cuenta o inicia sesión desde <strong>Acceso</strong>.</li>
                <li>En <strong>Perfil</strong>, completa tu ciudad y marca tus intereses.</li>
                <li>En <strong>Usuarios</strong>, busca gente por ciudad o interés.</li>
                <li>Envía solicitudes de amistad y acepta las que recibas.</li>
                <li>En <strong>Eventos</strong>, apúntate a una quedada o crea la tuya.</li>
            </ol>
        </article>
        <article class="card">
            <h3>Como colaborador</h3>
            <ol class="help-steps">
                <li>Inicia sesión con una cuenta de colaborador.</li>
                <li>En <strong>Colaborador</strong>, guarda los datos del local.</li>
                <li>Crea eventos de tu local desde esa misma página (el punto de encuentro sale por defecto con la dirección del local).</li>
                <li>Consulta la tabla con los eventos de tu local.</li>
            </ol>
        </article>
        <article class="card">
            <h3>Como administrador</h3>
            <ol class="help-steps">
                <li>Inicia sesión con la cuenta de administrador.</li>
                <li>En <strong>Admin</strong>, gestiona o <strong>elimina</strong> usuarios, intereses y colaboradores.</li>
                <li>Crea eventos y cambia su estado o los <strong>elimina</strong>, todo desde el panel.</li>
                <li>Consulta el informe (estadísticas) al final del propio panel de <strong>Admin</strong>.</li>
            </ol>
        </article>
        <article class="card">
            <h3>Navegación</h3>
            <p class="muted">
                El menú superior cambia según el rol: sin sesión solo verás
                Inicio, Acceso y Ayuda. El <strong>usuario</strong> ve Perfil,
                Usuarios y Eventos. El <strong>colaborador</strong> ve Perfil,
                Eventos y Colaborador (sin Usuarios: no hace amistades). El
                <strong>administrador</strong> ve Perfil, Eventos y Admin (sin
                Usuarios: los gestiona en el panel; el informe está al final de
                Admin). El enlace rojo <strong>Salir</strong> cierra la sesión.
            </p>
        </article>
    </div>
</section>

<section class="section">
    <h2>Preguntas frecuentes</h2>
    <div class="grid two">
        <article class="card">
            <h3>¿Cómo cambio mi contraseña?</h3>
            <p class="muted">Desde <strong>Perfil</strong>, en el apartado «Cambiar contraseña», escribiendo primero la actual.</p>
        </article>
        <article class="card">
            <h3>¿Por qué no puedo entrar a Admin?</h3>
            <p class="muted">Esa zona es solo para administradores. Con otro rol te redirige al inicio con un aviso.</p>
        </article>
        <article class="card">
            <h3>¿Puedo cancelar un evento?</h3>
            <p class="muted">Sí, el creador del evento (o un administrador) puede modificarlo o cancelarlo desde su detalle.</p>
        </article>
        <article class="card">
            <h3>¿Mis datos están seguros?</h3>
            <p class="muted">Las contraseñas se guardan cifradas y nunca se muestran. El correo no es visible en los perfiles públicos.</p>
        </article>
    </div>
</section>

<section class="section">
    <h2>Instalación</h2>
    <div class="card">
        <p>
            Para instalar y configurar el proyecto en local con XAMPP, o ver
            cómo está desplegado con Docker, consulta el archivo
            <code>docs/guia_instalacion.md</code> del proyecto.
        </p>
    </div>
</section>
