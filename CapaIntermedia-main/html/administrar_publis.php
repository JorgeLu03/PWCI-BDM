<?php
session_start();

// Conexión a la base de datos
require_once '../BD/Connection/Connection.php';

require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

// --- Lógica para mensajes de feedback y pestaña activa ---
$feedback_message = $_SESSION['feedback_message'] ?? '';
$feedback_type = $_SESSION['feedback_type'] ?? '';
unset($_SESSION['feedback_message'], $_SESSION['feedback_type']); // Limpiar para que no se muestre de nuevo

// Determinar la pestaña activa. Si venimos de una redirección, usamos el parámetro GET.
if (isset($_GET['tab'])) {
    $active_tab = $_GET['tab'];
} else {
    $active_tab = 'publis'; // Pestaña por defecto si no se especifica
}


// --- Lógica para crear una nueva categoría ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_category'])) {
    $active_tab = 'create'; // Si se envía el form, la pestaña activa es 'create'
    $nombre = $_POST['categoria_nombre'] ?? '';
    $descripcion = $_POST['categoria_desc'] ?? '';
    $imagen_data = null;

    // Validar que los campos no estén vacíos
    if (!empty($nombre) && !empty($descripcion) && isset($_FILES['categoria_imagen']) && $_FILES['categoria_imagen']['error'] == 0) {
        // Leer el contenido binario de la imagen
        $imagen_data = file_get_contents($_FILES['categoria_imagen']['tmp_name']);

        if ($imagen_data !== false) {
            $stmt = $conn->prepare("CALL SP_NewCategory(?, ?, ?)");
            if ($stmt) {
                $null = NULL; // Necesario para bind_param
                $stmt->bind_param('ssb', $nombre, $descripcion, $null);
                $stmt->send_long_data(2, $imagen_data); // Enviar el BLOB

                if ($stmt->execute()) {
                    $_SESSION['feedback_message'] = "¡Categoría creada con éxito!";
                    $_SESSION['feedback_type'] = 'success';
                } else {
                    $_SESSION['feedback_message'] = "Error al crear la categoría: " . $stmt->error;
                    $_SESSION['feedback_type'] = 'error';
                }
                $stmt->close();
            } else {
                $_SESSION['feedback_message'] = "Error al preparar la consulta: " . $conn->error;
                $_SESSION['feedback_type'] = 'error';
            }
        }
    } else {
        $_SESSION['feedback_message'] = "Por favor, completa todos los campos, incluyendo la imagen.";
        $_SESSION['feedback_type'] = 'error';
    }
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?tab=create");
    exit();
}

