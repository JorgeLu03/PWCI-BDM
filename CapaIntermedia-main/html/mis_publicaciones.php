<?php
session_start();

// --- Restringir acceso a usuarios no logueados ---
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    // Si el usuario no ha iniciado sesión, redirigirlo a la página de login.
    header('Location: Iniciar_sesion.php');
    exit(); // Detener la ejecución del script después de redirigir.
}
require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];

// --- Lógica para obtener las publicaciones del usuario ---
$user_id = $_SESSION['user_id'] ?? 0;
$user_publications = [];

if ($user_id > 0) {
    // Preparamos una consulta para obtener las publicaciones del usuario logueado
    $stmt = $conn->prepare(
        "SELECT ID_Publi, Titulo, Descripcion, Fec_pub, Multimedia, TipoMultimedia, Views, LikeCount, CommentCount, Estatus, MotivoRechazo
         FROM V_Publicaciones 
         WHERE ID_User = ? 
         ORDER BY Fec_pub DESC"
    );
    if ($stmt) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_publications = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>
<!DOCTYPE html>

<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Copa Mundial FIFA </title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
</style>
<style data-injected="status-light-styles">
    .status-indicator {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.9rem;
        font-weight: 500;
    }
    .status-light {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }
    .status-pending { background-color: #ffc107; } /* Amarillo */
    .status-approved { background-color: #28a745; } /* Verde */
    .status-rejected { background-color: #dc3545; } /* Rojo */

    .rejection-reason {
        background-color: #fff3f3;
        color: #5b1218;
        border-left: 5px solid #dc3545;
        border-radius: 8px;
        padding: 0.75rem 1.25rem;
        margin-top: 1rem;
        font-size: 0.95rem;
    }
    .rejection-reason strong {
        color: #842029;
    }
    .user-list-item {
        display: flex;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #eee;
    }
    .user-list-item:last-child { border-bottom: none; }
    .user-list-item img {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 12px;
        object-fit: cover;
    }
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
<!-- <div class="motto">Uniendo al mundo a través del fútbol</div> -->
</div>
</div><div class="header-center"><form action="buscar.php" class="header-search" method="GET"><input name="q" placeholder="Buscar..." type="search"/><button type="submit">Buscar</button></form></div>
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
<!-- <h2><i class="fas fa-tachometer-alt"></i> Mi Mundial</h2> -->
<ul>
<li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
<li><a href="mis_publicaciones.php"><i class="fa-solid fa-image"></i> <span>Mis Publicaciones</span></a></li>
<li><a href="editar_perfil.php"><i class="fas fa-cog"></i> <span>Configuración</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
<h2>Mis Publicaciones </h2>
<?php if (count($user_publications) > 0): ?>
    <?php foreach ($user_publications as $pub): ?>
        <div class="worldcup-container">
            <div class="worldcup-info">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                    <h3><?php echo htmlspecialchars($pub['Titulo']); ?></h3>
                    <?php
                        $status_class = '';
                        $status_text = '';
                        if ($pub['Estatus'] == 1) {
                            $status_class = 'status-pending';
                            $status_text = 'Pendiente';
                        } elseif ($pub['Estatus'] == 2) {
                            $status_class = 'status-approved';
                            $status_text = 'Aprobada';
                        } elseif ($pub['Estatus'] == 3) {
                            $status_class = 'status-rejected';
                            $status_text = 'Rechazada';
                        }
                    ?>
                    <div class="status-indicator">
                        <span class="status-light <?php echo $status_class; ?>"></span>
                        <span><?php echo $status_text; ?></span>
                    </div>
                </div>
                <div class="post-meta">
                    <span class="user-publish"><?php echo htmlspecialchars($displayName); ?></span>
                    <span class="separator">|</span>
                    <span class="user-publish"><?php echo date("d M, Y", strtotime($pub['Fec_pub'])); ?></span>
                </div>
                <?php if ($pub['Estatus'] == 3 && !empty($pub['MotivoRechazo'])): ?>
                    <div class="rejection-reason">
                        <strong><i class="fas fa-exclamation-circle"></i> Motivo del rechazo:</strong> <?php echo htmlspecialchars($pub['MotivoRechazo']); ?>
                    </div>
                <?php endif; ?>
                <div class="publication-description"><?php echo nl2br($pub['Descripcion']); ?></div>
                
                <?php if (!empty($pub['Multimedia']) && !empty($pub['TipoMultimedia'])): ?>
                    <div class="media-container">
                        <?php
                            $media_type = $pub['TipoMultimedia'];
                            $media_src = 'data:' . $media_type . ';base64,' . base64_encode($pub['Multimedia']);
                        ?>
                        <?php if (strpos($media_type, 'image/') === 0): ?>
                            <img alt="Multimedia de la publicación" class="publish-media" src="<?php echo $media_src; ?>"/>
                        <?php elseif (strpos($media_type, 'video/') === 0): ?>
                            <video class="publish-media" controls loop>
                                <source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>">
                            </video>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="post-actions">
                <div class="action-buttons-group">
                    <a href="comentarios_publi.php?id=<?php echo $pub['ID_Publi']; ?>" class="action-btn comment-btn">
                        <i class="fas fa-comment"></i> Ver Comentarios
                    </a>
                    <?php if ($pub['Estatus'] == 3): ?>
                        <a href="editar_publicacion.php?id=<?php echo $pub['ID_Publi']; ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit"></i> Editar y Reenviar
                        </a>
                    <?php endif; ?>
                </div>
                <div class="stat-item">
                    <i class="fas fa-eye"></i> <?php echo htmlspecialchars($pub['Views']); ?> Vistas
                </div>
            </div>
            <div class="post-stats">
                <h4 class="stats-title">Estadísticas</h4>
                <div class="stats-list">
                    <div class="stat-item"><strong>Vistas:</strong> <?php echo htmlspecialchars($pub['Views']); ?></div>
                    <!-- El conteo de "Me gusta" ahora viene de la vista V_Publicaciones -->
                    <div class="stat-item"><strong>Me gusta:</strong> <a href="#" class="stat-link" data-action="likers" data-publi-id="<?php echo $pub['ID_Publi']; ?>"><?php echo htmlspecialchars($pub['LikeCount']); ?> usuarios</a></div>
                    <div class="stat-item"><strong>Comentarios:</strong> <a href="#" class="stat-link" data-action="commenters" data-publi-id="<?php echo $pub['ID_Publi']; ?>"><?php echo htmlspecialchars($pub['CommentCount']); ?> comentarios</a></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align: center; padding: 2rem;">Aún no has creado ninguna publicación. <a href="crear_publicacion.php">¡Crea la primera!</a></p>
<?php endif; ?>
</main>

</div>
<script src="../javascript/inicio.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const publiId = this.dataset.publiId;
            const action = this.dataset.action;
            const handler = action === 'likers' ? 'get_likers_handler.php' : 'get_commenters_handler.php';
            const title = action === 'likers' ? 'Personas a las que les gusta' : 'Personas que comentaron';

            fetch(`${handler}?publi_id=${publiId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        let userListHTML = '<div style="text-align: left; max-height: 400px; overflow-y: auto;">';
                        if (data.users.length > 0) {
                            data.users.forEach(user => {
                                const photoSrc = user.Foto 
                                    ? 'data:image/jpeg;base64,' + user.Foto 
                                    : '../css/user-default.png';
                                userListHTML += `
                                    <div class="user-list-item">
                                        <img src="${photoSrc}" alt="Foto de ${escapeHTML(user.Nombre)}">
                                        <span>${escapeHTML(user.Nombre)}</span>
                                    </div>
                                `;
                            });
                        } else {
                            userListHTML += '<p style="text-align: center; padding: 20px 0;">Nadie ha interactuado aún.</p>';
                        }
                        userListHTML += '</div>';

                        Swal.fire({
                            title: `<strong>${title}</strong>`,
                            html: userListHTML,
                            showCloseButton: true,
                            showConfirmButton: false,
                            focusConfirm: false
                        });
                    } else {
                        Swal.fire('Error', data.error || 'No se pudo obtener la lista de usuarios.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error en la petición fetch:', error);
                    Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
                });
        });
    });

    function escapeHTML(str) {
        if (typeof str !== 'string') return '';
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }
});
</script>
</body>
</html>