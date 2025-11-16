-- ============================================================================
-- SCRIPT DE ACTUALIZACIÓN: NUEVAS FUNCIONALIDADES SQL
-- Proyecto: BDM-PWCI
-- Fecha: 14 de Noviembre de 2025
-- Descripción: Agrega 2 funciones, 2 triggers y 4 vistas al proyecto existente
-- ============================================================================

USE bdmpwci2;

-- ============================================================================
-- SECCIÓN 1: FUNCIONES (2)
-- ============================================================================

DELIMITER $$

-- Función 1: Calcular edad de usuario
DROP FUNCTION IF EXISTS `FN_CalcularEdadUsuario`$$
CREATE FUNCTION `FN_CalcularEdadUsuario` (`p_ID_User` INT) 
RETURNS INT(11) 
DETERMINISTIC 
BEGIN
    DECLARE v_edad INT;
    DECLARE v_fecha_nac DATE;
    
    SELECT Fec_nac INTO v_fecha_nac 
    FROM usuario 
    WHERE ID_User = p_ID_User;
    
    IF v_fecha_nac IS NULL THEN
        RETURN NULL;
    END IF;
    
    SET v_edad = TIMESTAMPDIFF(YEAR, v_fecha_nac, CURDATE());
    
    RETURN v_edad;
END$$

-- Función 2: Contar publicaciones de usuario por estado
DROP FUNCTION IF EXISTS `FN_ContarPublicacionesPorEstado`$$
CREATE FUNCTION `FN_ContarPublicacionesPorEstado` (`p_ID_User` INT, `p_Estatus` TINYINT) 
RETURNS INT(11) 
DETERMINISTIC 
BEGIN
    DECLARE v_contador INT;
    
    SELECT COUNT(*) INTO v_contador
    FROM publicacion
    WHERE ID_User = p_ID_User AND Estatus = p_Estatus;
    
    RETURN IFNULL(v_contador, 0);
END$$

DELIMITER ;

-- ============================================================================
-- SECCIÓN 2: TRIGGERS (2)
-- ============================================================================

DELIMITER $$

-- Trigger 1: Actualizar fecha de aprobación automáticamente
DROP TRIGGER IF EXISTS `TRG_ActualizarFechaAprobacion`$$
CREATE TRIGGER `TRG_ActualizarFechaAprobacion` 
BEFORE UPDATE ON `publicacion`
FOR EACH ROW 
BEGIN
    -- Si el estatus cambia a 2 (Aprobada) y no tenía fecha de aprobación
    IF NEW.Estatus = 2 AND OLD.Estatus != 2 THEN
        SET NEW.Fec_aprob = CURDATE();
    END IF;
    
    -- Si el estatus cambia a 3 (Rechazada), limpiar fecha de aprobación
    IF NEW.Estatus = 3 THEN
        SET NEW.Fec_aprob = NULL;
    END IF;
END$$

-- Trigger 2: Validar comentario antes de insertar
DROP TRIGGER IF EXISTS `TRG_ValidarComentario`$$
CREATE TRIGGER `TRG_ValidarComentario` 
BEFORE INSERT ON `comentario`
FOR EACH ROW 
BEGIN
    -- Validar que el contenido no esté vacío
    IF TRIM(NEW.Contenido) = '' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'El contenido del comentario no puede estar vacío';
    END IF;
    
    -- Establecer fecha actual si no se proporcionó
    IF NEW.Fec IS NULL THEN
        SET NEW.Fec = NOW();
    END IF;
    
    -- Establecer estatus por defecto como 'A' (Aprobado) si no se especificó
    IF NEW.Estatus IS NULL THEN
        SET NEW.Estatus = 'A';
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- SECCIÓN 3: VISTAS (4 Nuevas)
-- ============================================================================

-- Vista 1: Estadísticas de publicaciones por usuario
DROP VIEW IF EXISTS `V_EstadisticasPublicaciones`;
CREATE VIEW `V_EstadisticasPublicaciones` AS
SELECT 
    u.ID_User,
    u.Nombre AS Nombre_Usuario,
    COUNT(DISTINCT p.ID_Publi) AS Total_Publicaciones,
    SUM(CASE WHEN p.Estatus = 2 THEN 1 ELSE 0 END) AS Publicaciones_Aprobadas,
    SUM(CASE WHEN p.Estatus = 1 THEN 1 ELSE 0 END) AS Publicaciones_Pendientes,
    SUM(CASE WHEN p.Estatus = 3 THEN 1 ELSE 0 END) AS Publicaciones_Rechazadas,
    IFNULL(SUM(p.Views), 0) AS Total_Vistas,
    IFNULL(AVG(p.Views), 0) AS Promedio_Vistas
FROM usuario u
LEFT JOIN publicacion p ON u.ID_User = p.ID_User
GROUP BY u.ID_User, u.Nombre;

-- Vista 2: Comentarios con información del usuario (para mostrar en cualquier publicación)
DROP VIEW IF EXISTS `V_ComentariosPublicacion`;
CREATE VIEW `V_ComentariosPublicacion` AS
SELECT 
    c.ID_Coment,
    c.Contenido,
    c.Fec AS Fecha_Comentario,
    c.Estatus,
    c.ID_Publi,
    c.ID_User,
    u.Nombre AS Nombre_Usuario,
    u.Foto AS Foto_Usuario
