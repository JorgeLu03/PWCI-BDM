<?php
class AuthRepository
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    /**
     * Intenta autenticar a un usuario
     * @param string $username Usuario o correo
     * @param string $password Contraseña
     * @return array|null Datos del usuario si es exitoso, null si falla
     */
    public function attemptLogin(string $username, string $password): ?array
    {
        $stmt = $this->db->prepare("CALL SP_InicSes(?, ?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_InicSes: ' . $this->db->error);
        }
     
        $stmt->bind_param('ss', $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = ($result && $result->num_rows === 1) ? $result->fetch_assoc() : null;
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        
        return $user;
    }

    /**
     * Registra un nuevo usuario
     * @param string $correo
     * @param string $telefono
     * @param string $contrasena
     * @param string $fechaNac
     * @param bool $tipo_usuario
     * @param string $nombre
     * @param string|null $fotoTmpPath
     * @param string $pais
     * @param string $genero
     * @param string $nacionalidad
     * @return bool true si el registro fue exitoso
     */
    public function registerUser(
        string $correo,
        string $telefono,
        string $contrasena,
        string $fechaNac,
        bool $tipo_usuario,
        string $nombre,
        ?string $fotoTmpPath,
        string $pais,
        string $genero,
        string $nacionalidad
    ): bool {
        $stmt = $this->db->prepare("CALL SP_NewUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new RuntimeException('Error al preparar SP_NewUser: ' . $this->db->error);
        }

        $null = NULL;
        $tipo_usuario_int = $tipo_usuario ? 1 : 0;
        $stmt->bind_param('ssssisbsss', $correo, $telefono, $contrasena, $fechaNac, $tipo_usuario_int, $nombre, $null, $pais, $genero, $nacionalidad);
        
        // Enviar foto como BLOB si existe
        if ($fotoTmpPath && is_file($fotoTmpPath)) {
            $foto_data = file_get_contents($fotoTmpPath);
            if ($foto_data !== false) {
                $stmt->send_long_data(6, $foto_data);
            }
        }

        // Suprimir advertencias de MySQL
        mysqli_report(MYSQLI_REPORT_OFF);
        $ok = @$stmt->execute();
        $errno = $this->db->errno;
        $error_msg = $this->db->error;
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        
        $stmt->close();
        while ($this->db->more_results() && $this->db->next_result()) {;}
        
        if (!$ok) {
            if ($errno == 1062) {
                // Error de entrada duplicada (correo ya existe)
                if (strpos($error_msg, 'Correo') !== false) {
                    throw new RuntimeException('Este correo electrónico ya está registrado. Por favor, usa otro correo o inicia sesión.');
                } elseif (strpos($error_msg, 'Telefono') !== false) {
                    throw new RuntimeException('Este número de teléfono ya está registrado. Por favor, usa otro número.');
                } else {
                    throw new RuntimeException('Los datos proporcionados ya están registrados en el sistema.');
                }
            }
            throw new RuntimeException('Error al registrar el usuario. Por favor, intenta de nuevo.');
        }
        
        return true;
    }
}
