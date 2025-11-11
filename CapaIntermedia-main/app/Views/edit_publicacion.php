<?php /* Vista: Editar Publicación (MVC) */ ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Publicación - Mundial 2026</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/inicio.css">
    <link rel="stylesheet" href="../css/new_publi.css">
    <style data-injected="header-perfil">.header-profile-mini{display:inline-flex;align-items:center;gap:10px;padding:6px 10px;background:rgba(255,255,255,0.08);border-radius:999px;border:1px solid rgba(255,255,255,.15)}.header-profile-mini img{width:28px;height:28px;border-radius:50%;object-fit:cover;border:1px solid rgba(0,0,0,.2)}.header-profile-mini .name{font-weight:600}</style>
    <style data-injected="header-search">.header-content,.countdown{display:flex;gap:12px;align-items:center;flex-wrap:wrap}.header-search{display:flex;gap:8px;align-items:center}.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:180px}.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}.header-profile-link{text-decoration:none}</style>
    <style data-injected="centered-search">.header-content{display:flex;align-items:center;gap:12px;flex-wrap:wrap}.header-center{flex:1;display:flex;justify-content:center}.header-search{display:flex;gap:8px;align-items:center}.header-search input[type="search"]{padding:6px 10px;border-radius:999px;border:1px solid rgba(0,0,0,.15);min-width:220px}.header-search button{padding:6px 12px;border-radius:999px;border:1px solid rgba(0,0,0,.15);background:#fff;cursor:pointer}.header-profile-link{display:inline-block;text-decoration:none}</style>
    <script src="https://cdn.ckeditor.com/ckeditor5/41.0.0/classic/ckeditor.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<header class="header">
    <div class="header-content">
        <div class="logo-container"><div class="logo"><i class="fas fa-futbol"></i></div><div><h1>GolNet</h1></div></div>
        <div class="header-center">
            <form action="buscar.php" class="header-search" method="GET">
                <input name="q" placeholder="Buscar..." type="search"/>
                <button type="submit">Buscar</button>
            </form>
        </div>
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
    <ul>
        <li><a href="inicio.php"><i class="fas fa-home"></i> <span>Inicio</span></a></li>
        <li><a href="mis_publicaciones.php"><i class="fa-solid fa-user"></i> <span>Perfil</span></a></li>
        <?php if (!isset($_SESSION['user_id'])): ?><li><a href="Iniciar_sesion.php"><i class="fa-solid fa-right-to-bracket"></i> <span>Iniciar Sesión</span></a></li><?php endif; ?>
        <?php if ($userType === 0): ?><li><a href="administrar_publis.php"><i class="fa-solid fa-user-tie"></i> <span>Administrar</span></a></li><?php endif; ?>
        <li><a href="mundiales.php"><i class="fas fa-trophy"></i> <span>Mundiales</span></a></li>
        <li><a href="categorías.php"><i class="fa-solid fa-tags"></i> <span>Categorías</span></a></li>
        <li><a href="javascript:history.back()" onclick="return true;"><i class="fas fa-undo"></i><span>Volver Atrás</span></a></li>
    </ul>
</aside>
<main class="main-content">
    <div class="publicacion-container">
        <div class="publicacion-header">
            <h2><i class="fas fa-edit"></i> Editar Publicación</h2>
            <p>Corrige tu publicación y reenvíala para su aprobación.</p>
        </div>
        <form class="publicacion-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo"><i class="fas fa-heading"></i> Título de la publicación</label>
                <input type="text" id="titulo" name="Titulo" class="form-input" required value="<?php echo htmlspecialchars($pub_data['Titulo']); ?>" placeholder="Ej: Argentina gana el partido contra Brasil">
            </div>
            <div class="form-group">
                <label for="contenido"><i class="fas fa-align-left"></i> Contenido</label>
                <textarea id="contenido" name="Descripcion" class="form-textarea" required placeholder="Escribe el contenido de tu publicación..."><?php echo $pub_data['Descripcion']; ?></textarea>
            </div>
            <div class="form-group">
                <label><i class="fas fa-tag"></i> Categoría</label>
                <div class="categoria-tags">
                    <select name="ID_categ" class="categoria-tag" required>
                        <option value="">-- Selecciona la categoria --</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['ID']); ?>" <?php echo ($cat['ID'] == $pub_data['ID_Categ']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($cat['Nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-hashtag"></i> Etiquetas del Mundial</label>
                <div class="categoria-tags">
                    <select name="ID_Mundial" class="categoria-tag" required>
                        <option value="">-- Selecciona el año del mundial --</option>
                        <?php foreach ($mundiales as $mun): ?>
                            <option value="<?php echo htmlspecialchars($mun['ID']); ?>" <?php echo ($mun['ID'] == $pub_data['ID_Mundial']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($mun['Nombre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label><i class="fas fa-image"></i> Multimedia (Opcional: selecciona un nuevo archivo)</label>
                <div class="file-upload" id="uploadArea">
                    <button type="button" class="btn btn-upload" onclick="document.getElementById('mediaFile').click()"><i class="fa-solid fa-photo-film"></i> Cambiar archivo</button>
                    <input type="file" id="mediaFile" name="Multimedia" class="file-input" accept="image/*,video/*">
                    <div class="media-preview" id="mediaPreview">
                        <?php if (!empty($pub_data['Multimedia'])): ?>
                            <p>Multimedia actual:</p>
                            <?php
                                $media_type = $pub_data['TipoMultimedia'];
                                $media_src = 'data:' . $media_type . ';base64,' . base64_encode($pub_data['Multimedia']);
                            ?>
                            <?php if (strpos($media_type, 'image/') === 0): ?>
                                <img src="<?php echo $media_src; ?>" style="max-width:200px;border-radius:8px;">
                            <?php elseif (strpos($media_type, 'video/') === 0): ?>
                                <video src="<?php echo $media_src; ?>" style="max-width:200px;border-radius:8px;" controls></video>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="form-buttons">
                <button type="button" class="btn btn-secondary" onclick="window.location.href='mis_publicaciones.php'"><i class="fas fa-times"></i> Cancelar</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Guardar y Reenviar</button>
            </div>
        </form>
    </div>
    <?php if (!empty($error_message_local)): ?>
    <script>Swal.fire({icon:'error',title:'❌ Error al Actualizar',text:<?php echo json_encode($error_message_local); ?>,confirmButtonColor:'#d33'});</script>
    <?php endif; ?>
</main>
</div>
<script>
 document.addEventListener('DOMContentLoaded', function(){
  const textarea = document.querySelector('#contenido');
  if(!textarea) return;
  ClassicEditor.create(textarea,{toolbar:{items:['heading','|','bold','italic','link','bulletedList','numberedList','|','undo','redo']}})
   .then(editor=>{editor.model.document.on('change:data',()=>{textarea.value = editor.getData();});})
   .catch(err=>console.error('CKEditor error',err));
 });
</script>
<script src="../javascript/inicio.js"></script>
</body>
</html>