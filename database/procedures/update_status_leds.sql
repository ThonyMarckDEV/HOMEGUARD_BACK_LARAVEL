BEGIN
    DECLARE hora_actual TIME;

    -- Obtener la hora actual
    SET hora_actual = CURTIME();

    -- Encender luces si la hora de encendido es válida (y opcionalmente si no ha pasado la hora de apagado)
    UPDATE leds
    SET state = 1
    WHERE (hora_encendido IS NOT NULL AND hora_encendido <= hora_actual)
      AND (hora_apagado IS NULL OR hora_apagado > hora_actual)
      AND state = 0;

    -- Encender luces si solo tiene hora de encendido y la hora actual ya ha llegado (sin importar la hora de apagado)
    UPDATE leds
    SET state = 1
    WHERE hora_encendido IS NOT NULL
      AND hora_encendido <= hora_actual
      AND hora_apagado IS NULL
      AND state = 0;

    -- Apagar luces si la hora de apagado es válida y la luz está encendida
    UPDATE leds
    SET state = 0
    WHERE (hora_apagado IS NOT NULL AND hora_apagado <= hora_actual)
      AND state = 1;

    -- Apagar luces si solo tiene hora de apagado y la hora actual ha llegado (sin importar la hora de encendido)
    UPDATE leds
    SET state = 0
    WHERE hora_apagado IS NOT NULL
      AND hora_apagado <= hora_actual
      AND hora_encendido IS NULL
      AND state = 1;

    -- Opcional: Imprimir mensaje si es necesario
    SELECT 'Luces actualizadas' AS mensaje;
END