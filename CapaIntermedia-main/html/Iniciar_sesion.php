<?php
session_start();

$error_message = '';

require_once '../BD/Connection/Connection.php'; // $conn
require_once '../BD/Querys/auth.php'; // attemptLogin

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $usuario = isset($_POST['USuariotxt']) ? trim($_POST['USuariotxt']) : '';
    $contrasena = isset($_POST['Contraseñatxt']) ? $_POST['Contraseñatxt'] : '';

    if (empty($usuario) || empty($contrasena)) {
        $error_message = 'Por favor completa todos los campos.';
    } else {
        $loginResult = attemptLogin($conn, $usuario, $contrasena);

        if ($loginResult === true) {
            session_regenerate_id(true);
            $conn->close();
            header('Location: inicio.php');
            exit();
        } else {
            $error_message = $loginResult;
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
    <title>Iniciar Sesion</title>
    <link rel="stylesheet" type="text/css" href="../css/inicio.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
</head>

<body>
    <main>
        <div class="contenedor_principal">
            <!-- Formulario -->
            <section class="login">
                <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <h2 class="publicacion_titulo">Iniciar Sesión</h2>
                    <?php if (!empty($error_message)): ?>
                        <p style="color: red; text-align: center;"><?php echo htmlspecialchars($error_message); ?></p>
                    <?php endif; ?>
                    <div class="form_container">
                        <!-- Campos del formulario -->
                        <div class="form_gruop">
                            <input type="text" id="USuariotxt" name="USuariotxt" class="form_input"
                                placeholder="Usuario o Correo" required>
                            <i class="fas fa-user"></i>
                            <span class="form_line"></span>
                        </div>

                        <div class="form_gruop">
                            <input type="password" id="Contraseñatxt" name="Contraseñatxt" class="form_input"
                                placeholder="Contraseña" required>
                            <i class="fas fa-lock"></i>
                            <span class="form_line"></span>
                        </div>

                        <input type="submit" class="form_submit" value="Iniciar Sesión">
                        <p style="text-align: center; margin: 20px 0 10px;">¿No tienes cuenta? <a href="registro.php" style="text-decoration: underline;">Regístrate</a></p>
                        <input type="button" class="form_submit" value="Regresar"
                            onclick="window.history.back()">
                    </div>
                </form>
            </section>
        </div>
    </main>
</body>

</html>