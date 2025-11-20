<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesion</title>
    <link rel="stylesheet" type="text/css" href="../css/inicio.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main>
        <div class="contenedor_principal">
            <section class="login">
                <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST">
                    <h2 class="publicacion_titulo">Iniciar Sesión</h2>
                    <?php if (!empty($error_message)): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error de Inicio de Sesión',
                                    text: '<?php echo addslashes($error_message); ?>',
                                    confirmButtonColor: '#d33',
                                    confirmButtonText: 'Entendido'
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <div class="form_container">
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
