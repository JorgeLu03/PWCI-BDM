-- Agregar stored procedure para eliminar publicaciones
-- Fecha: 2025-11-14

USE `bdm-pwci2`;

DROP PROCEDURE IF EXISTS `SP_DeletePublicationComplete`;

DELIMITER $$

CREATE PROCEDURE `SP_DeletePublicationComplete` (IN `p_ID_Publi` INT)
BEGIN
    -- Eliminar comentarios de la publicación
    DELETE FROM comentario WHERE ID_Publi = p_ID_Publi;
    
    -- Eliminar reacciones (likes) de la publicación
    DELETE FROM usuario_reaccion WHERE ID_Publi = p_ID_Publi;
    
    -- Eliminar vistas de la publicación (si existe la tabla)
    DELETE FROM vistas WHERE FK_PUBLICACION = p_ID_Publi;
    
    -- Eliminar la publicación
    DELETE FROM publicacion WHERE ID_Publi = p_ID_Publi;
    
    -- Retornar resultado exitoso
    SELECT 1 AS success;
END$$

DELIMITER ;
