# Pruebas

Las pruebas se han organizado en tres bloques: pruebas de unidad, prueba de conexion a base de datos y pruebas de integracion/funcionales.

## Ejecucion desde consola

En este equipo PHP no esta anadido al PATH, por lo que se ejecutan con el binario de XAMPP:

```powershell
C:\xampp\php\php.exe tests\test_validaciones.php
C:\xampp\php\php.exe tests\test_conexion_bd.php
C:\xampp\php\php.exe tests\test_integracion_basica.php
```

Tambien pueden abrirse desde navegador si Apache esta iniciado:

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

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Validar correo correcto | `lucia@friends4you.com` | Correo valido | OK | Superada |
| Validar correo incorrecto | `correo-invalido` | Correo no valido | OK | Superada |
| Validar campos obligatorios | `nombre` vacio | Error de campo obligatorio | OK | Superada |
| Validar longitud minima | `12345678` y `1234` | `12345678` pasa y `1234` falla | OK | Superada |
| Escape HTML | `<script>` | Texto escapado | OK | Superada |

## Prueba de conexion

Script: `tests/test_conexion_bd.php`

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Conexion a base de datos | Credenciales de `app/config/database.php` | Conexion correcta o error claro | OK, conexion correcta a `friends4you` | Superada |

## Pruebas de integracion basica

Script: `tests/test_integracion_basica.php`

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Roles principales creados | Base de datos inicial | Existen administrador, usuario y colaborador | OK | Superada |
| Usuarios de prueba creados | Script de datos iniciales | Existen las 6 cuentas de prueba | OK | Superada |
| Hash de contrasena compatible | `admin@friends4you.com` / `1234` | `password_verify` valida la contrasena | OK | Superada |
| Intereses suficientes | Tabla `interes` | Existen al menos 12 intereses | OK | Superada |
| Colaboradores iniciales | Tabla `colaborador` | Existen al menos 2 colaboradores | OK | Superada |
| Eventos activos | Tabla `evento` | Existen al menos 3 eventos activos | OK | Superada |
| Amistades iniciales | Tabla `amistad` | Existen solicitudes o amistades | OK | Superada |
| Asistencias iniciales | Tabla `asistencia` | Existen asistencias confirmadas | OK | Superada |

## Pruebas funcionales manuales

| Prueba | Entrada | Resultado esperado | Resultado obtenido | Estado |
| --- | --- | --- | --- | --- |
| Registro de usuario | Nombre, apellidos, correo nuevo, ciudad y contrasena de 8 caracteres | Se crea usuario con rol `usuario` | Usuario creado y redirigido al perfil | Superada |
| Login correcto | `lucia@friends4you.com` / `1234` | Acceso al perfil | Acceso correcto | Superada |
| Login incorrecto | Correo correcto y contrasena incorrecta | Mensaje de error | Mensaje "Correo o contrasena incorrectos" | Superada |
| Editar perfil | Cambiar nombre o ciudad | Datos actualizados | Perfil actualizado | Superada |
| Crear nuevo interes | Texto de interes nuevo | Se crea el interes y se marca en el perfil | Interes creado y asociado al usuario | Superada |
| Buscar usuarios | Ciudad `Malaga` e interes `Cine` | Listado filtrado | Listado mostrado | Superada |
| Enviar solicitud de amistad | Usuario destino valido | Solicitud pendiente | Solicitud creada | Superada |
| Aceptar solicitud de amistad | Solicitud recibida | Amistad aceptada | Estado actualizado a `aceptada` | Superada |
| Crear evento | Datos validos de evento futuro | Evento activo creado | Evento creado | Superada |
| Apuntarse a evento | Evento activo | Asistencia confirmada | Asistencia confirmada | Superada |
| Cancelar asistencia | Asistencia confirmada | Asistencia cancelada | Asistencia cancelada | Superada |
| Acceso de administrador | `admin@friends4you.com` / `1234` | Acceso al panel Admin | Panel cargado | Superada |
| Acceso de colaborador | `padelclub@friends4you.com` / `1234` | Acceso a zona Colaborador | Zona cargada | Superada |
| Intento sin permisos | Usuario normal entra en Admin | Redireccion y mensaje de error | Acceso bloqueado | Superada |

## Observaciones

- Las contrasenas de usuarios se almacenan con `password_hash`.
- El acceso a las paginas privadas se controla por sesion y por rol.
- Las consultas a base de datos usan PDO con sentencias preparadas.
- Solo la carpeta `public/` es accesible desde el navegador; el resto del codigo queda fuera de la raiz web.
