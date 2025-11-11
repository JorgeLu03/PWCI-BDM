<?php
session_start();

// --- Restringir acceso a usuarios no logueados ---
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header('Location: Iniciar_sesion.php');
    exit();
}

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

$error_message = '';
$publi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = (int)$_SESSION['user_id'];

if ($publi_id <= 0) {
    header('Location: mis_publicaciones.php');
    exit();
}

// --- Obtener datos de la publicación para editar ---
$stmt_get = $conn->prepare("CALL SP_GetPublicationForEdit(?, ?)");
if (!$stmt_get) {
    die("Error al preparar la consulta para obtener la publicación: " . $conn->error);
}
$stmt_get->bind_param('ii', $publi_id, $user_id);
$stmt_get->execute();
$result_get = $stmt_get->get_result();
if ($result_get->num_rows === 0) {
    // El usuario no es el dueño o la publicación no está rechazada
    header('Location: mis_publicaciones.php');
    exit();
}
$pub_data = $result_get->fetch_assoc();
$stmt_get->close();
while ($conn->more_results() && $conn->next_result()) {;}


// --- Lógica para actualizar la publicación ---
if ($_SERVER["REQUEST_METHOD"] === 'POST') {
    $Titulo_P = $_POST['Titulo'] ?? '';
    $Descripcion_P = $_POST['Descripcion'] ?? '';
    $ID_Categ_P = (int)($_POST['ID_categ'] ?? 0);
    $ID_Mundial_P = (int)($_POST['ID_Mundial'] ?? 0);

    if (empty($Titulo_P) || empty($Descripcion_P) || empty($ID_Categ_P) || empty($ID_Mundial_P)) {
        $error_message = 'Por favor, completa todos los campos.';
    } else {
        $multimedia_data = null;
        $multimedia_type = null;

        if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
            $multimedia_type = $_FILES['Multimedia']['type'];
        }

        $stmt_update = $conn->prepare("CALL SP_UpdatePublication(?, ?, ?, ?, ?, ?, ?)");
        if ($stmt_update) {
            $null_blob = NULL;
            $stmt_update->bind_param('issbsii', 
                $publi_id, 
                $Titulo_P, 
                $Descripcion_P, 
                $null_blob, 
                $multimedia_type, 
                $ID_Categ_P, 
                $ID_Mundial_P
            );

            if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
                $file_path = $_FILES['Multimedia']['tmp_name'];
                $fp = fopen($file_path, 'r');
                if ($fp) {
                    while (!feof($fp)) {
                        $chunk = fread($fp, 8192);
                        $stmt_update->send_long_data(3, $chunk); // El 4º '?' (índice 3) es el BLOB
                    }
                    fclose($fp);
                }
            }

            if ($stmt_update->execute()) {
                header('Location: mis_publicaciones.php?edit=success');
                exit;
            } else {
                $error_message = 'Error al actualizar la publicación: ' . $stmt_update->error;
            }
            $stmt_update->close();
        } else {
            $error_message = 'Error al preparar la actualización: ' . $conn->error;
        }
    }
}

// --- Obtener categorías y mundiales para los selects ---
$sql_mundial = "CALL Seleccionar_Dato_Condicional(1);";
$sql_categoria = "CALL Seleccionar_Dato_Condicional(2);";

$categorias = [];
if ($stmt_cat = $conn->prepare($sql_categoria)) {
    $stmt_cat->execute();
    $result_cat = $stmt_cat->get_result();
    $categorias = $result_cat->fetch_all(MYSQLI_ASSOC);
    $stmt_cat->close();
}
while ($conn->more_results() && $conn->next_result()) {;}

