<?php
session_start();

require_once '../BD/Connection/Connection.php';

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
    $tipo_usuario = true; // Se envía como booleano (1) para el tipo de usuario
    $foto_data = null;

    if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || empty($nacionalidad) || empty($correo) || empty($telefono) || empty($contrasena) || empty($_FILES['profilePhoto']['name'])) {
        $error_message = 'Por favor, completa todos los campos obligatorios.';
    } else {
        if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] == 0) {
            // Leer el contenido binario del archivo temporal
            $foto_data = file_get_contents($_FILES['profilePhoto']['tmp_name']);
            if ($foto_data === false) {
                $error_message = 'Hubo un error al subir tu foto de perfil.';
                $foto_data = null;
            }
        }

        if (empty($error_message)) {
            $password_pattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).+$/';
            if (!preg_match($password_pattern, $contrasena)) {
                $error_message = 'La contraseña debe contener al menos una letra minúscula, una letra mayúscula, un número y un carácter especial.';
            }
        }

        if (empty($error_message) && $foto_data !== null) {
            $stmt = $conn->prepare("CALL SP_NewUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $null = NULL; // Variable para bind_param
                // El séptimo '?' corresponde a la foto (índice 6)
                $stmt->bind_param('ssssisbsss', $correo, $telefono, $contrasena, $fechaNac, $tipo_usuario, $nombre, $null, $pais, $genero, $nacionalidad);
                $stmt->send_long_data(6, $foto_data);

                if ($stmt->execute()) {
                    $success_message = "¡Registro exitoso! Ahora puedes iniciar sesión.";
                    header("refresh:3;url=Iniciar_sesion.php");
                } else {
                    if ($conn->errno == 1062) {
                        $error_message = "El correo electrónico ya está registrado. Por favor, utiliza otro.";
                    } else {
                        $error_message = "Error al registrar el usuario: " . $stmt->error;
                    }
                }
                $stmt->close();
            } else {
                $error_message = "Error al preparar la consulta: " . $conn->error;
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regístrate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/inicio.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
</head>

<body>
    <main>
        <div class="contenedor_principal">
            <!-- Formulario -->
            <section class="login">
                <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <h2 class="publicacion_titulo">Crea una cuenta</h2>
                    <?php if (!empty($error_message)): ?>
                        <p style="color: #ffcccc; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <p style="color: #ccffcc; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($success_message); ?></p>
                    <?php endif; ?>
                    <div class="form_container">
                        <!-- Campos del formulario --> 
                        <!-- Nombre Completo -->
                        <div class="form_gruop">
                            <input type="text" id="nombre" name="nombre" class="form_input"
                                placeholder="Nombre Completo" required>
                            <i class="fas fa-user"></i>
                            <span class="form_line"></span>
                        </div>
                        <!-- Fecha de Nacimiento -->
                        <div class="form_gruop">
                            <label>Fecha de Nacimiento:</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form_input"
                                placeholder="Fecha de Nacimiento" required>
                            <span class="form_line"></span>
                        </div>
                        <!-- Fotografía de Perfil -->
                        <div class="form_gruop">
                            <label>Foto de perfil:</label>
                            <div class="file-upload-container">
                                <button type="button" class="form_submit" id="selectImageBtn">Seleccionar imagen</button>
                                <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" style="display: none;" required>
                                <div id="imagePreviewContainer" class="image-preview-container"></div>
                            </div>
                        </div>
                        <!-- Genero -->
                        <div class="form_gruop">
                            <label>Genero:</label>
                            <input list="genero" name="genero" class="form_input" placeholder="Escribe o selecciona" required>
                            <datalist id="genero">
                                <option value="Masculino"></option>
                                <option value="Femenino"></option>
                                <option value="Otro"></option>
                            </datalist>
                            <span class="form_line"></span>
                        </div>
                        <!-- Pais de Nacimiento -->
                        <div class="form_gruop">
                            <label>País de Nacimiento:</label>
                            <input list="paises" name="pais" class="form_input" placeholder="Escribe o selecciona" required>
                            <datalist id="paises">
                                <option value="Argentina"></option>
                                <option value="Brasil"></option>
                                <option value="Canadá"></option>
                                <option value="Chile"></option>
                                <option value="Colombia"></option>
                                <option value="Estados Unidos"></option>
                                <option value="México"></option>
                                <option value="España"></option>
                            </datalist>
                            <span class="form_line"></span>
                        </div>
                        <!-- Nacionalidad -->
                        <div class="form_gruop">
                            <label>Nacionalidad:</label>
                            <input list="nacionalidad" name="nacionalidad" class="form_input" placeholder="Escribe o selecciona" required>
                            <datalist id="nacionalidad">
                                <option value="Argentina"></option>
                                <option value="Brasil"></option>
                                <option value="Canadá"></option>
                                <option value="Chile"></option>
                                <option value="Colombia"></option>
                                <option value="Estados Unidos"></option>
                                <option value="México"></option>
                                <option value="España"></option>
                            </datalist>
                            <span class="form_line"></span>
                        </div>
                        <!-- Correo Electronico -->
                        <div class="form_gruop">
                            <input type="email" id="correo" name="correo" class="form_input"
                                placeholder="Correo electronico" required>
                            <i class="fas fa-envelope"></i>
                            <span class="form_line"></span>
                        </div>
                        <!-- Telefono -->
                        <div class="form_gruop">
                            <input type="tel" id="telefono" name="telefono" class="form_input"
                                placeholder="Teléfono" required>
                            <i class="fas fa-phone"></i>
                            <span class="form_line"></span>
                        </div>
                        <!-- Contraseña -->
                        <div class="form_gruop">
                            <input type="password" id="contrasena" name="contrasena" class="form_input"
                                placeholder="Contraseña" required>
                            <i class="fas fa-lock"></i>
                            <span class="form_line"></span>
                        </div>

                        <input type="submit" class="form_submit" value="Registrarme">
                        <p style="text-align: center; margin: 20px 0 10px;">¿Ya tienes cuenta? <a href="Iniciar_sesion.php" style="text-decoration: underline;">Inicia Sesión</a></p>
                        <input type="button" class="form_submit" value="Regresar"
                            onclick="window.history.back()">
                    </div>
                </form>
            </section>
        </div>
    </main>
    <script src="../javascript/registro.js"></script>
</body>

</html>