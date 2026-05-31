# Guía de instalación y configuración

Esta guía explica cómo poner en marcha Friends4You en local con XAMPP y, al
final, cómo está desplegado en producción con Docker.

> La aplicación también está disponible online sin instalar nada:
> **https://friends4you.samuelciocan.com**

## 1. Requisitos previos

- **XAMPP** (incluye PHP 8, MySQL/MariaDB y Apache).
- Un navegador moderno (Chrome, Firefox o Edge).
- Git o el ZIP del proyecto.

## 2. Obtener el código

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
   1. `database/1_creacion.sql` — crea la base de datos y las tablas.
   2. `database/2_datos_iniciales.sql` — inserta datos de prueba.
   3. `database/3_usuarios.sql` — crea usuarios MySQL con roles.

> El script 3 es opcional para entrar a la aplicación con `root`, pero es
> necesario para cumplir el requisito de "usuarios con roles y privilegios".

> **Aviso:** los usuarios de prueba (`admin@friends4you.com` / `1234`, etc.)
> tienen contraseñas simples para facilitar la evaluación. En producción deben
> cambiarse tras la instalación.

## 4. Configurar credenciales

Las credenciales están en `app/config.php`:

```php
define('DB_HOST', getenv('F4Y_DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('F4Y_DB_NAME') ?: 'friends4you');
define('DB_USER', getenv('F4Y_DB_USER') ?: 'root');
define('DB_PASS', getenv('F4Y_DB_PASSWORD') ?: '');
```

Con XAMPP normalmente funciona con usuario `root` y contraseña vacía.
En producción conviene usar el usuario `app_f4y` (creado por el script 3),
nunca `root`.

## 5. Configurar la URL base

También en `app/config.php`:

```php
$url_base = getenv('F4Y_BASE_URL');
if (!$url_base) {
    $url_base = '/Friends4You/public/';
}
```

Si la carpeta tiene otro nombre, cambiar ese valor. Si Apache apunta
directamente a `public/`, la URL base debe ser `/`.

## 6. Acceder a la aplicación

```text
http://localhost/Friends4You/public/
```

## Problemas comunes

| Problema | Posible solución |
| --- | --- |
| No carga la página | Comprobar que Apache está iniciado. |
| Error de conexión a base de datos | Revisar MySQL, nombre de BD y credenciales en `app/config.php`. |
| No existen tablas | Importar primero `1_creacion.sql`. |
| No funcionan los usuarios de prueba | Importar `2_datos_iniciales.sql`. |
| No carga CSS o JS | Revisar `BASE_URL` en `app/config.php`. |

## Seguridad de carpetas

El punto de entrada está en `public/`. Lo más seguro es configurar Apache
para que la raíz del sitio apunte directamente a la carpeta `public/`, así
el resto del código (`app/`, `database/`) queda fuera de la web y no se puede
abrir desde el navegador. En el despliegue con Docker ya está configurado así.

---

## 7. Despliegue en producción con Docker

La versión online se sirve con tres contenedores en una red interna de Docker:

- **web**: PHP 8.3 + Apache (construido desde el `docker/Dockerfile`).
- **db**: MySQL 8. En el primer arranque ejecuta los scripts de `database/`.
- **tunnel**: `cloudflare/cloudflared`, expone la web por Cloudflare Tunnel
  sin abrir puertos en el servidor.

Cloudflare recibe la petición en `https://friends4you.samuelciocan.com`,
termina el TLS y la reenvía al contenedor `web`. La configuración (URL base y
credenciales de BD) se pasa por variables de entorno desde un archivo `.env`,
por eso `app/config.php` usa `getenv(...)` con valores por defecto para XAMPP.

Variables esperadas en `.env` (no se sube al repositorio):

```dotenv
MYSQL_ROOT_PASSWORD=<contraseña raíz de MySQL>
MYSQL_DATABASE=friends4you
F4Y_DB_USER=app_f4y
F4Y_DB_PASSWORD=<contraseña del usuario de la app>
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

Comandos útiles del día a día:

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

El código va montado como volumen, así que los cambios en PHP, CSS o JS se ven
sin reconstruir la imagen.
