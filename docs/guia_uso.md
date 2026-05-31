# Guía de uso

> Acceso online sin instalación: **https://friends4you.samuelciocan.com**

## Usuarios de prueba

Las contraseñas son deliberadamente simples para facilitar la evaluación académica. En producción deben cambiarse.

| Rol | Correo | Contraseña | Acceso especial |
| --- | --- | --- | --- |
| administrador | admin@friends4you.com | 1234 | Panel Admin (incluye el informe estadístico) |
| usuario | lucia@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| usuario | carlos@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| usuario | marta@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| colaborador | padelclub@friends4you.com | 1234 | Zona Colaborador, gestión de eventos del local |
| colaborador | cafeteriaplaza@friends4you.com | 1234 | Zona Colaborador, gestión de eventos del local |

## Uso como usuario normal

1. Entrar en la página de acceso.
2. Iniciar sesión con una cuenta de usuario o registrar una nueva.
3. Abrir `Perfil` y editar nombre, apellidos, ciudad e intereses.
4. Crear un interés nuevo desde el perfil si no aparece en la lista.
5. Abrir `Usuarios` para buscar personas por ciudad o interés.
6. Enviar solicitudes de amistad.
7. Aceptar o rechazar solicitudes recibidas.
8. Abrir `Eventos` para consultar quedadas activas.
9. Crear un evento propio.
10. Entrar al detalle de un evento para apuntarse o cancelar asistencia.
11. Modificar o cancelar eventos creados por el propio usuario.

## Uso como colaborador

1. Iniciar sesión con `padelclub@friends4you.com` o `cafeteriaplaza@friends4you.com`.
2. Abrir `Colaborador`.
3. Editar los datos del establecimiento.
4. Crear eventos del local desde esa misma página (el punto de encuentro sale por defecto con la dirección del local).
5. Consultar la tabla de eventos del local.
6. Abrir un evento desde la tabla para revisarlo o modificarlo.

> El colaborador representa un local: no busca usuarios, no envía solicitudes
> de amistad ni se apunta a eventos.

## Uso como administrador

1. Iniciar sesión con `admin@friends4you.com`.
2. Abrir `Admin`.
3. Revisar, modificar o **eliminar** usuarios.
4. Crear o eliminar intereses.
5. Crear o editar colaboradores.
6. Crear eventos, cambiar su estado o **eliminarlos**, todo desde el panel.
7. Ver el informe (estadísticas) al final del propio panel de `Admin`.

> El administrador es una cuenta de supervisión: no tiene intereses, ni
> amistades, ni se apunta a eventos.

## Navegación

El menú cambia según el rol conectado:

- Sin sesión: Inicio, Acceso y Ayuda.
- Usuario: Inicio, Perfil, Usuarios, Eventos, Ayuda y Salir.
- Colaborador: Inicio, Perfil, Eventos, Colaborador, Ayuda y Salir (sin Usuarios: no hace amistades).
- Administrador: Inicio, Perfil, Eventos, Admin, Ayuda y Salir (sin Usuarios: los gestiona en el panel; el informe está dentro de Admin).

## Registro y contraseñas

- El registro pide nombre, apellidos, correo, ciudad y contraseña.
- La contraseña de las cuentas nuevas debe tener al menos 8 caracteres.
- Las contraseñas se guardan cifradas en la base de datos, nunca en texto plano.
- Desde `Perfil` se puede cambiar la contraseña introduciendo la actual.

## Cierre de sesión

El enlace `Salir` del menú cierra la sesión y vuelve a la página de inicio.
Tras cerrar sesión, las páginas privadas (Perfil, Usuarios, Eventos, etc.)
vuelven a pedir iniciar sesión.
