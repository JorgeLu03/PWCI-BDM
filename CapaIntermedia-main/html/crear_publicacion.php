<?php
session_start();

// --- Restringir acceso a usuarios no logueados ---
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Si el usuario no ha iniciado sesión, redirigirlo a la página de login.
    header('Location: Iniciar_sesion.php');
    exit(); // Detener la ejecución del script después de redirigir.
}
require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    // 1. Capturar y definir datos (asegúrate de que todos están aquí)
    $Titulo_P = $_POST['Titulo'] ?? '';
    $Descripcion_P = $_POST['Descripcion'] ?? '';
    $ID_Categ_P = (int)($_POST['ID_categ'] ?? 0); // Aseguramos que sea INT
    $ID_Mundial_P = (int)($_POST['ID_Mundial'] ?? 0); // Aseguramos que sea INT
    
    // Valores por defecto
    $Estatus_P = 1; 
    $Views_P = 0;
    $Fec_aprob_P = NULL;
    $Fec_pub = date('Y-m-d');
    $ID_Use_P = (int) ($_SESSION['user_id'] ?? 0);

    // 2. Validación de campos obligatorios
    if (empty($Titulo_P) || empty($Descripcion_P) || empty($ID_Categ_P) || empty($ID_Mundial_P)) {
        $error_message = 'Por favor, completa el título, descripción, categoría y mundial.';
    } elseif (empty($ID_Use_P)) {
         $error_message = 'Error de sesión: No se pudo identificar al usuario. Por favor, inicia sesión de nuevo.';
    } else {
        // --- 3. Manejo de Multimedia (Leer como BLOB) ---
        $multimedia_data = null;
        $multimedia_type = null;
        
        if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
            // No leemos el archivo aquí todavía. Solo verificamos que existe.
        }
        
        // Si no hay errores de validación o subida, intentar la inserción en BD
        if (empty($error_message)) {
            // --- 4. Llamada al Procedimiento Almacenado ---
            $sql_insert = "CALL Insertar_Publicacion(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            if ($stmt = $conn->prepare($sql_insert)) {
                // Para enlazar un BLOB, primero se debe enlazar una variable NULL.
                $null_blob = NULL; 
                // Tipos: s, s, i, i, s, s, b (Multimedia), s (Tipo), i, i, i.
                // Enlazamos la variable $null_blob.
                $stmt->bind_param('ssisssbsiii',
                    $Titulo_P, 
                    $Descripcion_P, 
                    $Estatus_P, 
                    $Views_P, 
                    $Fec_aprob_P, 
                    $Fec_pub, 
                    $null_blob, 
                    $multimedia_type,
                    $ID_Categ_P, 
                    $ID_Use_P, 
                    $ID_Mundial_P
                );

                // --- Manejo de BLOB por trozos (chunks) ---
                if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
                    $file_path = $_FILES['Multimedia']['tmp_name'];
                    $multimedia_type = $_FILES['Multimedia']['type']; // Obtenemos el MIME type
                    $stmt->bind_param('ssisssbsiii', $Titulo_P, $Descripcion_P, $Estatus_P, $Views_P, $Fec_aprob_P, $Fec_pub, $null_blob, $multimedia_type, $ID_Categ_P, $ID_Use_P, $ID_Mundial_P);

                    $fp = fopen($file_path, 'r');
                    if ($fp) {
                        // Leemos el archivo en trozos de 8KB y lo enviamos
                        while (!feof($fp)) {
                            $chunk = fread($fp, 8192);
                            // El 7º '?' (índice 6) sigue siendo el BLOB
                            $stmt->send_long_data(6, $chunk);
                        }
                        fclose($fp);
                    }
                }

                if ($stmt->execute()) {
                    // Redirigimos con un parámetro para mostrar la alerta de éxito.
                    header('Location: crear_publicacion.php?publicado=exitoso');
                    exit;
                } else {
                    // Captura el error de ejecución de la base de datos (Ej: clave foránea)
                    $error_message = 'Error de Base de Datos al publicar: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                // Captura el error si falla la preparación de la consulta
                $error_message = 'Error interno: Fallo al preparar la consulta SQL. ' . $conn->error;
            }
        }
    }
}
$sql_mundial = "CALL Seleccionar_Dato_Condicional(1);";
$sql_categoria = "CALL Seleccionar_Dato_Condicional(2);";