// --- Lógica para crear un nuevo Mundial ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_mundial'])) {
    $active_tab = 'create';
    
    // Recoger todos los datos del formulario
    $nombre = $_POST['mundial_nombre'] ?? '';
    $anio = $_POST['mundial_anio'] ?? '';
    $descripcion = $_POST['mundial_resena'] ?? '';
    $sedes = $_POST['mundial_sedes'] ?? '';
    $balon = $_POST['mundial_balon'] ?? '';
    $campeon = $_POST['mundial_campeon'] ?? '';
    $subcampeon = $_POST['mundial_subcampeon'] ?? '';
    $tercer_lugar = $_POST['mundial_tercer_lugar'] ?? '';
    $cuarto_lugar = $_POST['mundial_cuarto_lugar'] ?? '';
    $final_fecha = $_POST['mundial_final_fecha'] ?? '';
    $final_lugar = $_POST['mundial_final_lugar'] ?? '';
    $marcador_final = $_POST['mundial_marcador'] ?? '';
    $tiempo_extra = $_POST['mundial_tiempo_extra'] ?? 0;
    $goleador = $_POST['mundial_goleador'] ?? '';
    $alineacion = $_POST['mundial_alineacion'] ?? '';
    $cantante = $_POST['mundial_cantante'] ?? null;
    $id_user = $_SESSION['user_id'] ?? 0;

    // Leer datos de las imágenes
    $logo_data = (isset($_FILES['mundial_logo']) && $_FILES['mundial_logo']['error'] == 0) ? file_get_contents($_FILES['mundial_logo']['tmp_name']) : null;
    $banner_data = (isset($_FILES['mundial_banner']) && $_FILES['mundial_banner']['error'] == 0) ? file_get_contents($_FILES['mundial_banner']['tmp_name']) : null;

    $stmt = $conn->prepare("CALL SP_NewMundial(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) { 
        // La cadena de tipos debe coincidir con los 19 parámetros del SP:
        // 1. Enlazamos todos los parámetros excepto los BLOBs. Para los BLOBs, pasamos NULL.
        $null = NULL;
    // La cadena de tipos debe coincidir con los 19 parámetros del SP.
    // 1:s, 2:i (año), 3:s, 4:i (ID_User), 5:s (Sede), ..., 14:i (TiempoExtra), 18:b (Logo), 19:b (Banner)
    $stmt->bind_param('sisisssssssssisssbb', $nombre, $anio, $descripcion, $id_user, $sedes, $balon, $campeon, $subcampeon, $tercer_lugar, $cuarto_lugar, $final_fecha, $final_lugar, $marcador_final, $tiempo_extra, $goleador, $alineacion, $cantante, $null, $null);
        
        // 2. Enviamos los datos BLOB por separado si existen.
        // El índice es 0-based. El 18º parámetro es el logo (índice 17).
        if ($logo_data) {
            $stmt->send_long_data(17, $logo_data);
        }
        // El 19º parámetro es el banner (índice 18).
        if ($banner_data) {
            $stmt->send_long_data(18, $banner_data);
        }
        
        if ($stmt->execute()) {
            $_SESSION['feedback_message'] = "¡Mundial creado con éxito!";
            $_SESSION['feedback_type'] = 'success';
        } else {
            $_SESSION['feedback_message'] = "Error al crear el mundial: " . $stmt->error;
            $_SESSION['feedback_type'] = 'error';
        }
        $stmt->close();
    } else {
        $_SESSION['feedback_message'] = "Error al preparar la consulta: " . $conn->error;
        $_SESSION['feedback_type'] = 'error';
    }
    // Redirigir para evitar reenvío del formulario
    header("Location: " . $_SERVER['PHP_SELF'] . "?tab=create");
    exit();
}

// --- Lógica para obtener publicaciones pendientes ---
$pending_publications = [];
$stmt_pending = $conn->prepare("CALL SP_GetPendingPublications()");
if ($stmt_pending && $stmt_pending->execute()) {
    $result_pending = $stmt_pending->get_result();
    $pending_publications = $result_pending->fetch_all(MYSQLI_ASSOC);
    $stmt_pending->close();
    while ($conn->more_results() && $conn->next_result()) {;}
}

