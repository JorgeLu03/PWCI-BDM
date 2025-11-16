-- Agregar stored procedure para eliminar categorías
-- Fecha: 2025-11-14

USE `bdm-pwci2`;

DROP PROCEDURE IF EXISTS `SP_DeleteCategory`;

DELIMITER $$

CREATE PROCEDURE `SP_DeleteCategory` (IN `p_ID_Categ` INT)
BEGIN
    -- Eliminar publicaciones asociadas a esta categoría
    DELETE FROM publicacion WHERE ID_Categ = p_ID_Categ;
    
    -- Eliminar la categoría
    DELETE FROM categoria WHERE ID_Categ = p_ID_Categ;
END$$

DELIMITER ;
