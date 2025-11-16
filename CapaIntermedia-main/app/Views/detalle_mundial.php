<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?php echo htmlspecialchars($mundial_details['Nombre']); ?> - GolNet</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="../css/inicio.css" rel="stylesheet"/>
<link href="../css/detalle_mundial.css" rel="stylesheet"/>
<style>
.main-content {
    background-color: #ffffff !important;
}
.main-content::before { display: none !important; }
</style>
<style data-injected="header-perfil">
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
            <div class="logo"><i class="fas fa-futbol"></i></div>
            <div><h1>GolNet</h1></div>
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
    <button class="menu-toggle" id="menuToggle"><i class="fas fa-bars"></i></button>
</header>

<!-- Contenedor principal -->
<div class="container">
    <!-- Barra lateral -->
    <aside class="sidebar left-sidebar" id="leftSidebar">
        <ul>
            <li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
            <li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
            <li><a href="iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
            <?php if (isset($userType) && $userType === 0): ?>
            <li><a href="administrar_publicaciones.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
            <?php endif; ?>
            <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
            <li><a href="categorias.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
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

        <!-- Filtros y Sección de Publicaciones -->
        <div class="filter-container" style="margin-top: 2rem;">
            <span>Ordenar publicaciones por:</span>
            <a href="detalle_mundial.php?id=<?php echo $mundial_id; ?>&sort=recent" class="filter-btn <?php echo ($sort_by === 'recent') ? 'active' : ''; ?>">Recientes</a>
            <a href="detalle_mundial.php?id=<?php echo $mundial_id; ?>&sort=likes" class="filter-btn <?php echo ($sort_by === 'likes') ? 'active' : ''; ?>">Más gustados</a>
            <a href="detalle_mundial.php?id=<?php echo $mundial_id; ?>&sort=comments" class="filter-btn <?php echo ($sort_by === 'comments') ? 'active' : ''; ?>">Más comentados</a>
        </div>

        <section class="infografia" id="infografia" style="padding-top: 2rem;">
            <div class="cards-grid">
                <?php if (count($publications) > 0): ?>
                    <?php foreach ($publications as $pub): ?>
                        <?php
                            $idPubli = htmlspecialchars($pub['ID_Publi']);
                            $titulo = htmlspecialchars($pub['Titulo']);
                            // Decodificar entidades HTML
                            $descDecodificado = html_entity_decode($pub['Descripcion'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            // Convertir etiquetas de bloque en saltos de línea
                            $descConSaltos = preg_replace('/<\/(p|div|h[1-6]|li|br)>/i', "\n", $descDecodificado);
                            $descConSaltos = preg_replace('/<br\s*\/?>/i', "\n", $descConSaltos);
                            // Quitar etiquetas y normalizar espacios
                            $descripcionPlano = trim(preg_replace('/\s+/', ' ', strip_tags($descConSaltos)));
                            if (function_exists('mb_substr')) {
                                $descripcionCorta = mb_substr($descripcionPlano, 0, 80);
                                $descLen = function_exists('mb_strlen') ? mb_strlen($descripcionPlano) : strlen($descripcionPlano);
                            } else {
                                $descripcionCorta = substr($descripcionPlano, 0, 80);
                                $descLen = strlen($descripcionPlano);
                            }
                            $descripcionCorta = htmlspecialchars($descripcionCorta . ($descLen > 80 ? '...' : ''), ENT_QUOTES, 'UTF-8');
                        ?>
                        <a class="card-link" href="comentarios_publicacion.php?id=<?php echo $idPubli; ?>">
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
                    <p style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                        No hay publicaciones sobre este mundial por el momento.
                    </p>
                <?php endif; ?>
            </div>
        </section>
    </main>
</div>

<script src="../javascript/inicio.js"></script>

</body>
</html>