// --- Lógica para obtener comentarios pendientes ---
$pending_comments = [];
$stmt_comments = $conn->prepare("CALL SP_GetPendingComments()");
if ($stmt_comments && $stmt_comments->execute()) {
    $result_comments = $stmt_comments->get_result();
    $pending_comments = $result_comments->fetch_all(MYSQLI_ASSOC);
    $stmt_comments->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Copa Mundial FIFA 2026</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="../css/inicio.css" rel="stylesheet"/>
<link href="../css/admin.css" rel="stylesheet"/>
<style data-injected="header-perfil">
.header-profile-mini{display:inline-flex;align-items:center;gap:10px;padding:6px 10px;background:rgba(255,255,255,0.08);border-radius:999px;border:1px solid rgba(255,255,255,.15)}
.header-profile-mini img{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(0,0,0,.2)}
.header-profile-mini .name{font-weight:600}
</style><style data-injected="header-search">
.header-content, .countdown { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
.header-search { display:flex; gap:8px; align-items:center; }
.header-search input[type="search"]{ padding:6px 10px; border-radius:999px; border:1px solid rgba(0,0,0,.15); min-width:180px; }
.header-search button{ padding:6px 12px; border-radius:999px; border:1px solid rgba(0,0,0,.15); background:#fff; cursor:pointer; }
.header-profile-link { text-decoration:none; }
</style><style data-injected="centered-search">
/* Centered search in header */
.header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.header-center{flex:1;display:flex;justify-content:center}
.header-search{display:flex;gap:8px;align-items:center}
.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
.header-profile-link{display:inline-block;text-decoration:none}
</style></head>
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
</div><div class="header-center"><form action="#" class="header-search" method="GET"><input name="q" placeholder="Buscar..." type="search"/><button type="submit">Buscar</button></form></div>
<?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
<div class="countdown">
<a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
<a class="header-profile-link" href="mis_publicaciones.php"><div class="header-profile-mini"><img alt="Foto de perfil" src="<?php echo $photoSrc; ?>"/><span class="name"><?php echo htmlspecialchars($displayName); ?></span></div></a>
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
<div class="user-profile" style="display:none">
<div class="user-avatar">
<i class="fas fa-user"></i>
</div>
<div class="user-name">Luis Venegas</div>
<div class="user-country">
<i class="fas fa-flag"></i>
<span>Mexico</span>
</div>
</div>
<ul>
<li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
<li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
<li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
<li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
<?php if ($userType === 0): ?>
<li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<?php endif; ?>
<li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
<li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
    <h2>Administrar Contenido</h2>

    <div class="admin-toggle-buttons">
        <button class="toggle-btn <?php echo ($active_tab === 'publis') ? 'active' : ''; ?>" id="btn-show-publis">Publicaciones</button>
        <button class="toggle-btn <?php echo ($active_tab === 'comments') ? 'active' : ''; ?>" id="btn-show-comments">Comentarios</button>
        <button class="toggle-btn <?php echo ($active_tab === 'create') ? 'active' : ''; ?>" id="btn-show-create">Crear</button>
    </div>

    <!-- Sección de Publicaciones -->
    <div id="admin-publis-section" style="<?php echo ($active_tab !== 'publis') ? 'display: none;' : ''; ?>">
        <?php if (count($pending_publications) > 0): ?>
            <?php foreach ($pending_publications as $pub): ?>
                <div class="worldcup-container" id="pub-<?php echo $pub['ID_Publi']; ?>">
                    <div class="worldcup-info">
                        <h3><?php echo htmlspecialchars($pub['Titulo']); ?></h3>
                        <div class="post-meta">
                            <span class="user-publish"><?php echo htmlspecialchars($pub['Nombre_Usuario']); ?></span>
                            <span class="separator">|</span>
                            <span class="user-publish"><?php echo date("d M, Y", strtotime($pub['Fec_pub'])); ?></span>
                            <span class="separator">|</span>
                            <span class="user-publish"><?php echo htmlspecialchars($pub['Nombre_Categoria']); ?></span>
                            <span class="separator">|</span>
                            <span class="user-publish"><?php echo htmlspecialchars($pub['Nombre_Mundial']); ?></span>
                        </div>
                        <p><?php echo nl2br(htmlspecialchars($pub['Descripcion'])); ?></p>
                        <?php if (!empty($pub['Multimedia']) && !empty($pub['TipoMultimedia'])): ?>
                            <div class="media-container">
                                <?php
                                    $media_type = $pub['TipoMultimedia'];
                                    $media_src = 'data:' . $media_type . ';base64,' . base64_encode($pub['Multimedia']);
                                ?>
                                <?php if (strpos($media_type, 'image/') === 0): ?>
                                    <img alt="Multimedia" class="publish-media" src="<?php echo $media_src; ?>"/>
                                <?php elseif (strpos($media_type, 'video/') === 0): ?>
                                    <video class="publish-media" controls><source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>"></video>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="post-actions">
                        <button class="action-btn approve-btn" data-id="<?php echo $pub['ID_Publi']; ?>">
                            <i class="fa-solid fa-thumbs-up"></i> Aprobar
                        </button>
                        <button class="action-btn reject-btn" data-id="<?php echo $pub['ID_Publi']; ?>">
                            <i class="fa-solid fa-xmark"></i> Rechazar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem;">No hay publicaciones pendientes de aprobación.</p>
        <?php endif; ?>
    </div>

    <!-- Sección de Comentarios -->
    <div id="admin-comments-section" style="<?php echo ($active_tab !== 'comments') ? 'display: none;' : ''; ?>">
        <?php if (count($pending_comments) > 0): ?>
            <?php foreach ($pending_comments as $comment): ?>
                <div class="worldcup-container" id="comment-<?php echo $comment['ID_Coment']; ?>">
                    <div class="worldcup-info comment">
                        <h4>Comentario de: <span class="user-publish" style="font-weight: bold;"><?php echo htmlspecialchars($comment['NombreUsuario']); ?></span></h4>
                        <p><?php echo nl2br(htmlspecialchars($comment['Contenido'])); ?></p>
                        <div class="post-meta" style="margin-top: 10px;">
                            <span>En la publicación: <a href="comentarios_publi.php?id=<?php echo $comment['ID_Publi']; ?>" target="_blank"><strong><?php echo htmlspecialchars($comment['TituloPublicacion']); ?></strong></a></span>
                        </div>
                    </div>
                    <div class="post-actions">
                        <button class="action-btn approve-comment-btn" data-id="<?php echo $comment['ID_Coment']; ?>">
                            <i class="fa-solid fa-thumbs-up"></i> Aprobar
                        </button>
                        <button class="action-btn reject-comment-btn" data-id="<?php echo $comment['ID_Coment']; ?>">
                            <i class="fa-solid fa-xmark"></i> Rechazar
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center; padding: 2rem;">No hay comentarios pendientes de aprobación.</p>
        <?php endif; ?>
    </div>

    <!-- Sección de Creación -->
    <div id="admin-create-section" style="<?php echo ($active_tab !== 'create') ? 'display: none;' : ''; ?>">
        <!-- Formulario para Crear Mundial -->
        <?php if (!empty($feedback_message)): ?>
            <div class="worldcup-container">
                <div class="worldcup-info" style="background-color: <?php echo $feedback_type === 'success' ? '#28a745' : '#dc3545'; ?>; color: white; text-align: center;">
                    <p><?php echo htmlspecialchars($feedback_message); ?></p>
                </div>
            </div>
        <?php endif; ?>



        <div class="worldcup-container">
            <div class="worldcup-info">
                <h3><i class="fas fa-trophy"></i> Crear Nuevo Mundial</h3>
                <form class="admin-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="mundial-logo">Logo</label>
                        <input type="file" id="mundial-logo" name="mundial_logo" class="form-input-file" accept="image/*">
                        <label for="mundial-logo"><i class="fas fa-upload"></i> Seleccionar Logotipo</label>
                        <div class="media-preview" id="mundial-logo-preview"></div>
                    </div>
                    <div class="form-group">
                        <label for="mundial-banner">Banner</label>
                        <input type="file" id="mundial-banner" name="mundial_banner" class="form-input-file" accept="image/*">
                        <label for="mundial-banner"><i class="fas fa-upload"></i> Seleccionar Banner</label>
                        <div class="media-preview" id="mundial-banner-preview"></div>
                    </div>
                    <div class="form-group">
                        <label for="mundial-nombre">Nombre del mundial</label>
                        <input type="text" id="mundial-nombre" name="mundial_nombre" class="form-input-text" placeholder="Ej: Mundial 2026 - Norteamérica" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-anio">Año</label>
                        <input type="number" id="mundial-anio" name="mundial_anio" class="form-input-text" placeholder="Ej: 2026" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-resena">Descripción</label>
                        <textarea id="mundial-resena" name="mundial_resena" class="form-input-textarea" placeholder="Describe brevemente el mundial..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="mundial-sedes">Sedes</label>
                        <input type="text" id="mundial-sedes" name="mundial_sedes" class="form-input-text" placeholder="Ej: Ciudad de México, Guadalajara, Monterrey" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-balon">Balón oficial</label>
                        <input type="text" id="mundial-balon" name="mundial_balon" class="form-input-text" placeholder="Ej: Adidas Telstar" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-campeon">Campeón</label>
                        <input type="text" id="mundial-campeon" name="mundial_campeon" class="form-input-text" placeholder="Ej: Argentina" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-subcampeon">Subcampeón</label>
                        <input type="text" id="mundial-subcampeon" name="mundial_subcampeon" class="form-input-text" placeholder="Ej: Francia" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-tercer-lugar">Tercer lugar</label>
                        <input type="text" id="mundial-tercer-lugar" name="mundial_tercer_lugar" class="form-input-text" placeholder="Ej: Croacia" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-cuarto-lugar">Cuarto lugar</label>
                        <input type="text" id="mundial-cuarto-lugar" name="mundial_cuarto_lugar" class="form-input-text" placeholder="Ej: Marruecos" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-final-fecha">Fecha de la final</label>
                        <input type="date" id="mundial-final-fecha" name="mundial_final_fecha" class="form-input-text" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-final-lugar">Lugar de la final</label>
                        <input type="text" id="mundial-final-lugar" name="mundial_final_lugar" class="form-input-text" placeholder="Ej: Estadio Lusail, Catar" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-marcador">Marcador final</label>
                        <input type="text" id="mundial-marcador" name="mundial_marcador" class="form-input-text" placeholder="Ej: 3-3 (4-2 pen.)" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-tiempo-extra">¿Hubo tiempo extra?</label>
                        <select id="mundial-tiempo-extra" name="mundial_tiempo_extra" class="form-input-text" required>
                            <option value="0">No</option>
                            <option value="1">Sí</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mundial-goleador">Goleador del torneo</label>
                        <input type="text" id="mundial-goleador" name="mundial_goleador" class="form-input-text" placeholder="Ej: Kylian Mbappé (8 goles)" required>
                    </div>
                    <div class="form-group">
                        <label for="mundial-alineacion">Alineación del equipo campeón</label>
                        <select id="mundial-alineacion" name="mundial_alineacion" class="form-input-text" required>
                            <option value="">-- Selecciona una alineación --</option>
                            <option value="4-4-2">4-4-2</option>
                            <option value="4-3-3">4-3-3</option>
                            <option value="4-5-1">4-5-1</option>
                            <option value="3-5-2">3-5-2</option>
                            <option value="3-4-3">3-4-3</option>
                            <option value="5-3-2">5-3-2</option>
                            <option value="4-2-3-1">4-2-3-1</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="mundial-cantante">Cantante(Opcional)</label>
                        <input type="text" id="mundial-cantante" name="mundial_cantante" class="form-input-text" placeholder="Ej: Shakira, Maluma">
                    </div>

                    <div class="post-actions">
                        <button type="submit" name="create_mundial" class="action-btn like-btn">
                            <i class="fas fa-save"></i> Guardar Mundial
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Formulario para Crear Categoría -->
        <div class="worldcup-container">
            <div class="worldcup-info">
                <h3><i class="fas fa-tags"></i> Crear Nueva Categoría</h3>
                <form class="admin-form" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="categoria-imagen">Imagen de la Categoría</label>
                        <input type="file" id="categoria-imagen" name="categoria_imagen" class="form-input-file" accept="image/*" required>
                        <label for="categoria-imagen"><i class="fas fa-upload"></i> Seleccionar Imagen</label>
                        <div class="media-preview" id="categoria-imagen-preview"></div>
                    </div>
                    <div class="form-group">
                        <label for="categoria-nombre">Nombre de la Categoría</label>
                        <input type="text" id="categoria-nombre" name="categoria_nombre" class="form-input-text" placeholder="Ej: Goles Memorables" required>
                    </div>
                    <div class="form-group">
                        <label for="categoria-desc">Descripción</label>
                        <textarea id="categoria-desc" name="categoria_desc" class="form-input-textarea" placeholder="Describe de qué trata la categoría..." required></textarea>
                    </div>
                    <div class="post-actions">
                        <button type="submit" name="create_category" class="action-btn like-btn">
                            <i class="fas fa-save"></i> Guardar Categoría
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main> 
</div>
<script src="../javascript/inicio.js"></script>
<script>
function setupImagePreview(inputId, previewId) {
    const input = document.getElementById(inputId);
    const previewContainer = document.getElementById(previewId);

    if (input && previewContainer) {
        input.addEventListener('change', function(event) {
            previewContainer.innerHTML = ''; // Limpiar previsualización anterior
            const file = event.target.files[0];

            if (file && file.type.startsWith('image/')) {
                const img = document.createElement('img');
                img.src = URL.createObjectURL(file);
                img.onload = () => {
                    URL.revokeObjectURL(img.src); // Liberar memoria
                }
                previewContainer.appendChild(img);
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    setupImagePreview('mundial-logo', 'mundial-logo-preview');
    setupImagePreview('mundial-banner', 'mundial-banner-preview');
    setupImagePreview('categoria-imagen', 'categoria-imagen-preview');

    const btnPublis = document.getElementById('btn-show-publis');
    const btnComments = document.getElementById('btn-show-comments');
    const btnCreate = document.getElementById('btn-show-create');

    const sectionPublis = document.getElementById('admin-publis-section');
    const sectionComments = document.getElementById('admin-comments-section');
    const sectionCreate = document.getElementById('admin-create-section');

    const buttons = [btnPublis, btnComments, btnCreate];
    const sections = [sectionPublis, sectionComments, sectionCreate];

    function toggleSections(activeBtn, activeSection) {
        buttons.forEach(btn => btn.classList.remove('active'));
        sections.forEach(sec => sec.style.display = 'none');

        activeBtn.classList.add('active');
        activeSection.style.display = 'block';
    }

    // El estado inicial ya se establece con PHP, estos listeners son para los clics del usuario
    btnPublis.addEventListener('click', () => toggleSections(btnPublis, sectionPublis));
    btnComments.addEventListener('click', () => toggleSections(btnComments, sectionComments));
    btnCreate.addEventListener('click', () => toggleSections(btnCreate, sectionCreate));

    // --- Lógica para aprobar/rechazar publicaciones ---
    document.querySelectorAll('.approve-btn, .reject-btn').forEach(button => {
        button.addEventListener('click', function() {
            const publiId = this.dataset.id;
            const action = this.classList.contains('approve-btn') ? 'approve' : 'reject';

            if (action === 'reject') {
                Swal.fire({
                    title: 'Motivo del Rechazo',
                    input: 'textarea',
                    inputPlaceholder: 'Escribe aquí por qué se rechaza la publicación...',
                    showCancelButton: true,
                    confirmButtonText: 'Rechazar Publicación',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545',
                    showLoaderOnConfirm: true,
                    preConfirm: (reason) => {
                        if (!reason || reason.trim() === '') {
                            Swal.showValidationMessage('El motivo del rechazo no puede estar vacío.');
                            return false;
                        }
                        return reason;
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        handlePublicationAction(publiId, action, result.value);
                    }
                });
            } else { // Para 'approve'
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Vas a APROBAR esta publicación y será visible para todos.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, ¡aprobar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        handlePublicationAction(publiId, action);
                    }
                });
            }
        });
    });

    // --- Lógica para aprobar/rechazar comentarios ---
    document.querySelectorAll('.approve-comment-btn, .reject-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.id;
            const action = this.classList.contains('approve-comment-btn') ? 'approve' : 'reject';

            if (action === 'approve') {
                Swal.fire({
                    title: '¿Aprobar Comentario?',
                    text: "El comentario será visible para todos.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    confirmButtonText: 'Sí, ¡aprobar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleCommentAction(commentId, action);
                    }
                });
            } else { // Para 'reject'
                Swal.fire({
                    title: '¿Rechazar Comentario?',
                    text: "El comentario será eliminado permanentemente. Esta acción no se puede deshacer.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Sí, ¡rechazar!',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        handleCommentAction(commentId, action);
                    }
                });
            }
        });
    });

    function handleCommentAction(commentId, action) {
        const formData = new FormData();
        formData.append('comment_id', commentId);
        formData.append('action', action);

        fetch('comment_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cardToRemove = document.getElementById('comment-' + commentId);
                if (cardToRemove) {
                    cardToRemove.style.transition = 'opacity 0.5s ease';
                    cardToRemove.style.opacity = '0';
                    setTimeout(() => {
                        cardToRemove.remove();
                        if (document.querySelectorAll('#admin-comments-section .worldcup-container').length === 0) {
                            document.getElementById('admin-comments-section').innerHTML = '<p style="text-align: center; padding: 2rem;">No hay más comentarios pendientes.</p>';
                        }
                    }, 500);
                }
            } else {
                Swal.fire('Error', data.error || 'No se pudo procesar la solicitud.', 'error');
            }
        })
        .catch(error => {
            console.error('Error en la petición fetch:', error);
            Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
        });
    }

    function handlePublicationAction(publiId, action, reason = null) {
        const formData = new FormData();
        formData.append('publi_id', publiId);
        formData.append('action', action);
        if (reason) {
            formData.append('reason', reason);
        }

        fetch('publication_action_handler.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cardToRemove = document.getElementById('pub-' + publiId);
                if (cardToRemove) {
                    cardToRemove.style.transition = 'opacity 0.5s ease';
                    cardToRemove.style.opacity = '0';
                    setTimeout(() => {
                        cardToRemove.remove();
                        if (document.querySelectorAll('#admin-publis-section .worldcup-container').length === 0) {
                            document.getElementById('admin-publis-section').innerHTML = '<p style="text-align: center; padding: 2rem;">No hay más publicaciones pendientes.</p>';
                        }
                    }, 500);
                }
            } else {
                Swal.fire('Error', data.error || 'No se pudo procesar la solicitud.', 'error');
            }
        })
        .catch(error => {
            console.error('Error en la petición fetch:', error);
            Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
        });
    }

});
</script>
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