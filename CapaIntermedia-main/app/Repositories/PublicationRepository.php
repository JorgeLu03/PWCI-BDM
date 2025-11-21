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
            $fp = fopen($mediaTmpPath, 'rb');
            if ($fp) {
                while (!feof($fp)) {
                    $chunk = fread($fp, 65536);
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
        $stmt = $this->db->prepare('CALL SP_InsertarPublicacion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_InsertarPublicacion: ' . $this->db->error);
        }
        $nullBlob = null;
        $stmt->bind_param('ssisssbsiii', $titulo, $descripcion, $estatus, $views, $fecAprob, $fecPub, $nullBlob, $mediaType, $idCateg, $idUser, $idMundial);

        if ($mediaTmpPath && is_file($mediaTmpPath)) {
            $fp = fopen($mediaTmpPath, 'rb');
            if ($fp) {
                while (!feof($fp)) {
                    $chunk = fread($fp, 65536);
                    $stmt->send_long_data(6, $chunk);
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

    // Obtiene publicaciones un usuario
    public function getUserPublications(int $userId): array
    {
        $stmt = $this->db->prepare('CALL SP_GetUserPublications(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetUserPublications: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publications;
    }

    public function getPublicationDetail(int $publiId): ?array
    {
        // Incrementar vistas
        $stmt_views = $this->db->prepare("CALL SP_ActualizarVistas(?)");
        if ($stmt_views) {
            $stmt_views->bind_param('i', $publiId);
            $stmt_views->execute();
            $stmt_views->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
        }
        $stmt = $this->db->prepare("CALL SP_MostrarPublicacionEspecifica(?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_MostrarPublicacionEspecifica: ' . $this->db->error);
        }
        $stmt->bind_param('i', $publiId);
        $stmt->execute();
        $result = $stmt->get_result();
        $publication = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        
        if ($publication !== null) {
            $publication['ID_Publi'] = $publiId;
        }
        
        return $publication;
    }

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

    public function getLikers(int $publiId): array
    {
        $stmt = $this->db->prepare("CALL SP_GetLikersByPost(?)");
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

    public function getCommenters(int $publiId): array
    {
        $stmt = $this->db->prepare("CALL SP_GetCommentersByPost(?)");
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

    public function getPublicationsWithDetails(int $estatus = 2, string $sortBy = 'recent', ?int $limit = null): array
    {
        $stmt = $this->db->prepare('CALL SP_GetPublicationsWithDetails(?, ?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetPublicationsWithDetails: ' . $this->db->error);
        }
        $stmt->bind_param('isi', $estatus, $sortBy, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publications;
    }

    public function getCommentsWithUserInfo(int $publiId, int $estatusComentario = 2): array
    {
        $stmt = $this->db->prepare('CALL SP_GetCommentsWithUserInfo(?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetCommentsWithUserInfo: ' . $this->db->error);
        }
        $stmt->bind_param('ii', $publiId, $estatusComentario);
        $stmt->execute();
        $result = $stmt->get_result();
        $comments = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $comments;
    }

    public function getUserPublicationStats(int $userId): ?array
    {
        $stmt = $this->db->prepare('CALL SP_GetUserPublicationStats(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetUserPublicationStats: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = ($result && $result->num_rows > 0) ? $result->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $stats;
    }

    public function getTopPublications(int $limit = 10): array
    {
        $stmt = $this->db->prepare('CALL SP_GetTopPublications(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetTopPublications: ' . $this->db->error);
        }
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $publications;
    }

    public function deletePublicationComplete(int $publicationId): bool
    {
        $stmt = $this->db->prepare('CALL SP_DeletePublicationComplete(?)');
        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->db->error);
        }
        
        $stmt->bind_param('i', $publicationId);
        
        try {
            if (!$stmt->execute()) {
                throw new Exception('Error al ejecutar SP: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $success = false;
            
            if ($result) {
                $row = $result->fetch_assoc();
                if ($row && isset($row['success'])) {
                    $success = $row['success'] == 1;
                }
            }
            
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            
            if (!$success) {
                throw new Exception('El procedimiento no retornó éxito');
            }
            
            return $success;
        } catch (Exception $e) {
            if (isset($stmt)) {
                $stmt->close();
            }
            while ($this->db->more_results() && $this->db->next_result()) {;}
            throw $e;
        }
    }
}
