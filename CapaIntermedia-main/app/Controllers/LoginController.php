<?php
class LoginController
{
    private mysqli $db;
    private AuthRepository $authRepo;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->authRepo = new AuthRepository($db);
    }

    public function handle(): void
    {
        $error_message = '';

        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $usuario = isset($_POST['USuariotxt']) ? trim($_POST['USuariotxt']) : '';
            $contrasena = isset($_POST['Contraseñatxt']) ? $_POST['Contraseñatxt'] : '';

            if (empty($usuario) || empty($contrasena)) {
                $error_message = 'Por favor completa todos los campos.';
            }
            elseif (strlen($usuario) > 100 || strlen($contrasena) > 255) {
                $error_message = 'Credenciales inválidas.';
            } else {
                try {
                    $user = $this->authRepo->attemptLogin($usuario, $contrasena);
                    
                    if ($user !== null) {
                        $_SESSION['user_id'] = $user['ID_User'];
                        $_SESSION['username'] = $user['Nombre'];
                        session_regenerate_id(true);
                        header('Location: inicio.php');
                        exit();
                    } else {
                        $error_message = 'Usuario o contraseña incorrectos.';
                    }
                } catch (Exception $e) {
                    error_log("Error en login: " . $e->getMessage());
                    $error_message = 'Error interno del servidor. Por favor, intenta de nuevo más tarde.';
                }
            }
        }

        // vista
        extract(['error_message' => $error_message]);
        require __DIR__ . '/../Views/iniciar_sesion.php';
    }
}
