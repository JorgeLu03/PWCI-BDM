<?php
class UserRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function getUserDetails(?int $userId): array
    {
        $displayName = 'Mi Perfil';
        $photoSrc = '../css/PlaceHolder3.jpg';
        $userType = null;

        if ($userId) {
            $stmt = $this->db->prepare('CALL SP_GetUserDetails(?)');
            if ($stmt) {
                $stmt->bind_param('i', $userId);
                $stmt->execute();
                $res = $stmt->get_result();
                if ($res && $res->num_rows === 1) {
                    $row = $res->fetch_assoc();
                    if (!empty($row['Nombre'])) {
                        $displayName = $row['Nombre'];
                    }
                    if (!empty($row['Foto'])) {
                        if (is_string($row['Foto'])) {
                            $photoSrc = 'data:image/jpeg;base64,' . base64_encode($row['Foto']);
                        } else {
                            $photoSrc = $row['Foto'];
                        }
                    }
                    if (isset($row['Tipo_usuario'])) {
                        $userType = (int)$row['Tipo_usuario'];
                    }
                }
                $stmt->close();
                while ($this->db->more_results() && $this->db->next_result()) {;}
            }
        }

        return ['displayName' => $displayName, 'photoSrc' => $photoSrc, 'userType' => $userType];
    }

    public function getUserProfileData(int $userId): ?array
    {
        $stmt = $this->db->prepare('CALL SP_GetUserData(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetUserData: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $out = ($res && $res->num_rows === 1) ? $res->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return $out;
    }

    public function updateProfile(
        int $userId,
        string $correo,
        string $telefono,
        ?string $contrasena,
        string $fechaNac,
        string $nombre,
        ?string $fotoData,
        string $pais,
        string $genero,
        string $nacionalidad
    ): bool {
        $sql = 'CALL SP_ModUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
        $success = false;
        
        if ($stmt = $this->db->prepare($sql)) {
            $null_photo = NULL;
            $stmt->bind_param(
                'isssssbsss',
                $userId,
                $correo,
                $telefono,
                $contrasena,
                $fechaNac,
                $nombre,
                $null_photo,
                $pais,
                $genero,
                $nacionalidad
            );
            
            if ($fotoData !== null) {
                // foto
                $stmt->send_long_data(6, $fotoData);
            }
            
            $success = $stmt->execute();
            $stmt->close();
            
            while ($this->db->more_results() && $this->db->next_result()) {;}
        }
        
        return $success;
    }

    // Calcular edad
    public function getUserAge(int $userId): ?int
    {
        $stmt = $this->db->prepare('CALL SP_GetUserAge(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetUserAge: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            return $row['edad'] !== null ? (int)$row['edad'] : null;
        }
        
        $stmt->close();
        return null;
    }

    // Cuenta las publicaciones de un usuario
    public function countUserPublicationsByStatus(int $userId, int $estatus): int
    {
        $stmt = $this->db->prepare('CALL SP_CountUserPublicationsByStatus(?, ?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_CountUserPublicationsByStatus: ' . $this->db->error);
        }
        $stmt->bind_param('ii', $userId, $estatus);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            return (int)$row['total'];
        }
        
        $stmt->close();
        return 0;
    }

    public function getUserStatistics(int $userId): ?array
    {
        $stmt = $this->db->prepare('CALL SP_GetUserStatistics(?)');
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_GetUserStatistics: ' . $this->db->error);
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $stats = $result->fetch_assoc();
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
            return $stats;
        }
        
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        return null;
    }

    // Obtener el perfil
    public function getUserProfileWithAge(int $userId): ?array
    {
        $profile = $this->getUserProfileData($userId);
        
        if ($profile) {
            $profile['Edad'] = $this->getUserAge($userId);
        }
        
        return $profile;
    }
}