FROM comentario c
INNER JOIN usuario u ON c.ID_User = u.ID_User
ORDER BY c.Fec DESC;

-- Vista 3: Publicaciones con detalles completos (puede reemplazar JOIN en queries)
DROP VIEW IF EXISTS `V_PublicacionesConDetalles`;
CREATE VIEW `V_PublicacionesConDetalles` AS
SELECT 
    p.ID_Publi,
    p.Titulo,
    p.Descripcion,
    p.Estatus,
    p.Views,
    p.Fec_aprob,
    p.Fec_pub,
    p.Multimedia,
    p.TipoMultimedia,
    p.MotivoRechazo,
    p.ID_Categ,
    c.Nombre AS Nombre_Categoria,
    p.ID_Mundial,
    m.Nombre AS Nombre_Mundial,
    m.Anio AS Anio_Mundial,
    p.ID_User,
    u.Nombre AS Nombre_Usuario,
    u.Foto AS Foto_Usuario,
    (SELECT COUNT(*) FROM usuario_reaccion ur WHERE ur.ID_Publi = p.ID_Publi AND ur.Estatus = 'L') AS LikeCount,
    (SELECT COUNT(*) FROM comentario cm WHERE cm.ID_Publi = p.ID_Publi AND cm.Estatus = 2) AS CommentCount
FROM publicacion p
INNER JOIN categoria c ON p.ID_Categ = c.ID_Categ
INNER JOIN mundial m ON p.ID_Mundial = m.ID_Mundial
INNER JOIN usuario u ON p.ID_User = u.ID_User;

-- Vista 4: Mundiales con estadísticas de publicaciones
DROP VIEW IF EXISTS `V_MundialesConEstadisticas`;
CREATE VIEW `V_MundialesConEstadisticas` AS
SELECT 
    m.ID_Mundial,
    m.Nombre,
    m.Anio,
    m.Sede,
    m.Campeon,
    m.Subcampeon,
    m.TercerLugar,
    m.CuartoLugar,
    m.Descripcion,
    m.Logo,
    m.Banner,
    m.Balon,
    m.Fec_Final,
    m.Lugar_Final,
    m.Marcador_Final,
    m.TiempoExtra_Final,
    m.Goleador,
    m.Alineacion_Campeon,
    m.Cantante,
    m.Views,
    m.ID_User,
    COUNT(DISTINCT p.ID_Publi) AS Total_Publicaciones
FROM mundial m
LEFT JOIN publicacion p ON m.ID_Mundial = p.ID_Mundial AND p.Estatus = 2
GROUP BY m.ID_Mundial, m.Nombre, m.Anio, m.Sede, m.Campeon, m.Subcampeon, 
         m.TercerLugar, m.CuartoLugar, m.Descripcion, m.Logo, m.Banner, 
         m.Balon, m.Fec_Final, m.Lugar_Final, m.Marcador_Final, 
         m.TiempoExtra_Final, m.Goleador, m.Alineacion_Campeon, m.Cantante, 
         m.Views, m.ID_User;

-- ============================================================================
-- VERIFICACIÓN: Mostrar lo que se creó
-- ============================================================================

SELECT '=== FUNCIONES CREADAS ===' AS Info;
SHOW FUNCTION STATUS WHERE Db = 'bdmpwci2' AND Name LIKE 'FN_%';

SELECT '=== TRIGGERS CREADOS ===' AS Info;
SHOW TRIGGERS WHERE `Table` IN ('publicacion', 'comentario');

SELECT '=== VISTAS CREADAS ===' AS Info;
SHOW FULL TABLES WHERE Table_type = 'VIEW' AND Tables_in_bdmpwci2 LIKE 'V_%';

-- ============================================================================
-- PRUEBAS RÁPIDAS (Opcional - comentar si no quieres ejecutar)
-- ============================================================================

-- Probar función de edad
-- SELECT FN_CalcularEdadUsuario(15) AS Edad_Usuario_15;

-- Probar función de conteo de publicaciones por estado
-- SELECT FN_ContarPublicacionesPorEstado(15, 2) AS Publicaciones_Aprobadas_Usuario_15;

-- Probar vista de estadísticas
-- SELECT * FROM V_EstadisticasPublicaciones LIMIT 5;

-- Probar vista de comentarios
-- SELECT * FROM V_ComentariosPublicacion WHERE ID_Publi = 12 LIMIT 10;

-- Probar vista de publicaciones con detalles
-- SELECT ID_Publi, Titulo, Nombre_Categoria, Nombre_Mundial FROM V_PublicacionesConDetalles WHERE Estatus = 2 LIMIT 10;

-- Probar vista de mundiales con estadísticas
-- SELECT Nombre, Anio, Total_Publicaciones FROM V_MundialesConEstadisticas ORDER BY Anio DESC;

-- ============================================================================
-- FIN DEL SCRIPT
-- ============================================================================

SELECT '✅ SCRIPT EJECUTADO CORRECTAMENTE' AS Status;
SELECT 'Revisa los resultados arriba para verificar que todo se creó' AS Instruccion;
