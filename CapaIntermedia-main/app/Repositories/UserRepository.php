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
        $sql = 'SELECT ID_User, Nombre, Fec_nac, Genero, Pais_de_nac, Nacionalidad, Correo, Telefono, Foto FROM V_DetallesUsuario WHERE ID_User = ?';
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

    // ========== MÉTODOS QUE USAN LAS NUEVAS FUNCIONES Y VISTAS SQL ==========

    /**
     * Calcula la edad de un usuario usando la función FN_CalcularEdadUsuario
     * @param int $userId ID del usuario
     * @return int|null Edad del usuario en años, o null si no se encuentra
     */
    public function getUserAge(int $userId): ?int
    {
        $sql = "SELECT FN_CalcularEdadUsuario(?) AS edad";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            throw new RuntimeException('Error al preparar consulta de edad: ' . $this->db->error);
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return $row['edad'] !== null ? (int)$row['edad'] : null;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Cuenta las publicaciones de un usuario por estado usando FN_ContarPublicacionesPorEstado
     * @param int $userId ID del usuario
     * @param int $estatus Estado de las publicaciones (1=Pendiente, 2=Aprobada, 3=Rechazada)
     * @return int Cantidad de publicaciones con ese estado
     */
    public function countUserPublicationsByStatus(int $userId, int $estatus): int
    {
        $sql = "SELECT FN_ContarPublicacionesPorEstado(?, ?) AS total";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            throw new RuntimeException('Error al preparar consulta de conteo: ' . $this->db->error);
        }
        
        $stmt->bind_param('ii', $userId, $estatus);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $stmt->close();
            return (int)$row['total'];
        }
        
        $stmt->close();
        return 0;
    }

    /**
     * Obtiene estadísticas completas de un usuario usando V_EstadisticasPublicaciones
     * @param int $userId ID del usuario
     * @return array|null Array con estadísticas (Total_Publicaciones, Publicaciones_Aprobadas, etc.)
     */
    public function getUserStatistics(int $userId): ?array
    {
        $sql = "SELECT ID_User, Nombre_Usuario, Total_Publicaciones, Publicaciones_Aprobadas, Publicaciones_Pendientes, Publicaciones_Rechazadas, Total_Vistas, Promedio_Vistas FROM V_EstadisticasPublicaciones WHERE ID_User = ?";
        $stmt = $this->db->prepare($sql);
        
        if (!$stmt) {
            throw new RuntimeException('Error al preparar consulta de estadísticas: ' . $this->db->error);
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $result->num_rows > 0) {
            $stats = $result->fetch_assoc();
            $stmt->close();
            return $stats;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Obtiene el perfil completo de un usuario con edad calculada
     * @param int $userId ID del usuario
     * @return array|null Datos del perfil incluyendo edad
     */
    public function getUserProfileWithAge(int $userId): ?array
    {
        $profile = $this->getUserProfileData($userId);
        
        if ($profile) {
            $profile['Edad'] = $this->getUserAge($userId);
        }
        
        return $profile;
    }
}