$mundiales = [];
if ($stmt_mun = $conn->prepare($sql_mundial)) {
    $stmt_mun->execute();
    $result_mun = $stmt_mun->get_result();
    $mundiales = $result_mun->fetch_all(MYSQLI_ASSOC);
    $stmt_mun->close();
}
while ($conn->more_results() && $conn->next_result()) {;}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - Mundial 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inicio.css">
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
        .header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
        .header-center{flex:1;display:flex;justify-content:center}
        .header-search{display:flex;gap:8px;align-items:center}
        .header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
        .header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
        .header-profile-link{display:inline-block;text-decoration:none}
    </style>

    <!-- CKEditor 5 -->
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
                 <?php if (!isset($_SESSION['user_id'])): ?>
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
                    <h2><i class="fas fa-edit"></i> Editar Publicación</h2>
                    <p>Corrige tu publicación y reenvíala para su aprobación.</p>
                </div>

                <form class="publicacion-form" method="POST" enctype="multipart/form-data">
                    <!-- Título de la publicación -->
                    <div class="form-group">
                        <label for="titulo"><i class="fas fa-heading"></i> Título de la publicación</label>
                        <input type="text" id="titulo" name="Titulo" class="form-input"
                            placeholder="Ej: Argentina gana el partido contra Brasil" required value="<?php echo htmlspecialchars($pub_data['Titulo']); ?>">
                    </div>

                    <!-- Contenido de la publicación -->
                    <div class="form-group">
                        <label for="contenido"><i class="fas fa-align-left"></i> Contenido</label>
                        <textarea id="contenido" name="Descripcion" class="form-textarea" placeholder="Escribe el contenido de tu publicación..."
                            required><?php echo $pub_data['Descripcion']; ?></textarea>
                    </div>

                    <!-- Categoría -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Categoría</label>
                        <div class="categoria-tags">
                            <select name="ID_categ" class="categoria-tag">
                            <option value="">-- Selecciona la categoria --</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat['ID_categ']); ?>" <?php echo ($cat['ID_categ'] == $pub_data['ID_Categ']) ? 'selected' : ''; ?>>
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
                            <select name="ID_Mundial" class="categoria-tag">
                                <option value="">-- Selecciona el año del mundial --</option>
                                <?php foreach ($mundiales as $mun): ?>
                                    <option value="<?php echo htmlspecialchars($mun['ID_Mundial']); ?>" <?php echo ($mun['ID_Mundial'] == $pub_data['ID_Mundial']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($mun['Nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Multimedia -->
                    <div class="form-group">
                        <label><i class="fas fa-image"></i> Multimedia (Opcional: selecciona un nuevo archivo para reemplazar el actual)</label>
                        <div class="file-upload" id="uploadArea">
                            <button type="button" class="btn btn-upload"
                                onclick="document.getElementById('mediaFile').click()">
                                <i class="fa-solid fa-photo-film"></i>
                                Cambiar archivo
                            </button>
                            <input type="file" id="mediaFile" name="Multimedia" class="file-input" accept="image/*,video/*">
                            <div class="media-preview" id="mediaPreview">
                                <?php if (!empty($pub_data['Multimedia'])): ?>
                                    <p>Multimedia actual:</p>
                                    <?php
                                        $media_type = $pub_data['TipoMultimedia'];
                                        $media_src = 'data:' . $media_type . ';base64,' . base64_encode($pub_data['Multimedia']);
                                    ?>
                                    <?php if (strpos($media_type, 'image/') === 0): ?>
                                        <img src="<?php echo $media_src; ?>" style="max-width: 200px; border-radius: 8px;">
                                    <?php elseif (strpos($media_type, 'video/') === 0): ?>
                                        <video src="<?php echo $media_src; ?>" style="max-width: 200px; border-radius: 8px;" controls></video>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Botones  -->
                    <div class="form-buttons">
                        <button type="button" class="btn btn-secondary" onclick="window.location.href='mis_publicaciones.php'">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Guardar y Reenviar
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
                            // Mantener sincronizado el valor del textarea para envío
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
                    title: '❌ Error al Actualizar',
                    text: <?php echo json_encode($error_message); ?>,
                    confirmButtonColor: '#d33'
                });
            </script>
            <?php endif; ?>

        </main>
    </div>
    <script src="../javascript/crear_publi.js"></script>
    <script src="../javascript/inicio.js"></script>

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