# Friends4You

Friends4You es una aplicación web sencilla para el módulo Proyecto DAW de 2º de
Desarrollo de Aplicaciones Web. Permite que los usuarios conozcan personas con
intereses parecidos, gestionen amistades y organicen quedadas o eventos, con la
participación de colaboradores (locales o negocios).

Está hecha con PHP sin frameworks siguiendo el patrón Modelo-Vista-Controlador,
y funciona tanto en local con XAMPP como en el despliegue online con Docker.

## Versión online

Disponible en **<https://friends4you.samuelciocan.com>** (Docker con PHP 8.3 +
Apache, MySQL 8 y Cloudflare Tunnel). Detalles en
[docs/guia_instalacion.md](docs/guia_instalacion.md).

## Roles

- **Usuario** (persona): gestiona su perfil e intereses, amistades y asistencia a eventos.
- **Colaborador** (local): solo gestiona su establecimiento y crea los eventos de su local. No hace amistades ni se apunta a eventos.
- **Administrador** (supervisión): gestiona y elimina usuarios, intereses, colaboradores y eventos, y consulta el informe (todo dentro del propio panel). No tiene intereses, amistades ni asistencias.

## Tecnologías

- PHP 8 sin frameworks (patrón MVC).
- MySQL 8 / MariaDB.
- PDO con consultas preparadas.
- HTML5, CSS3 y JavaScript (con AJAX).
- Sesiones de PHP y `password_hash` / `password_verify`.
- Docker + Cloudflare Tunnel (solo para el despliegue online).

## Estructura de carpetas

```text
Friends4You/
├── app/
│   ├── config.php          # Configuración (BD, URL base, zona horaria)
│   ├── core/               # Conexión BD, autenticación y funciones
│   ├── controllers/        # Controladores
│   └── views/              # Vistas (plantillas PHP)
├── public/
│   ├── index.php           # Punto de entrada y router
│   └── assets/             # CSS y JavaScript
├── database/               # Scripts SQL (creación, datos, usuarios)
├── docs/                   # Guías de instalación, uso y pruebas
├── tests/                  # Pruebas de validación, conexión e integración
├── docker-compose.yml
├── docker/                 # Dockerfile y VirtualHost de Apache del contenedor
└── README.md
```

## Instalación rápida (XAMPP)

1. Copiar el proyecto en `C:\xampp\htdocs\Friends4You`.
2. Iniciar Apache y MySQL desde XAMPP.
3. En `http://localhost/phpmyadmin` importar, en orden, los tres scripts de
   `database/`: `1_creacion.sql`, `2_datos_iniciales.sql` y `3_usuarios.sql`.
4. Revisar las credenciales en `app/config.php`.
5. Abrir `http://localhost/Friends4You/public/`.

Guía completa (incluido el despliegue con Docker) en
[docs/guia_instalacion.md](docs/guia_instalacion.md).

## Configuración

Todo está en `app/config.php`:

```php
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');
```

Los valores por defecto sirven para XAMPP. En Docker se pasan por variables de
entorno (`getenv`), por lo que no hace falta tocar el código.

## Usuarios de prueba

Todas las cuentas usan la contraseña `1234` (guardada como hash en la BD).

| Rol | Correo | Contraseña |
| --- | --- | --- |
| administrador | admin@friends4you.com | 1234 |
| usuario | lucia@friends4you.com | 1234 |
| usuario | carlos@friends4you.com | 1234 |
| usuario | marta@friends4you.com | 1234 |
| colaborador | padelclub@friends4you.com | 1234 |
| colaborador | cafeteriaplaza@friends4you.com | 1234 |

## Funcionalidades

- Registro, inicio y cierre de sesión.
- Edición de perfil, intereses y contraseña.
- Búsqueda de usuarios por ciudad e interés.
- Solicitudes de amistad: enviar, aceptar y rechazar.
- Eventos: crear, consultar, modificar, cancelar y apuntarse.
- Filtrado de eventos por interés con AJAX.
- Zona de colaborador con datos del establecimiento.
- Panel de administración con informe estadístico incluido.
- Página de ayuda con guía de uso e instalación.

## Pruebas

Scripts en `tests/` (ver [docs/pruebas.md](docs/pruebas.md)):

- `tests/test_validaciones.php` — pruebas de unidad (no necesita BD).
- `tests/test_conexion_bd.php` — comprueba la conexión a la BD.
- `tests/test_integracion_basica.php` — comprueba los datos iniciales.

Se ejecutan desde consola con `php tests/test_validaciones.php` o desde el
navegador. En XAMPP, si PHP no está en el PATH: `C:\xampp\php\php.exe`.

## Seguridad

- Contraseñas con `password_hash` (bcrypt) y verificación con `password_verify`.
- Acceso a zonas privadas controlado por sesión y por rol.
- Consultas con sentencias preparadas (PDO) para evitar inyección SQL.
- Escapado de HTML en las salidas para evitar XSS.
- Solo la carpeta `public/` es accesible desde el navegador; el resto del código queda fuera de la raíz web.

## Posibles mejoras futuras

- Recuperación de contraseña por correo.
- Foto de perfil.
- Paginación en las tablas grandes.
- Mensajería privada sencilla.