$categorias = [];
if ($stmt = $conn->prepare($sql_categoria)) {
    // Aquí puedes enlazar parámetros si el procedimiento los necesitara, pero es estático (2)
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        // Guardar todas las categorías en un array
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        $result->free();
    } else {
        // Manejar error de ejecución
        error_log("Error al ejecutar categorías: " . $stmt->error);
    }
    $stmt->close();
} 
else {
    // Manejar error de preparación
    error_log("Error al preparar categorías: " . $conn->error);
}
// Limpiar resultados para la siguiente consulta
while ($conn->more_results() && $conn->next_result()) {
    // Descartar resultados adicionales
}


$mundial = [];

if ($stmt = $conn->prepare($sql_mundial)) {
    // Aquí puedes enlazar parámetros si el procedimiento los necesitara, pero es estático (2)
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        // Guardar todas las categorías en un array
        while ($row = $result->fetch_assoc()) {
            $mundial[] = $row;
        }
        $result->free();
    } else {
        // Manejar error de ejecución
        error_log("Error al ejecutar mundial: " . $stmt->error);
    }
    $stmt->close();
}
else {
    // Manejar error de preparación
    error_log("Error al preparar mundial: " . $conn->error);
}
// Limpiar resultados para la siguiente consulta (el formulario)
while ($conn->more_results() && $conn->next_result()) {
    // Descartar resultados adicionales
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Publicación - Mundial 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inicio.css"> <!-- Incluir inicio.css para estilos de header -->
    <link rel="stylesheet" href="../css/new_publi.css">
    <style data-injected="header-perfil">
        .header-profile-mini{display:inline-flex;align-items:center;gap:10px;padding:6px 10px;background:rgba(255,255,255,0.08);border-radius:999px;border:1px solid rgba(255,255,255,.15)}
        .header-profile-mini img{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(0,0,0,.2)}
        .header-profile-mini .name{font-weight:600}
    </style>
    <style data-injected="header-search">
        .header-content, .countdown { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
        .header-search { display:flex; gap:8px; align-items:center; }
        .header-search input[type="search"]{ padding:6px 10px; border-radius:999px; border:1px solid rgba(0,0,0,.15); min-width:180px; }
        .header-search button{ padding:6px 12px; border-radius:999px; border:1px solid rgba(0,0,0,.15); background:#fff; cursor:pointer; }
        .header-profile-link { text-decoration:none; }
    </style>
    <style data-injected="centered-search">
        /* Centered search in header */
        .header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .header-center{flex:1;display:flex;justify-content:center}
        .header-search{display:flex;gap:8px;align-items:center}
        .header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
        .header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
        /* Ensure profile bubble stands alone */
        .header-profile-link{display:inline-block;text-decoration:none}
    </style>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <!-- Barra superior - Mundial 2026 -->
    <header class="header">
        <div class="header-content">
            <div class="logo-container">
                <div class="logo">
                    <i class="fas fa-futbol"></i>
                </div>
                <div>
                    <h1>GolNet </h1>
                </div>
            </div>
            <div class="header-center">
                <form action="buscar.php" class="header-search" method="GET">
                    <input name="q" placeholder="Buscar..." type="search"/>
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <div class="countdown">
                <a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
                <a class="header-profile-link" href="mis_publicaciones.php">
                    <div class="header-profile-mini">
                        <img alt="Foto de perfil" src="<?php echo $photoSrc; ?>"/>
                        <span class="name"><?php echo htmlspecialchars($displayName); ?></span>
                    </div>
                </a>
            </div>
            <?php endif; ?>
        </div>
        <button class="menu-toggle" id="menuToggle">
            <i class="fas fa-bars"></i>
        </button>
    </header>

    <!-- Contenedor principal -->
    <div class="container">
        <!-- Barra lateral izquierda - Perfil de Usuario -->
        <aside class="sidebar left-sidebar" id="leftSidebar">
            <ul>
                <li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
                <li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
                 <?php
                // Comprobar si NO existe la variable de sesión 'user_id' (es decir, NO está logueado)
                if (!isset($_SESSION['user_id'])): 
                ?>
                    <li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
                <?php endif; ?>
                <?php if ($userType === 0): ?>
                <li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
                <?php endif; ?>
                <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
                <li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
                <li><a href="javascript:history.back()" onclick="return true;"><i class="fas fa-undo"></i><span>Volver Atrás</span></a></li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="publicacion-container">
                <div class="publicacion-header">
                    <h2><i class="fas fa-plus-circle"></i> Crear Nueva Publicación</h2>
                    <p>Comparte noticias, fotos y videos del Mundial 2026</p>
                </div>

                <form class="publicacion-form" method="POST" enctype="multipart/form-data">
                    <!-- Título de la publicación -->
                    <div class="form-group">
                        <label for="titulo"><i class="fas fa-heading"></i> Título de la publicación</label>
                        <input type="text" id="titulo" name="Titulo" class="form-input"
                            placeholder="Ej: Argentina gana el partido contra Brasil" required>
                    </div>

                    <!-- Contenido de la publicación -->
                    <div class="form-group">
                        <label for="contenido"><i class="fas fa-align-left"></i> Contenido</label>
                        <textarea id="contenido" name="Descripcion" class="form-textarea" placeholder="Escribe el contenido de tu publicación..."
                            required></textarea>
                    </div>

                    <!-- Categoría -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Categoría</label>
                        <div class="categoria-tags">
                            <select id="worldCupYear" name="ID_categ" class="categoria-tag">
                            <option value="">-- Selecciona la categoria --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['ID_categ']); ?>">
                                            <?php echo htmlspecialchars($cat['Nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Etiquetas del Mundial -->
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> Etiquetas del Mundial</label>
                        <div class="categoria-tags">
                            <select id="worldCupYear" name="ID_Mundial" class="categoria-tag">
                                <option value="">-- Selecciona el año del mundial --</option>
                                <?php foreach ($mundial as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['ID_Mundial']); ?>">
                                            <?php echo htmlspecialchars($cat['Nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                                <!-- ... otras opciones de mundial ... -->
                            </select>
                        </div>
                    </div>

                    <!-- Multimedia -->
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Multimedia (Opcional)</label>
                        <div class="file-upload" id="uploadArea">
                            <button type="button" class="btn btn-upload"
                                onclick="document.getElementById('mediaFile').click()">
                                <i class="fa-solid fa-photo-film"></i>
                                Seleccionar archivos
                            </button>
                            <input type="file" id="mediaFile" name="Multimedia" class="file-input" accept="image/*,video/*" multiple>
                            <div class="media-preview" id="mediaPreview"></div>
                        </div>
                    </div>

                    <!-- Botones  -->
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='inicio.php'">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Publicar
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Inicializar CKEditor 5 para el campo de contenido -->
            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const textarea = document.querySelector('#contenido');
                    if (!textarea) return;

                    ClassicEditor
                        .create(textarea, {
                            toolbar: {
                                items: [
                                    'heading', '|', 'bold', 'italic', 'link',
                                    'bulletedList', 'numberedList', '|', 'undo', 'redo'
                                ]
                            }
                        })
                        .then(editor => {
                            // Mantener sincronizado el valor del textarea para validación y envío
                            editor.model.document.on('change:data', () => {
                                textarea.value = editor.getData();
                            });
                        })
                        .catch(error => {
                            console.error('Error al inicializar CKEditor:', error);
                        });
                });
            </script>
            
            <!-- Sistema de alertas  -->

            <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

            <?php if (!empty($error_message)) : ?>
            <script>
                Swal.fire({
                    icon: 'error',
                    title: '❌ Error al Publicar',
                    text: <?php echo json_encode($error_message); ?>,
                    confirmButtonColor: '#d33'
                });
            </script>
            <?php endif; ?>

            <?php if (isset($_GET['publicado']) && $_GET['publicado'] === 'exitoso') : ?>
            <script>
                Swal.fire({
                    icon: 'success',
                    title: '¡Publicación Exitosa!',
                    text: 'Tu publicación ha sido creada correctamente.',
                    confirmButtonColor: '#3085d6'
                });
                // Limpiar el parámetro GET de la URL para evitar que la alerta se muestre de nuevo al recargar
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.pathname);
                }
            </script>
            <?php endif; ?>

        </main>
    </div>
    <script src="../javascript/crear_publi.js"></script>
    <script src="../javascript/inicio.js"></script> <!-- Para el menu toggle -->

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Sobre el Mundial 2026</h3>
                <p>La Copa Mundial de la FIFA 2026</p>
            </div>
            <div class="footer-section">
                <h3>Enlaces Rápidos</h3>
                <div class="footer-links">
                    <a href="#">Inicio</a>
                    <a href="#">Noticias</a>
                </div>
            </div>
            <div class="footer-section">
                <h3>Contacto</h3>
                <div class="footer-contact">
                    <span><i class="fas fa-phone"></i> +52 123 456 789</span>
                    <span><i class="fas fa-envelope"></i> alumnos.fcfm@placeholder.com</span>
                </div>
                <div class="footer-social">
                    <a class="social-icon" href="#"><i class="fab fa-facebook-f"></i></a>
                    <a class="social-icon" href="#"><i class="fab fa-twitter"></i></a>
                    <a class="social-icon" href="#"><i class="fab fa-instagram"></i></a>
                    <a class="social-icon" href="#"><i class="fab fa-youtube"></i></a>
                    <a class="social-icon" href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>© Elaborado por alumnos de FCFM. Todos los derechos reservados.</p>
        </div>
    </footer>
</body>

</html>