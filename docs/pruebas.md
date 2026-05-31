# Pruebas

Las pruebas se han organizado en cuatro bloques: pruebas de unidad, prueba de conexión a la base de datos, pruebas de integración y pruebas funcionales manuales. Las pruebas automáticas (unidad, conexión e integración) se ejecutaron y las 14 resultaron superadas.

## Ejecución desde consola

Desde la carpeta del proyecto, con PHP en el PATH:

```text
php tests/test_validaciones.php
php tests/test_conexion_bd.php
php tests/test_integracion_basica.php
```

Si PHP no está en el PATH (caso habitual en XAMPP en Windows), se usa la ruta
completa del binario, por ejemplo:

```text
C:\xampp\php\php.exe tests\test_validaciones.php
```

También pueden abrirse desde el navegador si Apache está iniciado:

```text
http://localhost/Friends4You/tests/test_validaciones.php
http://localhost/Friends4You/tests/test_conexion_bd.php
http://localhost/Friends4You/tests/test_integracion_basica.php
```

En el despliegue con Docker se ejecutan dentro del contenedor web:

```bash
docker compose exec web php tests/test_validaciones.php
docker compose exec web php tests/test_conexion_bd.php
docker compose exec web php tests/test_integracion_basica.php
```

## Pruebas de unidad

Script: `tests/test_validaciones.php`

Comprueban, de forma aislada y sin base de datos, las funciones de validación
y de escape de `app/core/helpers.php`: el formato del correo, los campos
obligatorios, la longitud mínima de la contraseña y el escape de HTML.

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Validar correo correcto | `lucia@friends4you.com` | Correo válido | OK | Superada |
| Validar correo incorrecto | `correo-invalido` | Correo no válido | OK | Superada |
| Validar campos obligatorios | `nombre` vacío | Error de campo obligatorio | OK | Superada |
| Validar longitud mínima | `12345678` y `1234` | `12345678` pasa y `1234` falla | OK | Superada |
| Escape HTML | `<script>` | Texto escapado | OK | Superada |

## Prueba de conexión

Script: `tests/test_conexion_bd.php`

Comprueba que la aplicación abre correctamente la conexión PDO con los datos de
`app/config.php` y que la base de datos responde.

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Conexión a base de datos | Credenciales de `app/config.php` | Conexión correcta o error claro | OK, conexión correcta a `friends4you` | Superada |

## Pruebas de integración básica

Script: `tests/test_integracion_basica.php`

Comprueban que la aplicación y la base de datos funcionan juntas: que los
scripts SQL se han importado y que existen los datos iniciales (roles, cuentas
de prueba, intereses, colaboradores, eventos, amistades y asistencias).

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Roles principales creados | Base de datos inicial | Existen administrador, usuario y colaborador | OK | Superada |
| Usuarios de prueba creados | Script de datos iniciales | Existen las 6 cuentas de prueba | OK | Superada |
| Hash de contraseña compatible | `admin@friends4you.com` / `1234` | `password_verify` valida la contraseña | OK | Superada |
| Intereses suficientes | Tabla `interes` | Existen al menos 12 intereses | OK | Superada |
| Colaboradores iniciales | Tabla `colaborador` | Existen al menos 2 colaboradores | OK | Superada |
| Eventos activos | Tabla `evento` | Existen al menos 3 eventos activos | OK | Superada |
| Amistades iniciales | Tabla `amistad` | Existen solicitudes o amistades | OK | Superada |
| Asistencias iniciales | Tabla `asistencia` | Existen asistencias confirmadas | OK | Superada |

## Pruebas funcionales manuales

Recorren a mano, en el navegador y con cada rol, los flujos principales de la
aplicación para comprobar que el comportamiento es el esperado de principio a
fin.

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Registro de usuario | Nombre, apellidos, correo nuevo, ciudad y contraseña de 8 caracteres | Se crea usuario con rol `usuario` | Usuario creado y redirigido al perfil | Superada |
| Login correcto | `lucia@friends4you.com` / `1234` | Acceso al perfil | Acceso correcto | Superada |
| Login incorrecto | Correo correcto y contraseña incorrecta | Mensaje de error | Mensaje "Correo o contraseña incorrectos" | Superada |
| Editar perfil | Cambiar nombre o ciudad | Datos actualizados | Perfil actualizado | Superada |
| Crear nuevo interés | Texto de interés nuevo | Se crea el interés y se marca en el perfil | Interés creado y asociado al usuario | Superada |
| Buscar usuarios | Ciudad `Málaga` e interés `Cine` | Listado filtrado | Listado mostrado | Superada |
| Enviar solicitud de amistad | Usuario destino válido | Solicitud pendiente | Solicitud creada | Superada |
| Aceptar solicitud de amistad | Solicitud recibida | Amistad aceptada | Estado actualizado a `aceptada` | Superada |
| Crear evento | Datos válidos de evento futuro | Evento activo creado | Evento creado | Superada |
| Apuntarse a evento | Evento activo | Asistencia confirmada | Asistencia confirmada | Superada |
| Cancelar asistencia | Asistencia confirmada | Asistencia cancelada | Asistencia cancelada | Superada |
| Acceso de administrador | `admin@friends4you.com` / `1234` | Acceso al panel Admin | Panel cargado | Superada |
| Acceso de colaborador | `padelclub@friends4you.com` / `1234` | Acceso a zona Colaborador | Zona cargada | Superada |
| Intento sin permisos | Usuario normal entra en Admin | Redirección y mensaje de error | Acceso bloqueado | Superada |

## Observaciones

- Las contraseñas de usuarios se almacenan con `password_hash`.
- El acceso a las páginas privadas se controla por sesión y por rol.
- Las consultas a base de datos usan PDO con sentencias preparadas.
- Solo la carpeta `public/` es accesible desde el navegador; el resto del código queda fuera de la raíz web.
