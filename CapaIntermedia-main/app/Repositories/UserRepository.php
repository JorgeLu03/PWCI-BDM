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
        $sql = 'SELECT * FROM V_DetallesUser WHERE ID_User = ?';
        $out = null;
        if ($stmt = $this->db->prepare($sql)) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $out = $res->fetch_assoc();
            }
            $stmt->close();
        }
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
            
            // If there's photo data, send it as long data (BLOB handling)
            if ($fotoData !== null) {
                // The 7th parameter (index 6) is the photo
                $stmt->send_long_data(6, $fotoData);
            }
            
            $success = $stmt->execute();
            $stmt->close();
            
            // Clean up stored procedure results
            while ($this->db->more_results() && $this->db->next_result()) {;}
        }
        
        return $success;
    }
}
