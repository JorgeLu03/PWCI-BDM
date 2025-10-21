<?php
session_start();

// Conexión a la base de datos
require_once '../BD/Connection/Connection.php';

$displayName = 'Mi Perfil';
$photoSrc = '../css/PlaceHolder3.png';

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $uid = (int) $_SESSION['user_id'];
    $stmt = $conn->prepare("SELECT Nombre, Foto FROM USUARIO WHERE ID_User = ?");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (!empty($row['Nombre'])) {
                $displayName = htmlspecialchars($row['Nombre']);
            }
            if (!empty($row['Foto'])) {
                // Si la ruta es relativa, añadir prefijo; si es absoluta o URL, usarla tal cual
                $foto = $row['Foto'];
                if (strpos($foto, 'http') === 0 || strpos($foto, '/') === 0) {
                    $photoSrc = $foto;
                } else {
                    $photoSrc = '../' . ltrim($foto, '/');
                }
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Copa Mundial FIFA 2026</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
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
<a class="header-profile-link" href="mis_publicaciones.php"><div class="header-profile-mini"><img alt="Foto de perfil" src="<?php echo htmlspecialchars($photoSrc); ?>"/><span class="name"><?php echo htmlspecialchars($displayName); ?></span></div></a>
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
<li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
<li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
    <h2>Administrar Contenido</h2>

    <div class="admin-toggle-buttons">
        <button class="toggle-btn active" id="btn-show-publis">Publicaciones</button>
        <button class="toggle-btn" id="btn-show-comments">Comentarios</button>
        <button class="toggle-btn" id="btn-show-create">Crear</button>
    </div>

    <!-- Sección de Publicaciones -->
    <div id="admin-publis-section">
        <div class="worldcup-container">
            <div class="worldcup-info">
                <h3>Título Publicación</h3>
                <div class="post-meta">
                    <span class="user-publish">Usuario</span>
                    <span class="separator">|</span>
                    <span class="user-publish">Fecha</span>
                    <span class="separator">|</span>
                    <span class="user-publish">Categoria</span>
                    <span class="separator">|</span>
                    <span class="user-publish">Mundial</span>
                </div>
                <p>El torneo de fútbol más grande del mundo llega a Norteamérica...</p>
                <div class="media-container">
                    <img alt="Foto" class="publish-media" src="../css/PlaceHolder3.png"/>
                    <video class="publish-media" controls="">
                        <source src="../css/PlaceHolder3.mp4" type="video/mp4"/>
                    </video>
                </div>
            </div>
            <div class="post-actions">
                <button class="action-btn like-btn">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar
                </button>
                <button class="action-btn comment-btn">
                    <i class="fa-solid fa-xmark"></i> Rechazar
                </button>
            </div>
        </div>
    </div>

    <!-- Sección de Comentarios -->
    <div id="admin-comments-section" style="display: none;">
        <div class="worldcup-container">
            <div class="worldcup-info comment">
                <h4>Comentario de: <span class="user-publish" style="font-weight: bold;">Usuario Ejemplo</span></h4>
                <p>Este es un comentario de ejemplo que está pendiente de moderación. El administrador puede aprobarlo o rechazarlo.</p>
                <div class="post-meta" style="margin-top: 10px;">
                    <span>En la publicación: <strong>"Título de la Publicación Original"</strong></span>
                </div>
            </div>
            <div class="post-actions">
                <button class="action-btn like-btn">
                    <i class="fa-solid fa-thumbs-up"></i> Aprobar
                </button>
                <button class="action-btn comment-btn">
                    <i class="fa-solid fa-xmark"></i> Rechazar
                </button>
            </div>
        </div>
    </div>

    <!-- Sección de Creación -->
    <div id="admin-create-section" style="display: none;">
        <!-- Formulario para Crear Mundial -->
        <div class="worldcup-container">
            <div class="worldcup-info">
                <h3><i class="fas fa-trophy"></i> Crear Nuevo Mundial</h3>
                <form class="admin-form">
                    <div class="form-group">
                        <label for="mundial-logo">Logotipo del Mundial</label>
                        <input type="file" id="mundial-logo" class="form-input-file" accept="image/*">
                        <label for="mundial-logo"><i class="fas fa-upload"></i> Seleccionar Logotipo</label>
                        <div class="media-preview" id="mundial-logo-preview"></div>
                    </div>
                    <div class="form-group">
                        <label for="mundial-nombre">Nombre del Mundial</label>
                        <input type="text" id="mundial-nombre" class="form-input-text" placeholder="Ej: Mundial 2026 - Norteamérica">
                    </div>
                    <div class="form-group">
                        <label for="mundial-anio">Año</label>
                        <input type="number" id="mundial-anio" class="form-input-text" placeholder="Ej: 2026">
                    </div>
                    <div class="form-group">
                        <label for="mundial-resena">Breve Reseña</label>
                        <textarea id="mundial-resena" class="form-input-textarea" placeholder="Describe brevemente el mundial..."></textarea>
                    </div>
                    <div class="post-actions">
                        <button type="submit" class="action-btn like-btn">
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
                <form class="admin-form">
                    <div class="form-group">
                        <label for="categoria-imagen">Imagen de la Categoría</label>
                        <input type="file" id="categoria-imagen" class="form-input-file" accept="image/*">
                        <label for="categoria-imagen"><i class="fas fa-upload"></i> Seleccionar Imagen</label>
                        <div class="media-preview" id="categoria-imagen-preview"></div>
                    </div>
                    <div class="form-group">
                        <label for="categoria-nombre">Nombre de la Categoría</label>
                        <input type="text" id="categoria-nombre" class="form-input-text" placeholder="Ej: Goles Memorables">
                    </div>
                    <div class="form-group">
                        <label for="categoria-desc">Descripción</label>
                        <textarea id="categoria-desc" class="form-input-textarea" placeholder="Describe de qué trata la categoría..."></textarea>
                    </div>
                    <div class="post-actions">
                        <button type="submit" class="action-btn like-btn">
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
document.addEventListener('DOMContentLoaded', function () {
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

    btnPublis.addEventListener('click', () => {
        toggleSections(btnPublis, sectionPublis);
    });

    btnComments.addEventListener('click', () => {
        toggleSections(btnComments, sectionComments);
    });

    btnCreate.addEventListener('click', () => {
        toggleSections(btnCreate, sectionCreate);
    });
});

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
    setupImagePreview('categoria-imagen', 'categoria-imagen-preview');
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