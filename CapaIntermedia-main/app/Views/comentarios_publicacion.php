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
.header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.header-center{flex:1;display:flex;justify-content:center}
.header-search{display:flex;gap:8px;align-items:center}
.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}
.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}
.header-profile-link{display:inline-block;text-decoration:none}
</style>
<style data-injected="comment-styles">
.comment-author {display: flex;align-items: center;gap: 12px;margin-bottom: 1rem;}
.comment-author img {width: 45px;height: 45px;border-radius: 50%;object-fit: cover;border: 2px solid #f0f0f0;}
.comment-author h3 {margin: 0;font-size: 1.1rem;}
.comment-author .comment-date {margin-left: 5px;font-size: 0.85rem;color: #6c757d;}
</style>
<style data-injected="toast-notification">
.toast-notification {position: fixed;bottom: 20px;left: 50%;transform: translateX(-50%);background-color: #28a745;color: white;padding: 12px 20px;border-radius: 8px;z-index: 1050;box-shadow: 0 4px 15px rgba(0,0,0,0.2);opacity: 0;transition: opacity 0.5s, bottom 0.5s;}
.toast-notification.show {opacity: 1;bottom: 40px;}
.admin-delete-comment {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-left: auto;
    color: #dc3545;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    padding: 5px 10px;
    border-radius: 5px;
    transition: all 0.2s;
    z-index: 10;
    position: relative;
}
.admin-delete-comment:hover {
    background-color: #dc3545;
    color: white;
}
.admin-delete-comment i {
    pointer-events: none;
}

</style>
</head>
<body>
<header class="header">
<div class="header-content">
<a href="inicio.php" style="text-decoration: none; color: inherit;">
<div class="logo-container">
<div class="logo"><i class="fas fa-futbol"></i></div>
<div><h1>GolNet </h1></div>
</div>
</a><div class="header-center"><form action="buscar.php" class="header-search" method="GET"><input name="q" placeholder="Buscar..." type="search"/><button type="submit">Buscar</button></form></div>
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
<li><a href="crear_publicacion.php"><i class="fa-solid fa-upload"></i> <span>Publicar</span></a></li>
<?php if (!isset($_SESSION['user_id'])): ?>
<li><a href="iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li>
<?php endif; ?>
<?php if ($userType === 0): ?>
<li><a href="administrar_publicaciones.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li>
<?php endif; ?>
<li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
<li><a href="categorias.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
</ul>
</aside>
<main class="main-content">
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
<video class="publish-media" controls autoplay loop><source src="<?php echo $media_src; ?>" type="<?php echo $media_type; ?>">Tu navegador no soporta la etiqueta de video.</video>
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
<div class="worldcup-container">
<div class="worldcup-info comment-form-container">
<h3><i class="fas fa-comment-dots"></i> Deja un comentario</h3>
<?php if ($current_user_id > 0): ?>
<form id="comment-form" data-publi-id="<?php echo $publi_id; ?>">
<textarea name="content" placeholder="Escribe tu comentario aquí..." required></textarea>
<button type="submit" class="action-btn">Publicar Comentario</button>
</form>
<?php else: ?>
<p style="color: #333; font-size: 1rem; text-align: center; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 5px; margin: 0;">
    <i class="fas fa-info-circle" style="color: #856404; margin-right: 8px;"></i>
    Debes <a href="iniciar_sesion.php" style="color: #0066b3; font-weight: bold; text-decoration: underline;">iniciar sesión</a> para poder comentar.
</p>
<?php endif; ?>
</div>
</div>
<div id="comments-section">
<?php if (count($comments) > 0): ?>
<?php foreach ($comments as $comment): ?>
<div class="worldcup-container" id="comment-card-<?php echo $comment['ID_Coment']; ?>">
<div class="worldcup-info comment">
<div class="comment-author">
<img src="<?php echo !empty($comment['Foto_Usuario']) ? 'data:image/jpeg;base64,' . base64_encode($comment['Foto_Usuario']) : '../css/user-default.png'; ?>" alt="Foto de perfil">
<h3><?php echo htmlspecialchars($comment['Nombre_Usuario']); ?></h3>
<span class="comment-date"><?php echo date("d M, Y \a \l\a\s H:i", strtotime($comment['Fecha_Comentario'])); ?></span>
<?php if ($userType === 0): ?>
<span class="admin-delete-comment" onclick="confirmarEliminacion(<?php echo $comment['ID_Coment']; ?>)" title="Eliminar comentario">
<i class="fas fa-times-circle"></i>
</span>
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
            const formData = new FormData();
            formData.append('publi_id', publiId);

            fetch('like_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    likeCountSpan.textContent = data.new_like_count;
                    if (data.like_status === 'liked') {
                        this.classList.add('liked');
                    } else {
                        this.classList.remove('liked');
                    }
                } else {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Acción no Permitida',
                        text: data.error || 'Debes iniciar sesión para dar like.',
                        confirmButtonColor: '#3085d6'
                    });
                }
            })
            .catch(error => {

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.',
                    confirmButtonColor: '#d33'
                });
            });
        });
    }

    if (commentForm) {
        commentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const publiId = this.dataset.publiId;
            const contentTextarea = this.querySelector('textarea');
            const content = contentTextarea.value.trim();

            if (content === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Comentario Vacío',
                    text: 'Por favor, escribe un comentario antes de publicar.',
                    confirmButtonColor: '#3085d6'
                });
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
                    contentTextarea.value = '';
                    showToast(data.message);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Comentar',
                        text: data.error || 'Ocurrió un problema al publicar el comentario.',
                        confirmButtonColor: '#d33'
                    });
                }
            })
            .catch(error => {

                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Por favor, intenta nuevamente.',
                    confirmButtonColor: '#d33'
                });
            });
        });
    }

    function showToast(message) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => { toast.classList.add('show'); }, 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
});

// Eliminar comentario
function confirmarEliminacion(commentId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción eliminará el comentario permanentemente.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, eliminar',
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
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'El comentario ha sido eliminado exitosamente.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                } else {
                    Swal.fire('Error', data.error || 'No se pudo eliminar el comentario.', 'error');
                }
            })
            .catch(error => {

                Swal.fire('Error', 'No se pudo eliminar el comentario.', 'error');
            });
        }
    });
}
</script>
<script src="../javascript/inicio.js"></script>
</body>
</html>
