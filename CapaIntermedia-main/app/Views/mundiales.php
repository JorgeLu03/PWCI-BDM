<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Mundiales - GolNet</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="../css/inicio.css" rel="stylesheet"/>
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
</head>
<body>
<!-- Barra superior -->
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
<a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión">
<i class="fa-solid fa-right-from-bracket"></i>
</a>
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
<!-- Barra lateral izquierda -->
<aside class="sidebar left-sidebar" id="leftSidebar">
<ul>
<li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
<li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
<li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
<?php if (!isset($_SESSION['user_id'])): ?>
<li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
<?php endif; ?>
<?php if (isset($userType) && $userType === 0): ?>
<li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<?php endif; ?>
<li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
<li><a href="javascript:history.back()" onclick="return true;"><i class="fas fa-undo"></i><span>Volver Atrás</span></a></li>
</ul>
</aside>

<!-- Contenido principal -->
<main class="main-content">
<h2 class="section-title"><i class="fas fa-trophy"></i> Mundiales</h2>
<section class="infografia" id="infografia">
<div class="cards-grid" style="padding-top: 1rem;">
    <?php if (count($mundiales) > 0): ?>
        <?php foreach ($mundiales as $mundial): ?>
            <?php
                // Determinar la fuente de la imagen del logo
                $logoSrc = '../css/PlaceHolder5.png';
                if (!empty($mundial['Logo'])) {
                    $logoSrc = 'data:image/png;base64,' . base64_encode($mundial['Logo']);
                }
            ?>
            <a class="card-link" href="mundial_detalle.php?id=<?php echo htmlspecialchars($mundial['ID']); ?>">
                <article class="card publication-card">
                    <div class="publication-card-media">
                        <img alt="Logo <?php echo htmlspecialchars($mundial['Nombre']); ?>" src="<?php echo $logoSrc; ?>"/>
                    </div>
                    <div class="publication-card-content">
                        <h3><?php echo htmlspecialchars($mundial['Nombre']); ?></h3>
                        <p><?php echo htmlspecialchars(substr($mundial['Descripcion'], 0, 100)) . '...'; ?></p>
                    </div>
                </article>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="text-align: center; width: 100%; padding: 2rem;">
            Aún no se han registrado mundiales. ¡Crea el primero desde el panel de administración!
        </p>
    <?php endif; ?>
</div>
</section>
</main>
</div>

<script src="../javascript/inicio.js"></script>

</body>
</html>
