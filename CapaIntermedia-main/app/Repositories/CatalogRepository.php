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
        $sql = 'CALL Seleccionar_Dato_Condicional(2);';
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
        $sql = 'CALL Seleccionar_Dato_Condicional(1);';
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

    public function getWorldCupByID(int $mundialId): ?array
    {
        $sql = 'SELECT * FROM V_Mundiales WHERE ID_Mundial = ?';
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
}
