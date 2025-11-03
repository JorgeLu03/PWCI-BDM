<?php
session_start();

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

// --- Lógica para obtener detalles del usuario logueado ---
$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

// --- Lógica para obtener la categoría ---
$category_id = $_GET['id'] ?? 0;
$category_details = null;

if ($category_id > 0) {
    // Usamos un procedimiento almacenado para obtener los detalles de la categoría por su ID
    $stmt = $conn->prepare("CALL SP_GetCategoryByID(?)");
    if ($stmt) {
        $stmt->bind_param('i', $category_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $category_details = $result->fetch_assoc();
        }
        $stmt->close();
    }
}

// Si no se encuentra la categoría, podrías redirigir o mostrar un error 404.
if ($category_details === null) {
    // header("Location: 404.php"); // Opcional: redirigir a una página de error
    die("Categoría no encontrada."); // O mostrar un mensaje simple
}

// --- Lógica para la imagen de la categoría ---
$categoryImageSrc = '../css/PlaceHolder3.png'; // Imagen por defecto
if (!empty($category_details['Imagen'])) {
    // Convertir los datos BLOB a una Data URI para mostrar la imagen
    $categoryImageSrc = 'data:image/jpeg;base64,' . base64_encode($category_details['Imagen']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($category_details['Nombre']); ?> - GolNet</title>
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
<style>
    .category-header {
        border-radius: 12px;
        min-height: 250px; /* Altura mínima para que el fondo se vea bien */
        margin-bottom: 1.5rem; /* Espacio entre el banner y el texto */
        overflow: hidden; /* Para que la imagen respete los bordes redondeados */
        position: relative; /* Para posicionar la imagen */
    }
    .category-header img {
        width: 100%;
        height: 100%;
        object-fit: cover; /* La imagen cubre el contenedor sin deformarse */
        position: absolute;
    }

    .category-info {
        text-align: center;
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 12px;
        margin-bottom: 2rem;
        border: 1px solid #e9ecef;
    }

    .category-info h2 {
        color: var(--primary-color);
        margin: 0 0 0.5rem 0;
        font-size: 2.5rem;
    }

    .category-info p {
        font-size: 1.1rem;
        opacity: 0.9;
        max-width: 800px;
        margin: 0 auto;
    }
</style>
</head>
<body>
<!-- Barra superior (Header) - Es idéntico al de inicio.php -->
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
    <!-- Barra lateral (Sidebar) - Es idéntica a la de inicio.php -->
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
        <!-- Cabecera de la Categoría -->
        <div class="category-header">
            <img src="<?php echo $categoryImageSrc; ?>" alt="Banner de la categoría <?php echo htmlspecialchars($category_details['Nombre']); ?>">
        </div>
        <div class="category-info">
             <h2><?php echo htmlspecialchars($category_details['Nombre']); ?></h2>
             <p><?php echo htmlspecialchars($category_details['Descripcion']); ?></p>
        </div>

        <!-- Filtros (igual que en inicio.php) -->
        <div class="filter-container">
            <span>Ordenar por:</span>
            <button class="filter-btn active" data-sort="chronological">Recientes</button>
            <button class="filter-btn" data-sort="likes">Más gustados</button>
            <button class="filter-btn" data-sort="comments">Más comentados</button>
        </div>

        <!-- Grid de Publicaciones -->
        <section class="infografia" id="infografia">
            <div class="cards-grid">
                <!--
                    AQUÍ DEBES CARGAR DINÁMICAMENTE LAS PUBLICACIONES DE ESTA CATEGORÍA DESDE LA BASE DE DATOS.
                    La consulta SQL sería algo como:
                    SELECT * FROM Publicaciones WHERE id_categoria = (SELECT id FROM Categorias WHERE slug = ?)
                -->
                <p style="text-align:center; width:100%;">Aquí se mostrarán las publicaciones de "<?php echo htmlspecialchars($category_details['Nombre']); ?>".</p>
                <!-- Ejemplo de tarjeta -->
                <a class="card-link" data-comments="25" data-date="2024-05-21T10:00:00Z" data-likes="150" href="comentarios_publi.php">
                    <article class="card publication-card">
                        <div class="publication-card-media"><img alt="Foto" src="../css/PlaceHolder3.png"/></div>
                        <div class="publication-card-content"><h3>Publicación de Ejemplo en Categoría</h3><p>Contenido de la publicación...</p></div>
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