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
                <form action="#" class="header-search" method="GET">
                    <input name="q" placeholder="Buscar..." type="search"/>
                    <button type="submit">Buscar</button>
                </form>
            </div>
            <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
            <div class="countdown">
                <a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
                <a class="header-profile-link" href="mis_publicaciones.php">
                    <div class="header-profile-mini">
                        <img alt="Foto de perfil" src="<?php echo htmlspecialchars($photoSrc); ?>"/>
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
                <li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
                <li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
                <li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
                <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
                <li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
            </ul>
        </aside>

        <!-- Contenido principal -->
        <main class="main-content">
            <div class="publicacion-container">
                <div class="publicacion-header">
                    <h2><i class="fas fa-plus-circle"></i> Crear Nueva Publicación</h2>
                    <p>Comparte noticias, fotos y videos del Mundial 2026</p>
                </div>

                <form class="publicacion-form" id="publicacionForm">
                    <!-- Título de la publicación -->
                    <div class="form-group">
                        <label for="titulo"><i class="fas fa-heading"></i> Título de la publicación</label>
                        <input type="text" id="titulo" class="form-input"
                            placeholder="Ej: Argentina gana el partido contra Brasil" required>
                    </div>

                    <!-- Contenido de la publicación -->
                    <div class="form-group">
                        <label for="contenido"><i class="fas fa-align-left"></i> Contenido</label>
                        <textarea id="contenido" class="form-textarea" placeholder="Escribe el contenido de tu publicación..."
                            required></textarea>
                    </div>

                    <!-- Categoría -->
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Categoría</label>
                        <div class="categoria-tags">
                            <select id="worldCupYear" class="categoria-tag">
                                <option value="">-- Selecciona una Categoria --</option>
                                <option value="noticia">Noticia</option>
                                <option value="resultado">Resultado</option>
                                <option value="analisis">Analisis</option>
                                <option value="opinion">Opinion</option>
                                <option value="multimedia">Multimedia</option>
                            </select>
                        </div>
                    </div>

                    <!-- Etiquetas del Mundial -->
                    <div class="form-group">
                        <label><i class="fas fa-hashtag"></i> Etiquetas del Mundial</label>
                        <div class="categoria-tags">
                            <select id="worldCupYear" class="categoria-tag">
                                <option value="">-- Selecciona un año --</option>
                                <option value="2022">Mundial 2022 - Catar</option>
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
                            <input type="file" id="mediaFile" class="file-input" accept="image/*,video/*" multiple>
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