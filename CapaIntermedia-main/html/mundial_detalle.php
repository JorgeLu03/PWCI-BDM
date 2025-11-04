<?php
session_start();

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

// --- Lógica para obtener detalles del usuario logueado ---
$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

// --- Lógica para obtener el mundial específico ---
$mundial_id = $_GET['id'] ?? 0;
$mundial_details = null;

if ($mundial_id > 0) {
    // Usamos la vista V_MundialDetalles para obtener los datos
    $stmt = $conn->prepare("SELECT * FROM V_Mundiales WHERE ID_Mundial = ?");
    if ($stmt) {
        $stmt->bind_param('i', $mundial_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $mundial_details = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// Si no se encuentra el mundial, redirigir o mostrar un error
if ($mundial_details === null) {
    header("Location: mundiales.php"); // Redirige a la lista de mundiales
    exit();
}

// --- Lógica para las imágenes (Banner y Logo) ---
$bannerSrc = '../css/PlaceHolder3.png'; // Imagen por defecto
if (!empty($mundial_details['Banner'])) {
    $bannerSrc = 'data:image/jpeg;base64,' . base64_encode($mundial_details['Banner']);
}
$logoSrc = '../css/PlaceHolder3.png'; // Imagen por defecto
if (!empty($mundial_details['Logo'])) {
    $logoSrc = 'data:image/png;base64,' . base64_encode($mundial_details['Logo']);
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($mundial_details['Nombre']); ?> - GolNet</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="../css/inicio.css" rel="stylesheet"/>
<link href="../css/mundial_detalle.css" rel="stylesheet"/>
<style>
    /* Sobrescribir el fondo solo para esta página */
    .main-content {
        background-color: #ffffff !important;
    }
    .main-content::before { display: none !important; }
</style>
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
</head>
<body>
<!-- Barra superior (Header) -->
<header class="header">
    <div class="header-content">
        <div class="logo-container">
            <div class="logo"><i class="fas fa-futbol"></i></div>
            <div><h1>GolNet</h1></div>
        </div>
        <div class="header-center"><form action="#" class="header-search" method="GET"><input name="q" placeholder="Buscar..." type="search"/><button type="submit">Buscar</button></form></div>
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
            <li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
            <?php if ($userType === 0): ?>
            <li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
            <?php endif; ?>
            <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
            <li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
        </ul>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
        <!-- Banner del Mundial -->
        <div class="mundial-banner" style="background-image: url('<?php echo $bannerSrc; ?>');"></div>

        <!-- Cabecera con Logo y Título -->
        <div class="mundial-header">
            <img src="<?php echo $logoSrc; ?>" alt="Logo <?php echo htmlspecialchars($mundial_details['Nombre']); ?>" class="mundial-logo">
            <div class="mundial-title">
                <h1><?php echo htmlspecialchars($mundial_details['Nombre']); ?></h1>
                <p><?php echo htmlspecialchars($mundial_details['Descripcion']); ?></p>
            </div>
        </div>

        <!-- Grid de Detalles del Mundial -->
        <div class="details-grid">
            <div class="detail-card">
                <h4><i class="fas fa-map-marker-alt"></i> Sedes</h4>
                <p><?php echo htmlspecialchars($mundial_details['Sede']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-futbol"></i> Balón Oficial</h4>
                <p><?php echo htmlspecialchars($mundial_details['Balon']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-trophy"></i> Campeón</h4>
                <p><?php echo htmlspecialchars($mundial_details['Campeon']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-medal"></i> Subcampeón</h4>
                <p><?php echo htmlspecialchars($mundial_details['Subcampeon']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-award"></i> Tercer Lugar</h4>
                <p><?php echo htmlspecialchars($mundial_details['TercerLugar']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-award"></i> Cuarto Lugar</h4>
                <p><?php echo htmlspecialchars($mundial_details['CuartoLugar']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-calendar-alt"></i> Fecha de la Final</h4>
                <p><?php echo date("d M, Y", strtotime($mundial_details['Fec_Final'])); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-stadium"></i> Lugar de la Final</h4>
                <p><?php echo htmlspecialchars($mundial_details['Lugar_Final']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-clipboard-check"></i> Marcador Final</h4>
                <p><?php echo htmlspecialchars($mundial_details['Marcador_Final']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-stopwatch"></i> Tiempo Extra</h4>
                <p><?php echo $mundial_details['TiempoExtra_Final'] ? 'Sí' : 'No'; ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-shoe-prints"></i> Goleador(es)</h4>
                <p><?php echo htmlspecialchars($mundial_details['Goleador']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-users"></i> Alineación del Campeón</h4>
                <p><?php echo htmlspecialchars($mundial_details['Alineacion_Campeon']); ?></p>
            </div>
            <div class="detail-card">
                <h4><i class="fas fa-microphone"></i> Artista Principal</h4>
                <p><?php echo htmlspecialchars($mundial_details['Cantante'] ?? 'No registrado'); ?></p>
            </div>
        </div>

        <!-- Sección de Publicaciones -->
        <section class="infografia" id="infografia" style="padding-top: 2rem;">
            <div class="cards-grid">
                <!--
                    AQUÍ DEBES CARGAR DINÁMICAMENTE LAS PUBLICACIONES DE ESTE MUNDIAL.
                    La consulta SQL sería algo como:
                    SELECT * FROM V_Publicaciones WHERE ID_Mundial = ?
                -->
                <a class="card-link" href="comentarios_publi.php">
                    <article class="card publication-card">
                        <div class="publication-card-media">
                            <img alt="Foto de la publicación" src="../css/PlaceHolder3.png"/>
                        </div>
                        <div class="publication-card-content">
                            <h3>Publicación de Ejemplo</h3>
                            <p>Este es un ejemplo de una publicación relacionada con el mundial de <?php echo htmlspecialchars($mundial_details['Anio']); ?>.</p>
                        </div>
                    </article>
                </a>
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