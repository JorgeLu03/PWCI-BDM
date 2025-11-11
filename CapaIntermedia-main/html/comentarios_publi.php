<?php
session_start();

// Conexión a la base de datos
require_once '../BD/Connection/Connection.php';

require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

// --- Lógica para obtener la publicación ---
$publi_id = $_GET['id'] ?? 0;
$publication = null;
$like_count = 0;
$user_has_liked = false;
$current_user_id = $_SESSION['user_id'] ?? 0;
$comments = []; // Array para guardar los comentarios

if ($publi_id > 0) {
    // --- Incrementar el contador de vistas ---
    // Se llama al procedimiento almacenado que ya tienes: ACTUALIZAR_VISTAS.
    $stmt_views = $conn->prepare("CALL ACTUALIZAR_VISTAS(?)");
    if ($stmt_views) {
        $stmt_views->bind_param('i', $publi_id);
        $stmt_views->execute();
        $stmt_views->close();
        // Limpiar resultados para la siguiente consulta
        while ($conn->more_results() && $conn->next_result()) {;}
    }

    // Usamos un procedimiento almacenado para obtener los detalles de la publicación. Asegúrate que devuelve TipoMultimedia.
    $stmt = $conn->prepare("CALL Mostrar_Publicacion_Especifica(?)"); // Esta consulta se ejecuta después de actualizar la vista.
    if ($stmt) {
        $stmt->bind_param('i', $publi_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $publication = $result->fetch_assoc();
        }
        $stmt->close();
        while ($conn->more_results() && $conn->next_result()) {;}
    }

    // --- Obtener conteo de "Me gusta" y si el usuario actual le dio "Me gusta" ---
    $stmt_likes = $conn->prepare("CALL SP_GetLikeCount(?)");
    if ($stmt_likes) {
        $stmt_likes->bind_param('i', $publi_id);
        $stmt_likes->execute();
        $like_count = $stmt_likes->get_result()->fetch_assoc()['like_count'];
        $stmt_likes->close();
        while ($conn->more_results() && $conn->next_result()) {;}
    }
    if ($current_user_id > 0) {
        $stmt_check = $conn->prepare("CALL SP_CheckUserLike(?, ?)");
        $stmt_check->bind_param('ii', $current_user_id, $publi_id);
        $stmt_check->execute();
        $user_has_liked = $stmt_check->get_result()->fetch_assoc()['user_liked'] > 0;
        $stmt_check->close();
    }

    // --- Obtener los comentarios de la publicación ---
    $stmt_comments = $conn->prepare("CALL SP_GetCommentsByPost(?)");
    if ($stmt_comments) {
        $stmt_comments->bind_param('i', $publi_id);
        $stmt_comments->execute();
        $result_comments = $stmt_comments->get_result();
        $comments = $result_comments->fetch_all(MYSQLI_ASSOC);
        $stmt_comments->close();
    }
}

// Si no se encuentra la publicación, redirigir o mostrar un error
if ($publication === null) {
    header("Location: inicio.php");
    exit();
}
?>
<!DOCTYPE html>

<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Copa Mundial FIFA 2026</title>
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
<style data-injected="comment-styles">
    .comment-author {
        display: flex;
        align-items: center;
        gap: 12px; /* Espacio entre la foto y el nombre */
        margin-bottom: 1rem;
    }
    .comment-author img {
        width: 45px; /* Tamaño pequeño */
        height: 45px; /* Mismo tamaño para que sea un círculo perfecto */
        border-radius: 50%; /* Esto hace la imagen circular */
        object-fit: cover; /* Asegura que la imagen llene el círculo sin deformarse */
        border: 2px solid #f0f0f0; /* Un borde sutil opcional */
    }
    .comment-author h3 {
        margin: 0;
        font-size: 1.1rem;
    }
    .comment-author .comment-date {
        margin-left: auto; /* Empuja la fecha hacia la derecha */
        font-size: 0.85rem;
        color: #6c757d;
    }
</style>
<style data-injected="toast-notification">
    .toast-notification {
        position: fixed;
        bottom: 20px;
        left: 50%;
        transform: translateX(-50%);
        background-color: #28a745;
        color: white;
        padding: 12px 20px;
        border-radius: 8px;
        z-index: 1050;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        opacity: 0;
        transition: opacity 0.5s, bottom 0.5s;
    }
    .toast-notification.show {
        opacity: 1;
        bottom: 40px;
    }
    .delete-comment-btn {
        background: none;
        border: none;
        color: #dc3545;
        cursor: pointer;
        font-size: 0.9rem;
        opacity: 0.7;
        transition: opacity 0.2s;
    }
    .delete-comment-btn:hover { opacity: 1; }
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
<ul>
<li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
<li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
<li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
<!-- <li><a href="#"><i class="fas fa-cog"></i> <span>Configuración</span></a></li> -->
<!-- Otros botones -->
<li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
<?php if ($userType === 0): ?>
<li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<?php endif; ?>
<li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
<li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
<!-- Publicacion  -->
<div class="worldcup-container">
<div class="worldcup-info">
<h3><?php echo htmlspecialchars($publication['Titulo']); ?></h3>
<div class="post-meta">
<span class="user-publish"><?php echo htmlspecialchars($publication['Nombre_Usuario']); ?></span>
<span class="separator">|</span>
<span class="user-publish"><?php echo date("d M, Y", strtotime($publication['Fec_pub'])); ?></span>
<span class="separator">|</span>
<span class="user-publish"><?php echo htmlspecialchars($publication['Nombre_Categoria']); ?></span>
<span class="separator">|</span>
<span class="user-publish"><?php echo htmlspecialchars($publication['Nombre_Mundial']); ?></span>
</div>
<div class="publication-description"><?php echo nl2br($publication['Descripcion']); ?></div>
<?php if (!empty($publication['Multimedia']) && !empty($publication['TipoMultimedia'])): ?>
    <div class="media-container">
        <?php
            $media_type = $publication['TipoMultimedia'];
            $media_src = 'data:' . $media_type . ';base64,' . base64_encode($publication['Multimedia']);
        ?>
        <?php if (strpos($media_type, 'image/') === 0): ?>
            <img alt="Multimedia de la publicación" class="publish-media" src="<?php echo $media_src; ?>"/>
        <?php elseif (strpos($media_type, 'video/') === 0): ?>
            <video class="publish-media" controls autoplay loop>
                <source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>">
                Tu navegador no soporta la etiqueta de video.
            </video>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
