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
/* Ensure profile bubble stands alone */
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
<!-- <div class="motto">Uniendo al mundo a través del fútbol</div> -->
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
<!-- <li><a href="#"><i class="fas fa-cog"></i> <span>Configuración</span></a></li> -->
<!-- Otros botones -->
<li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
<li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
<li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
    <h2>La Copa Mundial FIFA </h2>
    <div class="filter-container">
        <span>Ordenar por:</span>
        <button class="filter-btn active" data-sort="chronological">Recientes</button>
        <button class="filter-btn" data-sort="likes">Más gustados</button>
        <button class="filter-btn" data-sort="comments">Más comentados</button>
    </div>
    <section class="infografia" id="infografia">
        <div class="cards-grid">
            <!-- Ejemplo de Publicación como Tarjeta -->
            <a class="card-link" data-comments="25" data-date="2024-05-21T10:00:00Z" data-likes="150" href="comentarios_publi.php">
                <article class="card publication-card">
                    <div class="publication-card-media">
                        <img alt="Foto de la publicación" src="../css/PlaceHolder3.png"/>
                    </div>
                    <div class="publication-card-content">
                        <h3>Título Publicación de Ejemplo</h3>
                        <p>El torneo de fútbol más grande del mundo llega a Nort...</p>
                    </div>
                </article>
            </a>
            <!-- Puedes añadir más publicaciones aquí siguiendo la misma estructura -->
            <a class="card-link" data-comments="12" data-date="2024-05-20T18:30:00Z" data-likes="95" href="comentarios_publi.php">
                <article class="card publication-card">
                    <div class="publication-card-media">
                        <img alt="Foto de la publicación" src="../css/PlaceHolder3.png"/>
                    </div>
                    <div class="publication-card-content">
                        <h3>Análisis del Grupo de la Muerte</h3>
                        <p>Un vistazo profundo a los equipos que conforman el...</p>
                    </div>
                </article>
            </a>
            <a class="card-link" data-comments="48" data-date="2024-05-22T09:00:00Z" data-likes="210" href="comentarios_publi.php">
                <article class="card publication-card">
                    <div class="publication-card-media">
                        <img alt="Foto de la publicación" src="../css/PlaceHolder3.png"/>
                    </div>
                    <div class="publication-card-content">
                        <h3>Estadios para el 2026</h3>
                        <p>Descubre los diseños arquitectónicos y las tec...</p>
                    </div>
                </article>
            </a>
        </div>
    </section>
</main>
</div>
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
<!-- <a href="#">Calendario</a>
                    <a href="#">Estadios</a>
                    <a href="#">Entradas</a> -->
</div>
</div>
<div class="footer-section">
<h3>Contacto</h3>
<div class="footer-contact">
<!-- <span><i class="fas fa-map-marker-alt"></i> FIFA Strasse 20, Zúrich, Suiza</span> -->
<!-- Colocar links a portafolios y demas cosas del equipo -->
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