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
            // Sanitizar inputs
            $nombre = trim($_POST['nombre'] ?? '');
            $fechaNac = $_POST['fecha_nacimiento'] ?? '';
            $genero = trim($_POST['genero'] ?? '');
            $pais = trim($_POST['pais'] ?? '');
            $nacionalidad = trim($_POST['nacionalidad'] ?? '');
            $correo = trim(strtolower($_POST['correo'] ?? ''));
            $telefono = trim($_POST['telefono'] ?? '');
            $contrasena = $_POST['contrasena'] ?? '';
            $tipo_usuario = true;

            // Validar campos vacíos
            if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || 
                empty($nacionalidad) || empty($correo) || empty($telefono) || empty($contrasena) || 
                empty($_FILES['profilePhoto']['name'])) {
                $error_message = 'Por favor, completa todos los campos obligatorios.';
            } else {
                // Validar longitud nombre (3-100 caracteres)
                if (strlen($nombre) < 3 || strlen($nombre) > 100) {
                    $error_message = 'El nombre debe tener entre 3 y 100 caracteres.';
                }
                // Validar formato nombre (solo letras y espacios)
                elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/u', $nombre)) {
                    $error_message = 'El nombre solo puede contener letras y espacios.';
                }
                // Validar formato email
                elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                    $error_message = 'El correo electrónico no es válido.';
                }
                // Validar longitud email (máximo 100 caracteres por BD)
                elseif (strlen($correo) > 100) {
                    $error_message = 'El correo electrónico es demasiado largo (máximo 100 caracteres).';
                }
                // Validar formato teléfono (10-15 dígitos)
                elseif (!preg_match('/^[0-9]{10,15}$/', $telefono)) {
                    $error_message = 'El teléfono debe contener entre 10 y 15 dígitos numéricos.';
                }
                // Validar longitud contraseña
                elseif (strlen($contrasena) < 8 || strlen($contrasena) > 255) {
                    $error_message = 'La contraseña debe tener entre 8 y 255 caracteres.';
                }
            }
            
            if (empty($error_message)) {
                // Validar edad mínima de 12 años (REQUISITO OBLIGATORIO)
                $birthDate = new DateTime($fechaNac);
                $today = new DateTime();
                $age = $today->diff($birthDate)->y;
                
                if ($age < 12) {
                    $error_message = 'Debes tener al menos 12 años para registrarte en GolNet.';
                }
                
                // Validar patrón de contraseña
                if (empty($error_message)) {
                    $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/';
                    if (!preg_match($password_pattern, $contrasena)) {
                        $error_message = 'La contraseña debe contener al menos una letra minúscula, una letra mayúscula, un número y un carácter especial.';
                    }
                }

                if (empty($error_message)) {
                    // Validar archivo de foto
                    if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] == 0) {
                        $foto_tmp_path = $_FILES['profilePhoto']['tmp_name'];
                        $foto_type = $_FILES['profilePhoto']['type'];
                        $foto_size = $_FILES['profilePhoto']['size'];
                        
                        // Validar tipo MIME (solo imágenes)
                        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                        if (!in_array($foto_type, $allowed_types)) {
                            $error_message = 'Solo se permiten archivos de imagen (JPEG, PNG, GIF, WebP).';
                        } else {
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
