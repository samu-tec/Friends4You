-- =====================================================================
-- Friends4You — Script 2: inserción de datos iniciales de prueba
-- ---------------------------------------------------------------------
-- Inserta los tres roles, las seis cuentas de prueba (todas con
-- contraseña "1234"), una lista de intereses, las preferencias de los
-- usuarios, las dos fichas de colaborador, tres eventos de ejemplo,
-- una solicitud de amistad aceptada y otra pendiente, y varias
-- asistencias confirmadas.
--
-- Ejecutar después de 1_creacion.sql (que crea la BD y las tablas vacías).
-- Como las tablas se crean vacías, los AUTO_INCREMENT empiezan en 1, así
-- que se pueden usar los ids directamente (id_rol 1 = administrador,
-- id_usuario 1 = admin, etc.).
-- =====================================================================

USE friends4you;

SET NAMES utf8mb4;


-- ---------------------------------------------------------------------
-- Roles (id_rol 1 = administrador, 2 = usuario, 3 = colaborador)
-- ---------------------------------------------------------------------
INSERT INTO rol (nombre) VALUES
('administrador'),
('usuario'),
('colaborador');


-- ---------------------------------------------------------------------
-- Usuarios de prueba
-- La contraseña '1234' está hasheada con la función password_hash de PHP.
-- id_usuario 1 = admin, 2 = Lucía, 3 = Carlos, 4 = Marta,
--           5 = Pádel Club, 6 = Cafetería Plaza
-- ---------------------------------------------------------------------
INSERT INTO usuario (nombre, apellidos, correo, contrasena, ciudad, id_rol) VALUES
('Admin',      'Friends4You',  'admin@friends4you.com',         '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Málaga',  1),
('Lucía',      'Martín López', 'lucia@friends4you.com',         '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Málaga',  2),
('Carlos',     'García Ruiz',  'carlos@friends4you.com',        '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Sevilla', 2),
('Marta',      'Sánchez Mora', 'marta@friends4you.com',         '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Málaga',  2),
('Pádel Club', 'Centro',       'padelclub@friends4you.com',     '$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Málaga',  3),
('Cafetería',  'Plaza',        'cafeteriaplaza@friends4you.com','$2y$10$xT1v7InCwQh049352GkUVe.i5TuW8uJOqKY5xlcI89uizH4jPV/ei', 'Sevilla', 3);


-- ---------------------------------------------------------------------
-- Intereses (id_interes en el orden de inserción: 1 = Pádel, 2 = Cine, ...)
-- ---------------------------------------------------------------------
INSERT INTO interes (nombre) VALUES
('Pádel'),           --  1
('Cine'),            --  2
('Senderismo'),      --  3
('Videojuegos'),     --  4
('Cafés y charlas'), --  5
('Música'),          --  6
('Fotografía'),      --  7
('Lectura'),         --  8
('Cocina'),          --  9
('Idiomas'),         -- 10
('Teatro'),          -- 11
('Running'),         -- 12
('Gimnasio'),        -- 13
('Juegos de mesa'),  -- 14
('Tecnología'),      -- 15
('Viajes'),          -- 16
('Voluntariado'),    -- 17
('Baile'),           -- 18
('Arte'),            -- 19
('Escritura'),       -- 20
('Anime'),           -- 21
('Astronomía');      -- 22


-- ---------------------------------------------------------------------
-- Preferencias (qué intereses tiene cada usuario)
-- ---------------------------------------------------------------------
INSERT INTO preferencia (id_usuario, id_interes) VALUES
(2, 2),  -- Lucía           - Cine
(2, 5),  -- Lucía           - Cafés y charlas
(2, 6),  -- Lucía           - Música
(3, 1),  -- Carlos          - Pádel
(3, 4),  -- Carlos          - Videojuegos
(4, 2),  -- Marta           - Cine
(4, 3),  -- Marta           - Senderismo
(5, 1),  -- Pádel Club      - Pádel
(6, 5);  -- Cafetería Plaza - Cafés y charlas


-- ---------------------------------------------------------------------
-- Fichas de los colaboradores (locales), asociadas a sus cuentas
-- ---------------------------------------------------------------------
INSERT INTO colaborador (nombre, direccion, ciudad, descripcion, id_usuario_colaborador) VALUES
('Pádel Club Centro', 'Avenida del Deporte 12', 'Málaga',
 'Club deportivo con pistas de pádel para quedadas de nivel inicial.', 5),
('Cafetería Plaza',   'Plaza Mayor 4',          'Sevilla',
 'Cafetería céntrica para charlas, juegos de mesa y encuentros tranquilos.', 6);


-- ---------------------------------------------------------------------
-- Eventos de ejemplo
-- ---------------------------------------------------------------------
INSERT INTO evento (id_creador, nombre, descripcion, fecha_hora, punto_encuentro, id_interes, id_colaborador, estado_evento) VALUES
(5, 'Partido de pádel para principiantes',
    'Quedada tranquila para aprender y jugar un partido amistoso.',
    '2026-09-12 18:00:00', 'Recepción de Pádel Club Centro',     1, 1,    'activo'),
(4, 'Ruta de senderismo del domingo',
    'Ruta sencilla de mañana para conocer gente y caminar en grupo.',
    '2026-09-19 09:30:00', 'Entrada principal del parque natural', 3, NULL, 'activo'),
(6, 'Café y charla entre usuarios',
    'Encuentro informal para conversar y conocer nuevos amigos.',
    '2026-09-26 17:30:00', 'Mesa reservada en Cafetería Plaza',    5, 2,    'activo');


-- ---------------------------------------------------------------------
-- Amistades iniciales
-- ---------------------------------------------------------------------
INSERT INTO amistad (usuario_origen, usuario_destino, estado) VALUES
(2, 3, 'aceptada'),   -- Lucía y Carlos ya son amigos
(4, 2, 'pendiente');  -- Marta envió una solicitud a Lucía


-- ---------------------------------------------------------------------
-- Asistencias confirmadas a los eventos
-- ---------------------------------------------------------------------
INSERT INTO asistencia (id_usuario, id_evento, estado_asistencia) VALUES
(2, 1, 'confirmada'),  -- Lucía  al partido de pádel
(3, 1, 'confirmada'),  -- Carlos al partido de pádel
(2, 3, 'confirmada'),  -- Lucía  al café y charla
(4, 3, 'confirmada');  -- Marta  al café y charla
