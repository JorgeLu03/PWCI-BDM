<?php
class CatalogRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getCategorias(): array
    {
        $sql = 'CALL SP_SeleccionarDatoCondicional(2);';
        $out = [];
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->execute();
            $res = $stmt->get_result();
            $out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function getMundiales(): array
    {
        $sql = 'CALL SP_SeleccionarDatoCondicional(1);';
        $out = [];
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->execute();
            $res = $stmt->get_result();
            $out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function getCategoryByID(int $categoryId): ?array
    {
        $sql = 'CALL SP_GetCategoryByID(?);';
        $out = null;
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $out = $res->fetch_assoc();
            }
            $stmt->close();
        }
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function getPublicationsByCategory(int $categoryId, string $sortBy = 'recent'): array
    {
        $spName = 'SP_GetPostsByCategory';
        if ($sortBy === 'likes') {
            $spName = 'SP_GetPostsByCategory_ByLikes';
        } elseif ($sortBy === 'comments') {
            $spName = 'SP_GetPostsByCategory_ByComments';
        }
        
        $sql = "CALL $spName(?);";
        $out = [];
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('i', $categoryId);
            $stmt->execute();
            $res = $stmt->get_result();
            $out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function getPublicationsByWorldCup(int $mundialId, string $sortBy = 'recent'): array
    {
        $spName = 'SP_GetPostsByMundial';
        if ($sortBy === 'likes') {
            $spName = 'SP_GetPostsByMundial_ByLikes';
        } elseif ($sortBy === 'comments') {
            $spName = 'SP_GetPostsByMundial_ByComments';
        }
        
        $sql = "CALL $spName(?);";
        $out = [];
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('i', $mundialId);
            $stmt->execute();
            $res = $stmt->get_result();
            $out = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
            $stmt->close();
        }
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function getWorldCupsWithStats(): array
    {
        $sql = 'SELECT ID_Mundial, Nombre, Anio, Sede, Campeon, Subcampeon, TercerLugar, CuartoLugar, Descripcion, Logo, Banner, Balon, Fec_Final, Lugar_Final, Marcador_Final, TiempoExtra_Final, Goleador, Alineacion_Campeon, Cantante, Views, ID_User, Total_Publicaciones FROM V_MundialesConEstadisticas ORDER BY Anio DESC';
        $out = [];
        
        $result = $this->db->query($sql);
        if ($result) {
            $out = $result->fetch_all(MYSQLI_ASSOC);
            $result->free();
        }
        
        return $out;
    }

    // Obtiene mundial específico
    public function getWorldCupWithStats(int $mundialId): ?array
    {
        $sql = 'SELECT ID_Mundial, Nombre, Anio, Sede, Campeon, Subcampeon, TercerLugar, CuartoLugar, Descripcion, Logo, Banner, Balon, Fec_Final, Lugar_Final, Marcador_Final, TiempoExtra_Final, Goleador, Alineacion_Campeon, Cantante, Views, ID_User, Total_Publicaciones FROM V_MundialesConEstadisticas WHERE ID_Mundial = ?';
        $out = null;
        
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('i', $mundialId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows > 0) {
                $out = $res->fetch_assoc();
            }
            $stmt->close();
        }
        
        return $out;
    }

    // Elimina categoría
    public function deleteCategory(int $categoryId): bool
    {
        $stmt = $this->db->prepare('CALL SP_DeleteCategory(?)');
        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->db->error);
        }
        
        $stmt->bind_param('i', $categoryId);
        
        try {
            $result = $stmt->execute();
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            return $result;
        } catch (Exception $e) {
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            throw $e;
        }
    }

    // Elimina mundial
    public function deleteMundial(int $mundialId): bool
    {
        $stmt = $this->db->prepare('CALL SP_DeleteMundial(?)');
        if (!$stmt) {
            throw new Exception('Error al preparar consulta: ' . $this->db->error);
        }
        
        $stmt->bind_param('i', $mundialId);
        
        try {
            $result = $stmt->execute();
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            return $result;
        } catch (Exception $e) {
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            throw $e;
        }
    }
}
