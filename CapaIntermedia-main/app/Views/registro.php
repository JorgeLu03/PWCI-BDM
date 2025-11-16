<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regístrate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../css/inicio.css">
    <link rel="stylesheet" type="text/css" href="../css/login.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <main>
        <div class="contenedor_principal">
            <section class="login">
                <form class="form" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
                    <h2 class="publicacion_titulo">Crea una cuenta</h2>
                    <?php if (!empty($error_message)): ?>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error en el Registro',
                                    text: '<?php echo addslashes($error_message); ?>',
                                    confirmButtonColor: '#d33',
                                    confirmButtonText: 'Entendido'
                                });
                            });
                        </script>
                    <?php endif; ?>
                    <?php if (!empty($success_message)): ?>
                        <p style="color: #ccffcc; text-align: center; margin-bottom: 15px;"><?php echo htmlspecialchars($success_message); ?></p>
                    <?php endif; ?>
                    <div class="form_container">
                        <div class="form_gruop">
                            <input type="text" id="nombre" name="nombre" class="form_input"
                                placeholder="Nombre Completo" required>
                            <i class="fas fa-user"></i>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <label>Fecha de Nacimiento:</label>
                            <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form_input"
                                placeholder="Fecha de Nacimiento" required>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <label>Foto de perfil:</label>
                            <div class="file-upload-container">
                                <button type="button" class="form_submit" id="selectImageBtn">Seleccionar imagen</button>
                                <input type="file" id="profilePhoto" name="profilePhoto" accept="image/*" style="display: none;" required>
                                <div id="imagePreviewContainer" class="image-preview-container"></div>
                            </div>
                        </div>
                        <div class="form_gruop">
                            <label>Género:</label>
                            <select name="genero" class="form_input" required>
                                <option value="">Selecciona tu género</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <label>País de Nacimiento:</label>
                            <select id="pais" name="pais" class="form_input" required>
                                <option value="">Cargando países...</option>
                            </select>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <label>Nacionalidad:</label>
                            <select id="nacionalidad" name="nacionalidad" class="form_input" required>
                                <option value="">Cargando nacionalidades...</option>
                            </select>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <input type="email" id="correo" name="correo" class="form_input"
                                placeholder="Correo electronico" required>
                            <i class="fas fa-envelope"></i>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <input type="tel" id="telefono" name="telefono" class="form_input"
                                placeholder="Teléfono" required>
                            <i class="fas fa-phone"></i>
                            <span class="form_line"></span>
                        </div>
                        <div class="form_gruop">
                            <input type="password" id="contrasena" name="contrasena" class="form_input"
                                placeholder="Contraseña" required>
                            <i class="fas fa-lock"></i>
                            <span class="form_line"></span>
                        </div>

                        <input type="submit" class="form_submit" value="Registrarme">
                        <p style="text-align: center; margin: 20px 0 10px;">¿Ya tienes cuenta? <a href="iniciar_sesion.php" style="text-decoration: underline;">Inicia Sesión</a></p>
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
