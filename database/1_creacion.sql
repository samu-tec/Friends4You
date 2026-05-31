-- =====================================================================
-- Friends4You — Script 1: creación de la base de datos y de las tablas
-- ---------------------------------------------------------------------
-- Crea la BD friends4you y sus 8 tablas con sus claves, índices y
-- restricciones de integridad referencial. Es el primer script a ejecutar.
--
-- Diseño de roles:
--   * usuario      -> persona; tiene perfil, intereses, amistades y
--                     asistencias a eventos.
--   * colaborador  -> cuenta de un local; tiene una ficha en la tabla
--                     colaborador y crea eventos asociados a su local.
--                     No participa en amistades ni en asistencias.
--   * administrador-> cuenta de supervisión; gestiona y elimina datos
--                     desde el panel. No participa en la parte social.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS friends4you
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE friends4you;

SET NAMES utf8mb4;

-- Tabla de roles. Solo se usan tres: administrador, usuario y colaborador.
CREATE TABLE rol (
    id_rol INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Tabla de usuarios. Cada cuenta tiene exactamente un rol (FK a la tabla rol).
-- La contraseña se guarda como hash bcrypt (función password_hash de PHP).
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

-- Categorías de intereses (Cine, Pádel, Senderismo...). Las gestionan los
-- administradores y los propios usuarios pueden crear nuevos desde el perfil.
CREATE TABLE interes (
    id_interes INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

-- Tabla intermedia para la relación muchos-a-muchos entre usuario e interes.
-- La clave primaria compuesta (id_usuario, id_interes) impide duplicados.
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

-- Ficha de los locales colaboradores. Cada ficha está asociada a una sola
-- cuenta con rol "colaborador" (id_usuario_colaborador es UNIQUE).
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
        ON DELETE CASCADE
);

-- Eventos creados por los usuarios. id_colaborador es opcional.
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
        ON DELETE CASCADE,
    CONSTRAINT fk_evento_interes
        FOREIGN KEY (id_interes) REFERENCES interes(id_interes)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,
    CONSTRAINT fk_evento_colaborador
        FOREIGN KEY (id_colaborador) REFERENCES colaborador(id_colaborador)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- Solicitudes y amistades entre usuarios.
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

-- Asistencias de los usuarios a los eventos.
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
