DELIMITER $$
--
-- Procedimientos
--
CREATE PROCEDURE update_inactive_users ()
BEGIN
    DECLARE rows_affected INT;

    -- Ejecuta el UPDATE para desloguear usuarios inactivos
    UPDATE usuarios
    INNER JOIN actividad_usuario ON usuarios.idUsuario = actividad_usuario.idUsuario
    SET usuarios.status = 'loggedOff'
    WHERE actividad_usuario.last_activity < NOW() - INTERVAL 1 MINUTE
      AND usuarios.status = 'loggedOn';

    -- Captura la cantidad de filas afectadas
    SET rows_affected = ROW_COUNT();

    -- Inserta un registro en event_log solo si hubo filas afectadas
    IF rows_affected > 0 THEN
        INSERT INTO event_log (event_name, execution_time, affected_rows)
        VALUES ('update_inactive_users', NOW(), rows_affected);
    END IF;
END$$