-- =====================================================================
-- Friends4You — Script 3: creación de usuarios y privilegios MySQL
-- ---------------------------------------------------------------------
-- Crea tres cuentas de MySQL con privilegios diferenciados para cumplir
-- el requisito académico de "usuarios con roles y privilegios necesarios":
--
--   * admin_f4y     -> ALL PRIVILEGES sobre la BD. Para tareas de
--                      administración (importar, exportar, reparar).
--   * app_f4y       -> SELECT, INSERT, UPDATE, DELETE. Es la que usa la
--                      aplicación web en producción (la mínima necesaria
--                      para que funcione la app, sin permisos de DDL).
--   * consulta_f4y  -> SELECT (solo lectura). Pensada para herramientas
--                      de informes externos que no deben modificar datos.
--
-- Ejecutar tras 01 y 02. No es necesario para entrar a la aplicación con
-- el usuario root (en XAMPP por defecto), pero sí para cumplir el
-- enunciado de la tarea.
--
-- Los usuarios se definen con host '%' para permitir la conexión tanto
-- desde localhost (XAMPP) como desde contenedores Docker (red interna).
-- =====================================================================

CREATE USER IF NOT EXISTS 'admin_f4y'@'%' IDENTIFIED BY 'Admin1234!';
GRANT ALL PRIVILEGES ON friends4you.* TO 'admin_f4y'@'%';

CREATE USER IF NOT EXISTS 'app_f4y'@'%' IDENTIFIED BY 'App1234!';
GRANT SELECT, INSERT, UPDATE, DELETE ON friends4you.* TO 'app_f4y'@'%';

CREATE USER IF NOT EXISTS 'consulta_f4y'@'%' IDENTIFIED BY 'Consulta1234!';
GRANT SELECT ON friends4you.* TO 'consulta_f4y'@'%';

-- Aplica los cambios de privilegios inmediatamente.
FLUSH PRIVILEGES;
