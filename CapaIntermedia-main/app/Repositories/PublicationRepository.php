<?php
class PublicationRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getForEdit(int $pubId, int $userId): ?array
    {
        $stmt = $this->db->prepare('CALL SP_GetPublicationForEdit(?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetPublicationForEdit: ' . $this->db->error);
        }
        $stmt->bind_param('ii', $pubId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = ($res && $res->num_rows > 0) ? $res->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $row;
    }

    public function updatePublication(int $pubId, string $titulo, string $descripcion, ?string $mediaType, ?string $mediaTmpPath, int $idCateg, int $idMundial): bool
    {
        $stmt = $this->db->prepare('CALL SP_UpdatePublication(?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_UpdatePublication: ' . $this->db->error);
        }
        $nullBlob = null;
        $stmt->bind_param('issbsii', $pubId, $titulo, $descripcion, $nullBlob, $mediaType, $idCateg, $idMundial);

        if ($mediaTmpPath && is_file($mediaTmpPath)) {
            $fp = fopen($mediaTmpPath, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $chunk = fread($fp, 8192);
                    $stmt->send_long_data(3, $chunk);
                }
                fclose($fp);
            }
        }
        $ok = $stmt->execute();
        if (!$ok) {
            $err = $stmt->error;
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            throw new RuntimeException('Error al actualizar publicación: ' . $err);
        }
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return true;
    }

    public function insertPublication(string $titulo, string $descripcion, int $estatus, int $views, ?string $fecAprob, string $fecPub, ?string $mediaType, ?string $mediaTmpPath, int $idCateg, int $idUser, int $idMundial): bool
    {
        $stmt = $this->db->prepare('CALL Insertar_Publicacion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar Insertar_Publicacion: ' . $this->db->error);
        }
        $nullBlob = null;
        $stmt->bind_param('ssisssbsiii', $titulo, $descripcion, $estatus, $views, $fecAprob, $fecPub, $nullBlob, $mediaType, $idCateg, $idUser, $idMundial);

        if ($mediaTmpPath && is_file($mediaTmpPath)) {
            $fp = fopen($mediaTmpPath, 'r');
            if ($fp) {
                while (!feof($fp)) {
                    $chunk = fread($fp, 8192);
                    $stmt->send_long_data(6, $chunk); // El 7º '?' (índice 6) es el BLOB
                }
                fclose($fp);
            }
        }
        $ok = $stmt->execute();
        if (!$ok) {
            $err = $stmt->error;
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            throw new RuntimeException('Error al insertar publicación: ' . $err);
        }
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return true;
    }

    /**
     * Obtiene todas las publicaciones aprobadas ordenadas según el criterio
     * @param string $sortBy 'recent', 'likes', o 'comments'
     * @return array Lista de publicaciones
     */
    public function getApprovedPublications(string $sortBy = 'recent'): array
    {
        // Determinar qué procedimiento almacenado llamar
        $sp = 'CALL Mostrar_Publicacion()';
        if ($sortBy === 'likes') {
            $sp = 'CALL SP_GetPostsByLikes()';
        } elseif ($sortBy === 'comments') {
            $sp = 'CALL SP_GetPostsByComments()';
        }

        $result = $this->db->query($sp);
        if (!$result) {
            throw new RuntimeException("Error al obtener publicaciones: " . $this->db->error);
        }

        $publications = [];
        while ($row = $result->fetch_assoc()) {
            $publications[] = $row;
        }
        $result->free();

        // Limpia resultados adicionales
        while ($this->db->more_results() && $this->db->next_result()) {
            if ($res = $this->db->store_result()) {
                $res->free();
            }
        }

        return $publications;
    }

    /**
     * Obtiene todas las publicaciones de un usuario específico
     * @param int $userId ID del usuario
     * @return array Lista de publicaciones del usuario
     */
    public function getUserPublications(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT ID_Publi, Titulo, Descripcion, Fec_pub, Multimedia, TipoMultimedia, Views, LikeCount, CommentCount, Estatus, MotivoRechazo
             FROM V_Publicaciones 
             WHERE ID_User = ? 
             ORDER BY Fec_pub DESC"
        );
        if (!$stmt) {
            throw new RuntimeException('Error al preparar consulta de publicaciones del usuario: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $publications;
    }

    /**
     * Obtiene los detalles de una publicación específica e incrementa las vistas
     * @param int $publiId ID de la publicación
     * @return array|null Datos de la publicación o null si no existe
     */
    public function getPublicationDetail(int $publiId): ?array
    {
        // Incrementar vistas
        $stmt_views = $this->db->prepare("CALL ACTUALIZAR_VISTAS(?)");
        if ($stmt_views) {
            $stmt_views->bind_param('i', $publiId);
            $stmt_views->execute();
            $stmt_views->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
        }

        // Obtener detalles de la publicación
        $stmt = $this->db->prepare("CALL Mostrar_Publicacion_Especifica(?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar Mostrar_Publicacion_Especifica: ' . $this->db->error);
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $publication = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publication;
    }

    /**
     * Obtiene el conteo de likes de una publicación
     * @param int $publiId ID de la publicación
     * @return int Número de likes
     */
    public function getLikeCount(int $publiId): int
    {
        $stmt = $this->db->prepare("CALL SP_GetLikeCount(?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetLikeCount: ' . $this->db->error);
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $like_count = $result ? $result->fetch_assoc()['like_count'] : 0;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $like_count;
    }

    /**
     * Verifica si un usuario ha dado like a una publicación
     * @param int $userId ID del usuario
     * @param int $publiId ID de la publicación
     * @return bool true si el usuario ya dio like
     */
    public function checkUserLike(int $userId, int $publiId): bool
    {
        $stmt = $this->db->prepare("CALL SP_CheckUserLike(?, ?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_CheckUserLike: ' . $this->db->error);
        }
        $stmt->bind_param('ii', $userId, $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_liked = $result ? $result->fetch_assoc()['user_liked'] > 0 : false;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $user_liked;
    }

    /**
     * Obtiene los comentarios de una publicación
     * @param int $publiId ID de la publicación
     * @return array Lista de comentarios
     */
    public function getComments(int $publiId): array
    {
        $stmt = $this->db->prepare("CALL SP_GetCommentsByPost(?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetCommentsByPost: ' . $this->db->error);
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $comments;
    }

    /**
     * Obtiene todas las publicaciones pendientes de aprobación
     * @return array Lista de publicaciones pendientes
     */
    public function getPendingPublications(): array
    {
        $stmt = $this->db->prepare("CALL SP_GetPendingPublications()");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetPendingPublications: ' . $this->db->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publications;
    }

    /**
     * Obtiene todos los comentarios pendientes de aprobación
     * @return array Lista de comentarios pendientes
     */
    public function getPendingComments(): array
    {
        $stmt = $this->db->prepare("CALL SP_GetPendingComments()");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetPendingComments: ' . $this->db->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $comments;
    }

    /**
     * Busca publicaciones por término
     * @param string $searchTerm Término de búsqueda
     * @return array Lista de publicaciones encontradas
     */
    public function searchPublications(string $searchTerm): array
    {
        $stmt = $this->db->prepare("CALL SP_SearchPublications(?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_SearchPublications: ' . $this->db->error);
        }
        $stmt->bind_param('s', $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publications;
    }

    /**
     * Toggle like on a publication
     * @param int $userId User ID
     * @param int $publiId Publication ID
     * @return string 'liked' or 'unliked'
     */
    public function toggleLike(int $userId, int $publiId): string
    {
        $stmt = $this->db->prepare("CALL SP_ToggleLike(?, ?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_ToggleLike: ' . $this->db->error);
        }
        $stmt->bind_param('ii', $userId, $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $status = $result ? $result->fetch_assoc()['status'] : 'unliked';
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $status;
    }

    /**
     * Add a comment to a publication
     * @param string $content Comment content
     * @param int $userId User ID
     * @param int $publiId Publication ID
     * @return bool Success status
     */
    public function addComment(string $content, int $userId, int $publiId): bool
    {
        $stmt = $this->db->prepare("CALL SP_AddComment(?, ?, ?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('sii', $content, $userId, $publiId);
        $success = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $success;
    }

    /**
     * Update publication status (approve/reject)
     * @param int $publiId Publication ID
     * @param int $newStatus New status (2=approved, 3=rejected)
     * @param string|null $reason Rejection reason
     * @return bool Success status
     */
    public function updatePublicationStatus(int $publiId, int $newStatus, ?string $reason): bool
    {
        $stmt = $this->db->prepare("CALL SP_UpdatePublicationStatus(?, ?, ?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('iis', $publiId, $newStatus, $reason);
        $success = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $success;
    }

    /**
     * Update comment status (approve/reject)
     * @param int $commentId Comment ID
     * @param int $newStatus New status (2=approved, 3=rejected)
     * @return bool Success status
     */
    public function updateCommentStatus(int $commentId, int $newStatus): bool
    {
        $stmt = $this->db->prepare("CALL SP_UpdateCommentStatus(?, ?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('ii', $commentId, $newStatus);
        $success = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $success;
    }

    /**
     * Delete a comment
     * @param int $commentId Comment ID
     * @param int $userId User ID (for verification)
     * @return bool Success status
     */
    public function deleteComment(int $commentId, int $userId): bool
    {
        $stmt = $this->db->prepare("CALL SP_DeleteCommentByAdmin(?)");
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param('i', $commentId);
        $success = $stmt->execute();
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $success;
    }

    /**
     * Get users who liked a publication
     * @param int $publiId Publication ID
     * @return array List of users
     */
    public function getLikers(int $publiId): array
    {
        $stmt = $this->db->prepare("CALL SP_GetLikers(?)");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $likers = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $likers;
    }

    /**
     * Get users who commented on a publication
     * @param int $publiId Publication ID
     * @return array List of users
     */
    public function getCommenters(int $publiId): array
    {
        $stmt = $this->db->prepare("CALL SP_GetCommenters(?)");
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $commenters = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $commenters;
    }

    /**
     * Toggle like on a comment
     * @param int $userId User ID
     * @param int $commentId Comment ID
     * @return string 'liked' or 'unliked'
     */
    public function toggleCommentLike(int $userId, int $commentId): string
    {
        $stmt = $this->db->prepare("CALL SP_ToggleCommentLike(?, ?)");
        if (!$stmt) {
            return 'unliked';
        }
        $stmt->bind_param('ii', $userId, $commentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $status = $result ? $result->fetch_assoc()['status'] : 'unliked';
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $status;
    }
}
