<?php
class RegisterController
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
        $success_message = '';

        if ($_SERVER["REQUEST_METHOD"] === 'POST') {
            $nombre = $_POST['nombre'] ?? '';
            $fechaNac = $_POST['fecha_nacimiento'] ?? '';
            $genero = $_POST['genero'] ?? '';
            $pais = $_POST['pais'] ?? '';
            $nacionalidad = $_POST['nacionalidad'] ?? '';
            $correo = $_POST['correo'] ?? '';
            $telefono = $_POST['telefono'] ?? '';
            $contrasena = $_POST['contrasena'] ?? '';
            $tipo_usuario = true;

            // Validar campos vacíos
            if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || 
                empty($nacionalidad) || empty($correo) || empty($telefono) || empty($contrasena) || 
                empty($_FILES['profilePhoto']['name'])) {
                $error_message = 'Por favor, completa todos los campos obligatorios.';
            } else {
                // Validar patrón de contraseña
                $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/';
                if (!preg_match($password_pattern, $contrasena)) {
                    $error_message = 'La contraseña debe contener al menos una letra minúscula, una letra mayúscula, un número y un carácter especial.';
                }

                if (empty($error_message)) {
                    // Validar archivo de foto
                    if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] == 0) {
                        $foto_tmp_path = $_FILES['profilePhoto']['tmp_name'];
                        
                        try {
                            $this->authRepo->registerUser(
                                $correo,
                                $telefono,
                                $contrasena,
                                $fechaNac,
                                $tipo_usuario,
                                $nombre,
                                $foto_tmp_path,
                                $pais,
                                $genero,
                                $nacionalidad
                            );
                            
                            $success_message = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                            header("refresh:3;url=Iniciar_sesion.php");
                        } catch (RuntimeException $e) {
                            $error_message = $e->getMessage();
                        } catch (Exception $e) {
                            error_log("Error en registro: " . $e->getMessage());
                            $error_message = "Error al registrar el usuario. Por favor, intenta de nuevo.";
                        }
                    } else {
                        $error_message = 'Hubo un error al subir tu foto de perfil.';
                    }
                }
            }
        }

        // Renderizar vista
        extract([
            'error_message' => $error_message,
            'success_message' => $success_message
        ]);
        require __DIR__ . '/../Views/registro.php';
    }
}
