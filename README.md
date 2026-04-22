# Friends4You

[![Online](https://img.shields.io/badge/online-friends4you.samuelciocan.com-1f7a6c)](https://friends4you.samuelciocan.com)

Friends4You es una aplicacion web sencilla para el modulo Proyecto DAW de 2º de
Desarrollo de Aplicaciones Web. Permite que los usuarios conozcan personas con
intereses parecidos, gestionen amistades y organicen quedadas o eventos, con la
participacion de colaboradores (locales o negocios).

Esta hecha con PHP sin frameworks siguiendo el patron Modelo-Vista-Controlador,
y funciona tanto en local con XAMPP como en el despliegue online con Docker.

## Version online

Disponible en **<https://friends4you.samuelciocan.com>** (Docker con PHP 8.3 +
Apache, MySQL 8 y Cloudflare Tunnel). Detalles en
[docs/guia_instalacion.md](docs/guia_instalacion.md).

## Roles

- **Administrador**: gestiona usuarios, intereses, colaboradores, eventos e informes.
- **Usuario**: gestiona su perfil, intereses, amistades y asistencia a eventos.
- **Colaborador**: gestiona su establecimiento y los eventos asociados.

## Tecnologias

- PHP 8 sin frameworks (patron MVC).
- MySQL 8 / MariaDB.
- PDO con consultas preparadas.
- HTML5, CSS3 y JavaScript (con AJAX).
- Sesiones de PHP y `password_hash` / `password_verify`.
- Docker + Cloudflare Tunnel (solo para el despliegue online).

## Estructura de carpetas

```text
Friends4You/
├── app/
│   ├── config.php          # Configuracion (BD, URL base, zona horaria)
│   ├── core/               # Conexion BD, autenticacion y funciones
│   ├── controllers/        # Controladores
│   └── views/              # Vistas (plantillas PHP)
├── public/
│   ├── index.php           # Punto de entrada y router
│   └── assets/             # CSS y JavaScript
├── database/               # Scripts SQL (creacion, datos, usuarios)
├── docs/                   # Guias de instalacion, uso y pruebas
├── tests/                  # Pruebas de validacion, conexion e integracion
├── Dockerfile
├── docker-compose.yml
├── docker/                 # VirtualHost de Apache del contenedor
└── README.md
```

## Instalacion rapida (XAMPP)

1. Copiar el proyecto en `C:\xampp\htdocs\Friends4You`.
2. Iniciar Apache y MySQL desde XAMPP.
3. En `http://localhost/phpmyadmin` importar, en orden, los scripts de
   `database/` (01, 02 y 03).
4. Revisar las credenciales en `app/config.php`.
5. Abrir `http://localhost/Friends4You/public/`.

Guia completa (incluido el despliegue con Docker) en
[docs/guia_instalacion.md](docs/guia_instalacion.md).

## Configuracion

Todo esta en `app/config.php`:

```php
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');
```

Los valores por defecto sirven para XAMPP. En Docker se pasan por variables de
entorno (`getenv`), por lo que no hace falta tocar el codigo.

## Usuarios de prueba

Todas las cuentas usan la contrasena `1234` (guardada como hash en la BD).

| Rol | Correo | Contrasena |
| --- | --- | --- |
| administrador | admin@friends4you.com | 1234 |
| usuario | lucia@friends4you.com | 1234 |
| usuario | carlos@friends4you.com | 1234 |
| usuario | marta@friends4you.com | 1234 |
| colaborador | padelclub@friends4you.com | 1234 |
| colaborador | cafeteriaplaza@friends4you.com | 1234 |

## Funcionalidades

- Registro, inicio y cierre de sesion.
- Edicion de perfil, intereses y contrasena.
- Busqueda de usuarios por ciudad e interes.
- Solicitudes de amistad: enviar, aceptar y rechazar.
- Eventos: crear, consultar, modificar, cancelar y apuntarse.
- Filtrado de eventos por interes con AJAX.
- Zona de colaborador con datos del establecimiento.
- Panel de administracion e informe estadistico.
- Pagina de ayuda con guia de uso e instalacion.

## Pruebas

Scripts en `tests/` (ver [docs/pruebas.md](docs/pruebas.md)):

- `tests/test_validaciones.php` — pruebas de unidad (no necesita BD).
- `tests/test_conexion_bd.php` — comprueba la conexion a la BD.
- `tests/test_integracion_basica.php` — comprueba los datos iniciales.

Se ejecutan desde consola con `php tests/test_validaciones.php` o desde el
navegador. En XAMPP, si PHP no esta en el PATH: `C:\xampp\php\php.exe`.

## Seguridad

- Contrasenas con `password_hash` (bcrypt) y verificacion con `password_verify`.
- Acceso a zonas privadas controlado por sesion y por rol.
- Consultas con sentencias preparadas (PDO) para evitar inyeccion SQL.
- Escapado de HTML en las salidas para evitar XSS.
- Solo la carpeta `public/` es accesible desde el navegador; el resto del codigo queda fuera de la raiz web.

## Posibles mejoras futuras

- Recuperacion de contrasena por correo.
- Foto de perfil.
- Paginacion en las tablas grandes.
- Mensajeria privada sencilla.
