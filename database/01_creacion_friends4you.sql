-- =====================================================================
-- Friends4You — Script 01: creación de la base de datos y de las tablas
-- ---------------------------------------------------------------------
-- Crea la BD friends4you y sus 8 tablas con sus claves, índices y
-- restricciones de integridad referencial. Ejecutar este script primero.
--
-- Nota de compatibilidad con MySQL 8:
-- La regla "un usuario no puede tener amistad consigo mismo" se aplica
-- mediante TRIGGERS (BEFORE INSERT/UPDATE) en lugar de un CHECK
-- constraint. MySQL 8 no permite CHECK constraints sobre columnas que
-- forman parte de una FOREIGN KEY con acciones referenciales
-- (ON UPDATE/DELETE CASCADE), como ocurre en la tabla `amistad`.
-- Los triggers son compatibles tanto con MySQL 8.4 como con MariaDB
-- (XAMPP), garantizando portabilidad entre ambos entornos.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS friends4you
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE friends4you;

CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE usuario (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    id_rol INT NOT NULL,
    CONSTRAINT fk_usuario_rol
        FOREIGN KEY (id_rol) REFERENCES rol(id_rol)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE interes (
    id_interes INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

CREATE TABLE preferencia (
    id_usuario INT NOT NULL,
    id_interes INT NOT NULL,
    PRIMARY KEY (id_usuario, id_interes),
    CONSTRAINT fk_preferencia_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_preferencia_interes
        FOREIGN KEY (id_interes) REFERENCES interes(id_interes)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE colaborador (
    id_colaborador INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    direccion VARCHAR(150) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    descripcion TEXT,
    id_usuario_colaborador INT NOT NULL UNIQUE,
    CONSTRAINT fk_colaborador_usuario
        FOREIGN KEY (id_usuario_colaborador) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

CREATE TABLE evento (
    id_evento INT AUTO_INCREMENT PRIMARY KEY,
    id_creador INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_hora DATETIME NOT NULL,
    punto_encuentro VARCHAR(150) NOT NULL,
    id_interes INT NOT NULL,
    id_colaborador INT NULL,
    estado_evento ENUM('activo', 'cancelado', 'finalizado') NOT NULL DEFAULT 'activo',
    CONSTRAINT fk_evento_creador
        FOREIGN KEY (id_creador) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_evento_interes
        FOREIGN KEY (id_interes) REFERENCES interes(id_interes)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_evento_colaborador
        FOREIGN KEY (id_colaborador) REFERENCES colaborador(id_colaborador)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

CREATE TABLE amistad (
    usuario_origen INT NOT NULL,
    usuario_destino INT NOT NULL,
    fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('pendiente', 'aceptada', 'rechazada') NOT NULL DEFAULT 'pendiente',
    PRIMARY KEY (usuario_origen, usuario_destino),
    CONSTRAINT fk_amistad_origen
        FOREIGN KEY (usuario_origen) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_amistad_destino
        FOREIGN KEY (usuario_destino) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

DELIMITER //

CREATE TRIGGER trg_amistad_no_self_insert
BEFORE INSERT ON amistad
FOR EACH ROW
BEGIN
    IF NEW.usuario_origen = NEW.usuario_destino THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se permite una amistad consigo mismo';
    END IF;
END//

CREATE TRIGGER trg_amistad_no_self_update
BEFORE UPDATE ON amistad
FOR EACH ROW
BEGIN
    IF NEW.usuario_origen = NEW.usuario_destino THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'No se permite una amistad consigo mismo';
    END IF;
END//

DELIMITER ;

CREATE TABLE asistencia (
    id_usuario INT NOT NULL,
    id_evento INT NOT NULL,
    estado_asistencia ENUM('pendiente', 'confirmada', 'cancelada') NOT NULL DEFAULT 'pendiente',
    PRIMARY KEY (id_usuario, id_evento),
    CONSTRAINT fk_asistencia_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_asistencia_evento
        FOREIGN KEY (id_evento) REFERENCES evento(id_evento)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);
