-- =====================================================================
-- Friends4You — Script 02: inserción de datos iniciales de prueba
-- ---------------------------------------------------------------------
-- Inserta roles, usuarios de prueba (todos con contraseña 1234),
-- intereses, preferencias, colaboradores, eventos, amistades y asistencias
-- para poder probar la aplicación sin crear datos manualmente.
-- Ejecutar después de 01_creacion_friends4you.sql.
-- =====================================================================

USE friends4you;

SET @hash_1234 = '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei';

INSERT INTO rol (nombre) VALUES
('administrador'),
('usuario'),
('colaborador')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT INTO usuario (nombre, apellidos, correo, contrasena, ciudad, id_rol) VALUES
('Admin', 'Friends4You', 'admin@friends4you.com', @hash_1234, 'Malaga', (SELECT id_rol FROM rol WHERE nombre = 'administrador')),
('Lucia', 'Martin Lopez', 'lucia@friends4you.com', @hash_1234, 'Malaga', (SELECT id_rol FROM rol WHERE nombre = 'usuario')),
('Carlos', 'Garcia Ruiz', 'carlos@friends4you.com', @hash_1234, 'Sevilla', (SELECT id_rol FROM rol WHERE nombre = 'usuario')),
('Marta', 'Sanchez Mora', 'marta@friends4you.com', @hash_1234, 'Malaga', (SELECT id_rol FROM rol WHERE nombre = 'usuario')),
('Padel Club', 'Centro', 'padelclub@friends4you.com', @hash_1234, 'Malaga', (SELECT id_rol FROM rol WHERE nombre = 'colaborador')),
('Cafeteria', 'Plaza', 'cafeteriaplaza@friends4you.com', @hash_1234, 'Sevilla', (SELECT id_rol FROM rol WHERE nombre = 'colaborador'))
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    apellidos = VALUES(apellidos),
    contrasena = VALUES(contrasena),
    ciudad = VALUES(ciudad),
    id_rol = VALUES(id_rol);

INSERT INTO interes (nombre) VALUES
('Padel'),
('Cine'),
('Senderismo'),
('Videojuegos'),
('Cafes y charlas'),
('Musica'),
('Fotografia'),
('Lectura'),
('Cocina'),
('Idiomas'),
('Teatro'),
('Running'),
('Gimnasio'),
('Juegos de mesa'),
('Tecnologia'),
('Viajes'),
('Voluntariado'),
('Baile'),
('Arte'),
('Escritura'),
('Anime'),
('Astronomia')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

INSERT IGNORE INTO preferencia (id_usuario, id_interes)
SELECT u.id_usuario, i.id_interes
FROM usuario u, interes i
WHERE i.nombre IN ('Cine', 'Cafes y charlas', 'Musica')
  AND u.correo = 'lucia@friends4you.com';

INSERT IGNORE INTO preferencia (id_usuario, id_interes)
SELECT u.id_usuario, i.id_interes
FROM usuario u, interes i
WHERE i.nombre IN ('Padel', 'Videojuegos')
  AND u.correo = 'carlos@friends4you.com';

INSERT IGNORE INTO preferencia (id_usuario, id_interes)
SELECT u.id_usuario, i.id_interes
FROM usuario u, interes i
WHERE i.nombre IN ('Senderismo', 'Cine')
  AND u.correo = 'marta@friends4you.com';

INSERT IGNORE INTO preferencia (id_usuario, id_interes)
SELECT u.id_usuario, i.id_interes
FROM usuario u, interes i
WHERE i.nombre = 'Padel'
  AND u.correo = 'padelclub@friends4you.com';

INSERT IGNORE INTO preferencia (id_usuario, id_interes)
SELECT u.id_usuario, i.id_interes
FROM usuario u, interes i
WHERE i.nombre = 'Cafes y charlas'
  AND u.correo = 'cafeteriaplaza@friends4you.com';

INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
SELECT 'Padel Club Centro', 'Avenida del Deporte 12', 'Malaga',
       'Club deportivo con pistas de padel para quedadas de nivel inicial.',
       u.id_usuario
