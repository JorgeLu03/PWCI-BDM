<?php
session_start();

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
?>
<!DOCTYPE html>

<html lang="es">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title>Copa Mundial FIFA </title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>
<link href="../css/inicio.css" rel="stylesheet"/>
<link href="../css/editar.css" rel="stylesheet"/>
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
<li><a href="mis_publicaciones.php"><i class="fa-solid fa-image"></i> <span>Mis
                            Publicaciones</span></a></li>
<li><a href="editar_perfil.php"><i class="fas fa-cog"></i> <span>Configuración</span></a></li>
</ul>
</aside>
<!-- Contenido principal - Información del Mundial -->
<main class="main-content">
<h2>Configuracion </h2>
<div class="contenedor_principal">
<!-- Formulario -->
<section class="login">
<form action="" class="form" method="POST">
<h3>Editar Cuenta</h3>
<div class="profile-pic-section">
    <div class="profile-pic-container">
        <img src="<?php echo $photoSrc; ?>" alt="Foto de perfil" id="imagePreview">
        <label for="profilePhoto" class="profile-pic-edit">
            <i class="fas fa-camera"></i>
        </label>
        <input accept="image/*" id="profilePhoto" style="display: none;" type="file"/>
    </div>
</div>
<div class="form_container">
<!-- Campos del formulario -->
<!-- Nombre Completo -->
<div class="form_gruop">
<label>Nombre Completo:</label>
<input class="form_input" id="USuariotxt" name="USuariotxt" placeholder="Nombre Completo" required="" type="text"/>
<span class="form_line"></span>
</div>
<!-- Fecha de Nacimiento -->
<div class="form_gruop">
<label>Fecha de Nacimiento:</label>
<input class="form_input" id="USuariotxt" name="USuariotxt" placeholder="Fecha de Nacimiento" required="" type="date"/>
<span class="form_line"></span>
</div>
<!-- Genero -->
<div class="form_gruop">
<label>Genero:</label>
<input class="form_input" list="genero" placeholder="Escribe o selecciona"/>
<datalist id="genero">
<option value="Masculino">
<option value="Femenino">
<option value="Otro">
</option></option></option></datalist>
<span class="form_line"></span>
</div>
<!-- Pais de Nacimiento -->
<div class="form_gruop">
<label>País de Nacimiento:</label>
<input class="form_input" list="paises" placeholder="Escribe o selecciona"/>
<datalist id="paises">
<option value="Argentina">
<option value="Brasil">
<option value="Canadá">
<option value="Chile">
<option value="Colombia">
<option value="Estados Unidos">
<option value="México">
<option value="España">
</option></option></option></option></option></option></option></option></datalist>
<span class="form_line"></span>
</div>
<!-- Nacionalidad -->
<div class="form_gruop">
<label>Nacionalidad:</label>
<input class="form_input" list="nacionalidad" placeholder="Escribe o selecciona"/>
<datalist id="nacionalidad">
<option value="Argentina">
<option value="Brasil">
<option value="Canadá">
<option value="Chile">
<option value="Colombia">
<option value="Estados Unidos">
<option value="México">
<option value="España">
</option></option></option></option></option></option></option></option></datalist>
<span class="form_line"></span>
</div>
<!-- Correo Electronico -->
<div class="form_gruop">
<label>Correo:</label>
<input class="form_input" id="USuariotxt" name="USuariotxt" placeholder="Correo electronico" required="" type="text"/>
<span class="form_line"></span>
</div>
<!-- Telefono -->
<div class="form_gruop">
<label>Teléfono:</label>
<input class="form_input" id="telefonotxt" name="telefonotxt" placeholder="Teléfono" type="tel"/>
<span class="form_line"></span>
</div>
<!-- Contraseña -->
<div class="form_gruop">
<label>Contraseña:</label>
<input class="form_input" id="Contraseñatxt" name="Contraseñatxt" placeholder="Contraseña" required="" type="password"/>
<span class="form_line"></span>
</div>
<input class="form_submit full-width" onclick="window.location.href='Iniciar_sesion.html'" type="button" value="Guardar Cambios"/>
</div>
</form>
</section>
</div>
</main>
</div>
<script src="../javascript/inicio.js"></script>
<script src="../javascript/edit_perfil.js"></script>
</body>
</html>