-- =====================================================================
-- Friends4You — Script 03: creación de usuarios y privilegios MySQL
-- ---------------------------------------------------------------------
-- Crea tres cuentas MySQL con privilegios diferenciados:
--   - admin_f4y      → ALL PRIVILEGES (administración total).
--   - app_f4y        → SELECT/INSERT/UPDATE/DELETE (uso desde la aplicación).
--   - consulta_f4y   → SELECT (solo lectura, para informes externos).
-- Ejecutar tras 01 y 02. No es necesario para entrar a la aplicación
-- usando el usuario root, pero sí para cumplir el requisito académico
-- de "usuarios con roles y privilegios necesarios".
-- Los usuarios se definen con '%' para permitir conexión tanto desde
-- localhost (XAMPP) como desde contenedores Docker.
-- =====================================================================

CREATE USER IF NOT EXISTS 'admin_f4y'@'%' IDENTIFIED BY 'Admin1234!';
GRANT ALL PRIVILEGES ON friends4you.* TO 'admin_f4y'@'%';

CREATE USER IF NOT EXISTS 'app_f4y'@'%' IDENTIFIED BY 'App1234!';
GRANT SELECT, INSERT, UPDATE, DELETE ON friends4you.* TO 'app_f4y'@'%';

CREATE USER IF NOT EXISTS 'consulta_f4y'@'%' IDENTIFIED BY 'Consulta1234!';
GRANT SELECT ON friends4you.* TO 'consulta_f4y'@'%';

FLUSH PRIVILEGES;