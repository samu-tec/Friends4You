# Guia de instalacion y configuracion

Esta guia explica como poner en marcha Friends4You en local con XAMPP y, al
final, como esta desplegado en produccion con Docker.

> La aplicacion tambien esta disponible online sin instalar nada:
> **https://friends4you.samuelciocan.com**

## 1. Requisitos previos

- **XAMPP** (incluye PHP 8, MySQL/MariaDB y Apache).
- Un navegador moderno (Chrome, Firefox o Edge).
- Git o el ZIP del proyecto.

## 2. Obtener el codigo

Copiar el proyecto en la carpeta de XAMPP:

```text
C:\xampp\htdocs\Friends4You
```

El punto de entrada debe quedar en:

```text
C:\xampp\htdocs\Friends4You\public\index.php
```

## 3. Crear e importar la base de datos

1. Abrir XAMPP e iniciar **Apache** y **MySQL**.
2. Entrar en `http://localhost/phpmyadmin`.
3. Importar los scripts SQL **en este orden**:
   1. `database/01_creacion_friends4you.sql` — crea la base de datos y las tablas.
   2. `database/02_datos_iniciales_friends4you.sql` — inserta datos de prueba.
   3. `database/03_usuarios_privilegios_friends4you.sql` — crea usuarios MySQL con roles.

> El script 03 es opcional para entrar a la aplicacion con `root`, pero es
> necesario para cumplir el requisito de "usuarios con roles y privilegios".

> **Aviso:** los usuarios de prueba (`admin@friends4you.com` / `1234`, etc.)
> tienen contrasenas simples para facilitar la evaluacion. En produccion deben
> cambiarse tras la instalacion.

### Nota MySQL 8 / MariaDB

La regla "un usuario no puede ser amigo de si mismo" se aplica con dos
**triggers** (`trg_amistad_no_self_insert` y `trg_amistad_no_self_update`) en
la tabla `amistad`, en lugar de un `CHECK`. MySQL 8 no permite un `CHECK`
sobre columnas que forman parte de una clave ajena con accion referencial,
mientras que los triggers funcionan igual en MySQL 8 y en MariaDB (XAMPP).

## 4. Configurar credenciales

Las credenciales estan en `app/config.php`:

```php
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');
```

Con XAMPP normalmente funciona con usuario `root` y contrasena vacia.
En produccion conviene usar el usuario `app_f4y` (creado por el script 03),
nunca `root`.

## 5. Configurar la URL base

Tambien en `app/config.php`:

```php
$base = getenv('F4Y_BASE_URL');
if (!$base) {
    $base = '/Friends4You/public/';
}
```

Si la carpeta tiene otro nombre, cambiar ese valor. Si Apache apunta
directamente a `public/`, la URL base debe ser `/`.

## 6. Acceder a la aplicacion

```text
http://localhost/Friends4You/public/
```

## Problemas comunes

| Problema | Posible solucion |
| --- | --- |
| No carga la pagina | Comprobar que Apache esta iniciado. |
| Error de conexion a base de datos | Revisar MySQL, nombre de BD y credenciales en `app/config.php`. |
| No existen tablas | Importar primero `01_creacion_friends4you.sql`. |
| No funcionan los usuarios de prueba | Importar `02_datos_iniciales_friends4you.sql`. |
| No carga CSS o JS | Revisar `BASE_URL` en `app/config.php`. |

## Seguridad de carpetas

El punto de entrada esta en `public/`. Lo mas seguro es configurar Apache
para que la raiz del sitio apunte directamente a la carpeta `public/`, asi
el resto del codigo (`app/`, `database/`) queda fuera de la web y no se puede
abrir desde el navegador. En el despliegue con Docker ya esta configurado asi.

---

## 7. Despliegue en produccion con Docker

La version online se sirve con tres contenedores en una red interna de Docker:

- **web**: PHP 8.3 + Apache (construido desde el `Dockerfile`).
- **db**: MySQL 8. En el primer arranque ejecuta los scripts de `database/`.
- **tunnel**: `cloudflare/cloudflared`, expone la web por Cloudflare Tunnel
  sin abrir puertos en el servidor.

Cloudflare recibe la peticion en `https://friends4you.samuelciocan.com`,
termina el TLS y la reenvia al contenedor `web`. La configuracion (URL base y
credenciales de BD) se pasa por variables de entorno desde un archivo `.env`,
por eso `app/config.php` usa `getenv(...)` con valores por defecto para XAMPP.

Variables esperadas en `.env` (no se sube al repositorio):

```dotenv
MYSQL_ROOT_PASSWORD=<contrasena raiz de MySQL>
MYSQL_DATABASE=friends4you
F4Y_DB_USER=app_f4y
F4Y_DB_PASSWORD=<contrasena del usuario de la app>
F4Y_BASE_URL=https://friends4you.samuelciocan.com
CLOUDFLARE_TUNNEL_TOKEN=<token de cloudflared>
```

Pasos:

```bash
git clone <repositorio> Friends4You
cd Friends4You
# crear el archivo .env con las variables de arriba
docker compose up -d --build
```

Comandos utiles del dia a dia:

```bash
docker compose ps               # estado de los contenedores
docker compose logs -f web      # logs del contenedor web
docker compose restart web      # reiniciar la web
docker compose down             # parar todo
```

Copia de seguridad de la base de datos:

```bash
docker compose exec db sh -c 'exec mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" friends4you' > backup.sql
```

El codigo va montado como volumen, asi que los cambios en PHP, CSS o JS se ven
sin reconstruir la imagen.
