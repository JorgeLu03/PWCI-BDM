<?php
session_start();

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

// --- Lógica de búsqueda ---
$search_term = $_GET['q'] ?? '';
$publications = [];

if (!empty($search_term)) {
    $stmt = $conn->prepare("CALL SP_SearchPublications(?)");
    if ($stmt) {
        $stmt->bind_param('s', $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $publications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) {;}
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Resultados de búsqueda para "<?php echo htmlspecialchars($search_term); ?>"</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="../css/inicio.css" rel="stylesheet"/>
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
.header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.header-center{flex:1;display:flex;justify-content:center}
.header-search{display:flex;gap:8px;align-items:center}
.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
.header-profile-link{display:inline-block;text-decoration:none}
</style>
<style data-injected="publication-card-fix">
    .publication-card-media {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        background-color: #e0e0e0;
        overflow: hidden;
    }
    .publication-card-media img,
    .publication-card-media video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
</style>
</head>
<body>
<!-- Barra superior (Header) -->
<header class="header">
    <div class="header-content">
        <div class="logo-container">
            <div class="logo"><i class="fas fa-futbol"></i></div>
            <div><h1>GolNet</h1></div>
        </div>
        <div class="header-center">
            <form action="buscar.php" class="header-search" method="GET">
                <input name="q" placeholder="Buscar..." type="search" value="<?php echo htmlspecialchars($search_term); ?>"/>
                <button type="submit">Buscar</button>
            </form>
        </div>
        <?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
        <div class="countdown">
            <a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
            <a class="header-profile-link" href="mis_publicaciones.php"><div class="header-profile-mini"><img alt="Foto de perfil" src="<?php echo $photoSrc; ?>"/><span class="name"><?php echo htmlspecialchars($displayName); ?></span></div></a>
        </div>
        <?php endif; ?>
    </div>
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
</header>

<!-- Contenedor principal -->
<div class="container">
    <!-- Barra lateral (Sidebar) -->
    <aside class="sidebar left-sidebar" id="leftSidebar">
        <ul>
            <li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
            <li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
            <li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
            <?php endif; ?>
            <?php if ($userType === 0): ?>
            <li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
            <?php endif; ?>
            <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
            <li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
        </ul>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
        <h2>Resultados de búsqueda para: "<?php echo htmlspecialchars($search_term); ?>"</h2>

        <section class="infografia" id="infografia">
            <div class="cards-grid">
                <?php if (count($publications) > 0): ?>
                    <?php foreach ($publications as $pub): ?>
                        <?php
                            $idPubli = htmlspecialchars($pub['ID_Publi']);
                            $titulo = htmlspecialchars($pub['Titulo']);
                            $descripcionCorta = htmlspecialchars(substr($pub['Descripcion'], 0, 80)) . '...';
                        ?>
                        <a class="card-link" href="comentarios_publi.php?id=<?php echo $idPubli; ?>">
                            <article class="card publication-card">
                                <div class="publication-card-media">
                                    <?php if (!empty($pub['Multimedia']) && !empty($pub['TipoMultimedia'])): ?>
                                        <?php
                                            $media_type = $pub['TipoMultimedia'];
                                            $media_src = 'data:' . $media_type . ';base64,' . base64_encode($pub['Multimedia']);
                                        ?>
                                        <?php if (strpos($media_type, 'image/') === 0): ?>
                                            <img alt="<?php echo $titulo; ?>" src="<?php echo $media_src; ?>"/>
                                        <?php elseif (strpos($media_type, 'video/') === 0): ?>
                                            <video muted loop><source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>"></video>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <img alt="Sin multimedia" src="../css/PlaceHolder3.png"/>
                                    <?php endif; ?>
                                </div>
                                <div class="publication-card-content">
                                    <h3><?php echo $titulo; ?></h3>
                                    <p><?php echo $descripcionCorta; ?></p>
                                </div>
                            </article>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">No se encontraron publicaciones que coincidan con tu búsqueda.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script src="../javascript/inicio.js"></script>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section"><h3>Sobre el Mundial 2026</h3><p>La Copa Mundial de la FIFA 2026</p></div>
        <div class="footer-section"><h3>Enlaces Rápidos</h3><div class="footer-links"><a href="#">Inicio</a><a href="#">Noticias</a></div></div>
        <div class="footer-section"><h3>Contacto</h3><div class="footer-contact"><span><i class="fas fa-phone"></i> +52 123 456 789</span><span><i class="fas fa-envelope"></i> alumnos.fcfm@placeholder.com</span></div><div class="footer-social"><a class="social-icon" href="#"><i class="fab fa-facebook-f"></i></a><a class="social-icon" href="#"><i class="fab fa-twitter"></i></a><a class="social-icon" href="#"><i class="fab fa-instagram"></i></a><a class="social-icon" href="#"><i class="fab fa-youtube"></i></a><a class="social-icon" href="#"><i class="fab fa-tiktok"></i></a></div></div>
    </div>
    <div class="footer-bottom"><p>© Elaborado por alumnos de FCFM. Todos los derechos reservados.</p></div>
</footer>
</body>
</html>