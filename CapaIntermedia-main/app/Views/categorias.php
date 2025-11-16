<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Categorías - GolNet</title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
<style>
.admin-delete-category {
    position: absolute;
    top: 10px;
    right: 10px;
    z-index: 10;
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    transition: all 0.2s ease;
}
.admin-delete-category:hover {
    background: #c82333;
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.6);
}
.admin-delete-category i {
    pointer-events: none;
}
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
<h2 class="section-title"><i class="fa-solid fa-globe"></i> Categorías</h2>
<section class="infografia" id="infografia">
    <div class="cards-grid" style="padding-top: 1rem;">
        <?php if (count($categories) > 0): ?>
            <?php foreach ($categories as $category): ?>
                <div style="position: relative;">
                    <?php if (isset($userType) && $userType === 0): ?>
                        <button class="admin-delete-category" 
                                data-category-id="<?php echo htmlspecialchars($category['ID']); ?>" 
                                data-category-name="<?php echo htmlspecialchars($category['Nombre']); ?>"
                                title="Eliminar categoría">
                            <i class="fas fa-trash"></i>
                        </button>
                    <?php endif; ?>
                    <a class="card-link" href="categoria.php?id=<?php echo htmlspecialchars($category['ID']); ?>">
                        <article class="card">
                            <h3><?php echo htmlspecialchars($category['Nombre']); ?></h3>
                            <p><?php echo htmlspecialchars($category['Descripcion']); ?></p>
                        </article>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="worldcup-container">
                <p style="text-align: center; width: 100%; padding: 2rem;">
                    Aún no se han creado categorías. ¡Sé el primero en añadir una desde el panel de administración!
                </p>
            </div>
        <?php endif; ?>
    </div>
</section>
</main>
</div>

<script src="../javascript/inicio.js"></script>
<script>
// Manejar eliminación de categorías (solo admin)
document.querySelectorAll('.admin-delete-category').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const categoryId = this.dataset.categoryId;
        const categoryName = this.dataset.categoryName;
        
        Swal.fire({
            title: '¿Eliminar categoría?',
            html: `¿Estás seguro de eliminar la categoría <strong>${categoryName}</strong>?<br><small style="color: #dc3545; font-weight: 600;">⚠️ ADVERTENCIA: Se eliminarán también todas las publicaciones asociadas a esta categoría.</small><br><small style="color: #666;">Esta acción no se puede deshacer.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar todo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Mostrar loading
                Swal.fire({
                    title: 'Eliminando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Enviar petición al servidor
                const formData = new FormData();
                formData.append('category_id', categoryId);
                
                fetch('delete_category_handler.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Eliminada!',
                            text: data.message,
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al eliminar la categoría'
                    });
                });
            }
        });
    });
});
</script>

</body>
</html>
