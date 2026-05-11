<?php // Pagina de ayuda: que es la aplicacion, usuarios de prueba y como se usa. ?>

<section class="section">
    <h1>Ayuda</h1>
    <p class="muted">
        Friends4You es una aplicacion para conocer gente con intereses
        parecidos, hacer amistades y organizar quedadas o eventos.
    </p>
</section>

<section class="section">
    <h2>Usuarios de prueba</h2>
    <p class="muted">Todas las cuentas tienen la contrasena <code>1234</code>.</p>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Rol</th>
                    <th>Correo</th>
                    <th>Contrasena</th>
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
                    <td><span class="badge badge--colaborador">Colaborador</span></td>
                    <td>padelclub@friends4you.com</td>
                    <td><code>1234</code></td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="section">
    <h2>Como se usa</h2>
    <div class="grid three">
        <article class="card">
            <h3><span class="badge badge--usuario">Usuario</span></h3>
            <p class="muted">
                Edita su perfil e intereses, busca personas por ciudad o
                interes, envia y acepta solicitudes de amistad y se apunta
                a eventos.
            </p>
        </article>
        <article class="card">
            <h3><span class="badge badge--colaborador">Colaborador</span></h3>
            <p class="muted">
                Gestiona los datos de su establecimiento y crea eventos
                asociados a su local.
            </p>
        </article>
        <article class="card">
            <h3><span class="badge badge--administrador">Administrador</span></h3>
            <p class="muted">
                Gestiona usuarios, intereses, colaboradores y eventos, y
                consulta el informe estadistico.
            </p>
        </article>
    </div>
</section>

<section class="section">
    <h2>Instalacion</h2>
    <div class="card">
        <p>
            Para instalar y configurar el proyecto en local con XAMPP, o ver
            como esta desplegado con Docker, consulta el archivo
            <code>docs/guia_instalacion.md</code> del proyecto.
        </p>
    </div>
</section>
