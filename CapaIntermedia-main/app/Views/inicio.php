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
.header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.header-center{flex:1;display:flex;justify-content:center}
.header-search{display:flex;gap:8px;align-items:center}
.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
.header-profile-link{display:inline-block;text-decoration:none}
</style>
<style data-injected="publication-card-fix">
.publication-card-media {position: relative;width: 100%;aspect-ratio: 16 / 9;background-color: #e0e0e0;overflow: hidden;}
.publication-card-media img,.publication-card-media video {width: 100%;height: 100%;object-fit: cover;}
</style>
</head>
<body>
<header class="header">
<div class="header-content">
<div class="logo-container">
<div class="logo"><i class="fas fa-futbol"></i></div>
<div><h1>GolNet </h1></div>
</div><div class="header-center"><form action="buscar.php" class="header-search" method="GET"><input name="q" placeholder="Buscar..." type="search"/><button type="submit">Buscar</button></form></div>
<?php if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])): ?>
<div class="countdown">
<a class="header-logout-icon-link" href="cerrar_sesion.php" title="Cerrar Sesión"><i class="fa-solid fa-right-from-bracket"></i></a>
<a class="header-profile-link" href="mis_publicaciones.php"><div class="header-profile-mini"><img alt="Foto de perfil" src="<?php echo $photoSrc; ?>"/><span class="name"><?php echo htmlspecialchars($displayName); ?></span></div></a>
</div>
<?php endif; ?>
</div>
<button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
</header>
<div class="container">
<aside class="sidebar left-sidebar" id="leftSidebar">
<div class="user-profile" style="display:none">
<div class="user-avatar"><i class="fas fa-user"></i></div>
<div class="user-name">Luis Venegas</div>
<div class="user-country"><i class="fas fa-flag"></i><span>Mexico</span></div>
</div>
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
<main class="main-content">
<h2>La Copa Mundial FIFA </h2>
<div class="filter-container">
<span>Ordenar por:</span>
<a href="inicio.php?sort=recent" class="filter-btn <?php echo ($sort_by === 'recent') ? 'active' : ''; ?>">Recientes</a>
<a href="inicio.php?sort=likes" class="filter-btn <?php echo ($sort_by === 'likes') ? 'active' : ''; ?>">Más gustados</a>
<a href="inicio.php?sort=comments" class="filter-btn <?php echo ($sort_by === 'comments') ? 'active' : ''; ?>">Más comentados</a>
</div>
<section class="infografia" id="infografia">
<div class="cards-grid">
<?php if (!empty($publications)): ?>
<?php foreach($publications as $row): 
    $idPubli = htmlspecialchars($row['ID_Publi']);
    $titulo = htmlspecialchars($row['Titulo']);
    $descripcionPlano = trim(preg_replace('/\s+/', ' ', strip_tags($row['Descripcion'])));
    if (function_exists('mb_substr')) {
        $descripcionCorta = mb_substr($descripcionPlano, 0, 80);
        $descLen = function_exists('mb_strlen') ? mb_strlen($descripcionPlano) : strlen($descripcionPlano);
    } else {
        $descripcionCorta = substr($descripcionPlano, 0, 80);
        $descLen = strlen($descripcionPlano);
    }
    $descripcionCorta = htmlspecialchars($descripcionCorta . ($descLen > 80 ? '...' : ''), ENT_QUOTES, 'UTF-8'); 
?>
<a class="card-link" href="comentarios_publi.php?id=<?php echo $idPubli; ?>">
<article class="card publication-card">
<div class="publication-card-media">
<?php if (!empty($row['Multimedia']) && !empty($row['TipoMultimedia'])): ?>
<?php
$media_type = $row['TipoMultimedia'];
$media_src = 'data:' . $media_type . ';base64,' . base64_encode($row['Multimedia']);
?>
<?php if (strpos($media_type, 'image/') === 0): ?>
<img alt="<?php echo $titulo; ?>" src="<?php echo $media_src; ?>"/>
<?php elseif (strpos($media_type, 'video/') === 0): ?>
<video muted loop><source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>"></video>
<?php endif; ?>
<?php else: ?>
<img alt="Sin multimedia" src="../css/PlaceHolder3.jpg"/>
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
<p style='grid-column: 1 / -1; text-align: center; padding: 20px;'>No hay publicaciones disponibles en este momento.</p>
<?php endif; ?>
</div>
</section>
</main>
</div>
<script src="../javascript/inicio.js"></script>
</body>
</html>