<div class="post-actions" data-publi-id="<?php echo $publi_id; ?>">
    <button class="action-btn like-btn <?php echo $user_has_liked ? 'liked' : ''; ?>">
        <i class="fas fa-heart"></i> Me gusta
        <span class="like-count"><?php echo $like_count; ?></span>
    </button>
</div>
</div>
<!-- Formulario para nuevo comentario -->
<div class="worldcup-container">
    <div class="worldcup-info comment-form-container">
        <h3><i class="fas fa-comment-dots"></i> Deja un comentario</h3>
        <?php if ($current_user_id > 0): ?>
            <form id="comment-form" data-publi-id="<?php echo $publi_id; ?>">
                <textarea name="content" placeholder="Escribe tu comentario aquí..." required></textarea>
                <button type="submit" class="action-btn">Publicar Comentario</button>
            </form>
        <?php else: ?>
            <p>Debes <a href="Iniciar_sesion.php">iniciar sesión</a> para poder comentar.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Comentarios: -->
<div id="comments-section">
    <?php if (count($comments) > 0): ?>
        <?php foreach ($comments as $comment): ?>
            <div class="worldcup-container" id="comment-card-<?php echo $comment['ID_Coment']; ?>">
                <div class="worldcup-info comment">
                    <div class="comment-author">
                        <img src="<?php echo !empty($comment['FotoUsuario']) ? 'data:image/jpeg;base64,' . base64_encode($comment['FotoUsuario']) : '../css/user-default.png'; ?>" alt="Foto de perfil">
                        <h3><?php echo htmlspecialchars($comment['NombreUsuario']); ?></h3>
                        <span class="comment-date"><?php echo date("d M, Y \a \l\a\s H:i", strtotime($comment['Fec'])); ?></span>
                        <?php if ($userType === 0): ?>
                            <button class="delete-comment-btn" data-comment-id="<?php echo $comment['ID_Coment']; ?>" title="Eliminar comentario">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($comment['Contenido'])); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p id="no-comments-message" style="text-align: center; padding: 2rem;">Aún no hay comentarios. ¡Sé el primero en comentar!</p>
    <?php endif; ?>
</div>
</main>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButton = document.querySelector('.like-btn');
    const commentForm = document.getElementById('comment-form');

    if (likeButton) {
        likeButton.addEventListener('click', function() {
            const postActionsContainer = this.closest('.post-actions');
            const publiId = postActionsContainer.dataset.publiId;
            const likeCountSpan = this.querySelector('.like-count');

            // Preparar los datos para enviar
            const formData = new FormData();
            formData.append('publi_id', publiId);

            // Enviar la petición AJAX
            fetch('like_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar el contador de "Me gusta"
                    likeCountSpan.textContent = data.new_like_count;

                    // Alternar la clase 'liked' para cambiar el estilo del botón
                    if (data.like_status === 'liked') {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                } else {
                    alert('Error: ' + (data.error || 'No se pudo procesar la solicitud. Inicia sesión para dar Me Gusta.'));
                }
            })
            .catch(error => console.error('Error en la petición fetch:', error));
        });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Evitar que la página se recargue

            const publiId = this.dataset.publiId;
            const contentTextarea = this.querySelector('textarea');
            const content = contentTextarea.value.trim();

            if (content === '') {
                alert('Por favor, escribe un comentario.');
                return;
            }

            const formData = new FormData();
            formData.append('publi_id', publiId);
            formData.append('content', content);

            fetch('comment_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Limpiar el textarea
                    contentTextarea.value = '';
                    // Mostrar la notificación "toast"
                    showToast(data.message);
                } else {
                    alert('Error al publicar el comentario: ' + (data.error || 'Ocurrió un problema.'));
                }
            })
            .catch(error => {
                console.error('Error en la petición de comentario:', error);
                alert('Error de conexión al intentar publicar el comentario.');
            });
        });
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);

        // Mostrar el toast
        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        // Ocultar y eliminar el toast después de 3 segundos
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }

    // Función para escapar HTML y prevenir ataques XSS
    function escapeHTML(str) {
        const div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // --- Lógica para eliminar comentarios (solo para admin) ---
    document.querySelectorAll('.delete-comment-btn').forEach(button => {
        button.addEventListener('click', function() {
            const commentId = this.dataset.commentId;

            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará el comentario permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('comment_id', commentId);

                    fetch('delete_comment_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const cardToRemove = document.getElementById('comment-card-' + commentId);
                            if (cardToRemove) {
                                cardToRemove.style.transition = 'opacity 0.5s ease';
                                cardToRemove.style.opacity = '0';
                                setTimeout(() => cardToRemove.remove(), 500);
                            }
                        } else {
                            Swal.fire('Error', data.error || 'No se pudo eliminar el comentario.', 'error');
                        }
                    })
                    .catch(error => {
                        console.error('Error en la petición de eliminación:', error);
                        Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
                    });
                }
            });
        });
    });

});
</script>
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