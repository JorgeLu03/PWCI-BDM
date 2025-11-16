-- Agregar stored procedure para eliminar mundiales
-- Fecha: 2025-11-14

USE `bdm-pwci2`;

DROP PROCEDURE IF EXISTS `SP_DeleteMundial`;

DELIMITER $$

CREATE PROCEDURE `SP_DeleteMundial` (IN `p_ID_Mundial` INT)
BEGIN
    -- Eliminar publicaciones asociadas a este mundial
    DELETE FROM publicacion WHERE ID_Mundial = p_ID_Mundial;
    
    -- Eliminar el mundial
    DELETE FROM mundial WHERE ID_Mundial = p_ID_Mundial;
END$$

DELIMITER ;
