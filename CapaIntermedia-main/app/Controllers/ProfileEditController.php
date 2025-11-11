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
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_changes'])) {
            $nombre = $_POST['nombre'] ?? '';
            $fechaNac = $_POST['fecha_nacimiento'] ?? '';
            $genero = $_POST['genero'] ?? '';
            $pais = $_POST['pais'] ?? '';
            $nacionalidad = $_POST['nacionalidad'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';

            // Validate required fields
            if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || 
                empty($nacionalidad) || empty($correo) || empty($telefono)) {
                $feedback_message = "Por favor, completa todos los campos obligatorios";
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
        
        // Get user profile data for form
        $userData = $this->userRepo->getUserProfileData($userId);
        if ($userData === null) {
            $userData = [];
        }

        // Load view
        extract($userDetails);
        require __DIR__ . '/../Views/editar_perfil.php';
    }
}