FROM usuario u
WHERE u.correo = 'padelclub@friends4you.com'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    direccion = VALUES(direccion),
    ciudad = VALUES(ciudad),
    descripcion = VALUES(descripcion);

INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador)
SELECT 'Cafeteria Plaza', 'Plaza Mayor 4', 'Sevilla',
       'Cafeteria centrica para charlas, juegos de mesa y encuentros tranquilos.',
       u.id_usuario
FROM usuario u
WHERE u.correo = 'cafeteriaplaza@friends4you.com'
ON DUPLICATE KEY UPDATE
    nombre = VALUES(nombre),
    direccion = VALUES(direccion),
    ciudad = VALUES(ciudad),
    descripcion = VALUES(descripcion);

INSERT INTO evento (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento)
SELECT u.id_usuario,
       'Partido de padel para principiantes',
       'Quedada tranquila para aprender y jugar un partido amistoso.',
       '2026-09-12 18:00:00',
       'Recepcion de Padel Club Centro',
       i.id_interes,
       c.id_colaborador,
       'activo'
FROM usuario u, interes i, colaborador c
WHERE i.nombre = 'Padel'
  AND c.nombre = 'Padel Club Centro'
  AND u.correo = 'padelclub@friends4you.com'
  AND NOT EXISTS (
      SELECT 1 FROM evento
      WHERE nombre = 'Partido de padel para principiantes'
        AND fecha_hora = '2026-09-12 18:00:00'
  );

INSERT INTO evento (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento)
SELECT u.id_usuario,
       'Ruta de senderismo del domingo',
       'Ruta sencilla de manana para conocer gente y caminar en grupo.',
       '2026-09-19 09:30:00',
       'Entrada principal del parque natural',
       i.id_interes,
       NULL,
       'activo'
FROM usuario u, interes i
WHERE i.nombre = 'Senderismo'
  AND u.correo = 'marta@friends4you.com'
  AND NOT EXISTS (
      SELECT 1 FROM evento
      WHERE nombre = 'Ruta de senderismo del domingo'
        AND fecha_hora = '2026-09-19 09:30:00'
  );

INSERT INTO evento (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento)
SELECT u.id_usuario,
       'Cafe y charla entre usuarios',
       'Encuentro informal para conversar y conocer nuevos amigos.',
       '2026-09-26 17:30:00',
       'Mesa reservada en Cafeteria Plaza',
       i.id_interes,
       c.id_colaborador,
       'activo'
FROM usuario u, interes i, colaborador c
WHERE i.nombre = 'Cafes y charlas'
  AND c.nombre = 'Cafeteria Plaza'
  AND u.correo = 'cafeteriaplaza@friends4you.com'
  AND NOT EXISTS (
      SELECT 1 FROM evento
      WHERE nombre = 'Cafe y charla entre usuarios'
        AND fecha_hora = '2026-09-26 17:30:00'
  );

INSERT IGNORE INTO amistad (usuario_origen, usuario_destino, estado)
SELECT origen.id_usuario, destino.id_usuario, 'aceptada'
FROM usuario origen, usuario destino
WHERE destino.correo = 'carlos@friends4you.com'
  AND origen.correo = 'lucia@friends4you.com';

INSERT IGNORE INTO amistad (usuario_origen, usuario_destino, estado)
SELECT origen.id_usuario, destino.id_usuario, 'pendiente'
FROM usuario origen, usuario destino
WHERE destino.correo = 'lucia@friends4you.com'
  AND origen.correo = 'marta@friends4you.com';

INSERT IGNORE INTO asistencia (id_usuario, id_evento, estado_asistencia)
SELECT u.id_usuario, e.id_evento, 'confirmada'
FROM usuario u, evento e
WHERE e.nombre = 'Partido de padel para principiantes'
  AND u.correo IN ('lucia@friends4you.com', 'carlos@friends4you.com');

INSERT IGNORE INTO asistencia (id_usuario, id_evento, estado_asistencia)
SELECT u.id_usuario, e.id_evento, 'confirmada'
FROM usuario u, evento e
WHERE e.nombre = 'Cafe y charla entre usuarios'
  AND u.correo IN ('lucia@friends4you.com', 'marta@friends4you.com');
