# Guia de uso

> Acceso online sin instalacion: **https://friends4you.samuelciocan.com**

## Usuarios de prueba

Las contrasenas son deliberadamente simples para facilitar la evaluacion academica. En produccion deben cambiarse.

| Rol | Correo | Contrasena | Acceso especial |
| --- | --- | --- | --- |
| administrador | admin@friends4you.com | 1234 | Panel Admin, Informe estadistico |
| usuario | lucia@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| usuario | carlos@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| usuario | marta@friends4you.com | 1234 | Perfil, Usuarios, Eventos |
| colaborador | padelclub@friends4you.com | 1234 | Zona Colaborador, gestion de eventos del local |
| colaborador | cafeteriaplaza@friends4you.com | 1234 | Zona Colaborador, gestion de eventos del local |

## Uso como usuario normal

1. Entrar en la pagina de acceso.
2. Iniciar sesion con una cuenta de usuario o registrar una nueva.
3. Abrir `Perfil` y editar nombre, apellidos, ciudad e intereses.
4. Crear un interés nuevo desde el perfil si no aparece en la lista.
5. Abrir `Usuarios` para buscar personas por ciudad o interes.
6. Enviar solicitudes de amistad.
7. Aceptar o rechazar solicitudes recibidas.
8. Abrir `Eventos` para consultar quedadas activas.
9. Crear un evento propio.
10. Entrar al detalle de un evento para apuntarse o cancelar asistencia.
11. Modificar o cancelar eventos creados por el propio usuario.

## Uso como colaborador

1. Iniciar sesion con `padelclub@friends4you.com` o `cafeteriaplaza@friends4you.com`.
2. Abrir `Colaborador`.
3. Editar los datos del establecimiento.
4. Crear eventos asociados al establecimiento.
5. Consultar la tabla de eventos del local.
6. Abrir un evento desde la tabla para revisarlo o modificarlo si fue creado por esa cuenta.

## Uso como administrador

1. Iniciar sesion con `admin@friends4you.com`.
2. Abrir `Admin`.
3. Revisar y modificar usuarios.
4. Crear o eliminar intereses.
5. Crear colaboradores para cuentas con rol colaborador.
6. Editar datos de colaboradores existentes.
7. Cambiar el estado de eventos.
8. Abrir `Informe` para ver estadisticas.

## Navegacion

El menu cambia segun el rol conectado:

- Sin sesion: Inicio, Acceso y Ayuda.
- Usuario: Inicio, Perfil, Usuarios, Eventos, Ayuda y Salir.
- Colaborador: lo anterior mas Colaborador (entre Eventos y Ayuda).
- Administrador: lo anterior mas Admin e Informe (entre Eventos y Ayuda).

## Registro y contrasenas

- El registro pide nombre, apellidos, correo, ciudad y contrasena.
- La contrasena de las cuentas nuevas debe tener al menos 8 caracteres.
- Las contrasenas se guardan cifradas en la base de datos, nunca en texto plano.
- Desde `Perfil` se puede cambiar la contrasena introduciendo la actual.

## Cierre de sesion

El enlace `Salir` del menu cierra la sesion y vuelve a la pagina de inicio.
Tras cerrar sesion, las paginas privadas (Perfil, Usuarios, Eventos, etc.)
vuelven a pedir iniciar sesion.
