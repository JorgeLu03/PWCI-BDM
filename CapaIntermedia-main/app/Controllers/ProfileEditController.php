<?php

class ProfileEditController {
    private $userRepo;

    public function __construct($userRepo) {
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header("Location: Iniciar_sesion.php");
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $feedback_message = '';
        $feedback_type = '';

        // Handle POST request to save changes
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Sanitizar inputs
            $nombre = trim($_POST['nombre'] ?? '');
            $fechaNac = $_POST['fecha_nacimiento'] ?? '';
            $genero = trim($_POST['genero'] ?? '');
            $pais = trim($_POST['pais'] ?? '');
            $nacionalidad = trim($_POST['nacionalidad'] ?? '');
            $correo = trim(strtolower($_POST['correo'] ?? ''));
            $telefono = trim($_POST['telefono'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';

            // Validate required fields
            if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || 
                empty($nacionalidad) || empty($correo) || empty($telefono)) {
                $feedback_message = "Por favor, completa todos los campos obligatorios";
                $feedback_type = 'error';
            }
            // Validar formato email
            elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $feedback_message = "El correo electrónico no es válido";
                $feedback_type = 'error';
            }
            // Validar formato teléfono
            elseif (!preg_match('/^[0-9]{10,15}$/', $telefono)) {
                $feedback_message = "El teléfono debe contener entre 10 y 15 dígitos";
                $feedback_type = 'error';
            }
            // Validar contraseña si se proporciona
            elseif (!empty($contrasena) && (strlen($contrasena) < 8 || !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/', $contrasena))) {
                $feedback_message = "La contraseña debe tener al menos 8 caracteres e incluir mayúscula, minúscula, número y carácter especial";
                $feedback_type = 'error';
            } else {
                // Handle photo upload
                $foto_data = null;
                if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] == 0) {
                    $foto_data = file_get_contents($_FILES['profilePhoto']['tmp_name']);
                }

                // If password is empty, treat as NULL (don't update)
                $contrasena_to_db = !empty($contrasena) ? $contrasena : null;

                // Update profile
                $success = $this->userRepo->updateProfile(
                    $userId,
                    $correo,
                    $telefono,
                    $contrasena_to_db,
                    $fechaNac,
                    $nombre,
                    $foto_data,
                    $pais,
                    $genero,
                    $nacionalidad
                );

                if ($success) {
                    $feedback_message = "¡Perfil actualizado con éxito!";
                    $feedback_type = 'success';
                } else {
                    $feedback_message = "Error al actualizar el perfil";
                    $feedback_type = 'error';
                }
            }
        }

        // Get user details for header
        $userDetails = $this->userRepo->getUserDetails($userId);
        
        // Get user profile data for form (con edad calculada usando la función SQL)
        $userData = $this->userRepo->getUserProfileWithAge($userId);
        if ($userData === null) {
            $userData = [];
        }

        // Obtener estadísticas del usuario usando V_EstadisticasPublicaciones
        $userStats = $this->userRepo->getUserStatistics($userId);
        if ($userStats === null) {
            $userStats = [];
        }

        // Load view
        extract($userDetails);
        require __DIR__ . '/../Views/editar_perfil.php';
    }
}
