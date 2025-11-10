<?php
session_start();

require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

$userDetails = getUserDetails($conn);
$displayName = $userDetails['displayName'];
$photoSrc = $userDetails['photoSrc'];
$userType = $userDetails['userType'];

$feedback_message = '';
$feedback_type = ''; // 'success' o 'error'

// --- Lógica para guardar los cambios del perfil ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['save_changes'])) {
    $uid = (int)$_SESSION['user_id'];
    $nombre = $_POST['nombre'] ?? '';
    $fechaNac = $_POST['fecha_nacimiento'] ?? '';
    $genero = $_POST['genero'] ?? '';
    $pais = $_POST['pais'] ?? '';
    $nacionalidad = $_POST['nacionalidad'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $contrasena = $_POST['contrasena'] ?? ''; // Nueva contraseña (puede estar vacía)

    // --- Validación de campos obligatorios ---
    if (empty($nombre) || empty($fechaNac) || empty($genero) || empty($pais) || empty($nacionalidad) || empty($correo) || empty($telefono)) {
        $feedback_message = "Por favor, completa todos los campos obligatorios";
        $feedback_type = 'error';
    } else {
        // Si la validación pasa, continuamos con la lógica de la base de datos.
        
        $foto_data = null;
        if (isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] == 0) {
            $foto_data = file_get_contents($_FILES['profilePhoto']['tmp_name']);
        }

        // Si la contraseña está vacía, la tratamos como NULL para que el SP no la actualice.
        $contrasena_to_db = !empty($contrasena) ? $contrasena : null;

        $stmt = $conn->prepare("CALL SP_ModUser(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            // El 7º parámetro (foto) se enlaza como NULL inicialmente.
            // Si hay una foto, se enviará con send_long_data.
            $null_photo = NULL;
            $stmt->bind_param('isssssbsss', $uid, $correo, $telefono, $contrasena_to_db, $fechaNac, $nombre, $null_photo, $pais, $genero, $nacionalidad);
            
            if ($foto_data !== null) {
                // El 7º '?' (índice 6) es la foto.
                $stmt->send_long_data(6, $foto_data);
            }
            
            $execute_success = $stmt->execute();
            
            $stmt->close();

            // Limpiar cualquier resultado pendiente del procedimiento almacenado.
            while ($conn->more_results() && $conn->next_result()) {;}

            if ($execute_success) {
                $feedback_message = "¡Perfil actualizado con éxito!";
                $feedback_type = 'success';
                // Forzar la recarga de los detalles del usuario para ver los cambios inmediatamente
                $userDetails = getUserDetails($conn);
                $displayName = $userDetails['displayName'];
                $photoSrc = $userDetails['photoSrc'];
                // También forzamos la recarga de los datos del formulario para que todo esté sincronizado
                $userData = []; // Limpiamos los datos antiguos
            } else {
                $feedback_message = "Error al actualizar el perfil: " . $conn->error;
                $feedback_type = 'error';
            }
        } else {
            $feedback_message = "Error al preparar la consulta: " . $conn->error;
            $feedback_type = 'error';
        }
    }
}

// --- Lógica para precargar los datos del usuario ---
$userData = [];
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    // Usamos la vista V_DetallesUser para obtener todos los datos
    $stmt = $conn->prepare("SELECT * FROM V_DetallesUser WHERE ID_User = ?");
    if ($stmt) {
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
        }
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
<link href="../css/inicio.css" rel="stylesheet"/>
<link href="../css/editar.css" rel="stylesheet"/>
<style>
    .feedback-message {
        margin-top: 1.5rem; /* Espacio superior para separarlo del título */
        margin-bottom: 1.5rem; /* Espacio inferior para separarlo del formulario */
        padding: 1rem;
        border-radius: 8px;
        text-align: center;
        font-weight: 500;
    }
    .feedback-message.success {
        background-color: #d1e7dd;
        color: #0f5132;
    }
    .feedback-message.error {
        background-color: #f8d7da;
        color: #842029;
    }
</style>
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
<form class="form" method="POST" enctype="multipart/form-data">
<h3>Editar Cuenta</h3>

<?php if (!empty($feedback_message)): ?>
    <div class="feedback-message <?php echo $feedback_type; ?>">
        <?php echo htmlspecialchars($feedback_message); ?>
    </div>
<?php endif; ?>

<div class="profile-pic-section">
    <div class="profile-pic-container">
        <img src="<?php echo $photoSrc; ?>" alt="Foto de perfil" id="imagePreview">
        <label for="profilePhoto" class="profile-pic-edit">
            <i class="fas fa-camera"></i>
        </label>
        <input accept="image/*" id="profilePhoto" name="profilePhoto" style="display: none;" type="file"/>
    </div>
</div>
<div class="form_container">
<!-- Campos del formulario -->
<!-- Nombre Completo -->
<div class="form_gruop">
    <label for="nombre">Nombre Completo:</label>
    <input class="form_input" id="nombre" name="nombre" placeholder="Nombre Completo" required type="text" value="<?php echo htmlspecialchars($userData['Nombre'] ?? ''); ?>"/>
    <span class="form_line"></span>
</div>
<!-- Fecha de Nacimiento -->
<div class="form_gruop">
    <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
    <input class="form_input" id="fecha_nacimiento" name="fecha_nacimiento" placeholder="Fecha de Nacimiento" required type="date" value="<?php echo htmlspecialchars($userData['Fec_nac'] ?? ''); ?>"/>
    <span class="form_line"></span>
</div>
<!-- Genero -->
<div class="form_gruop">
    <label for="genero_input">Género:</label>
    <input class="form_input" id="genero_input" list="genero" name="genero" placeholder="Escribe o selecciona" value="<?php echo htmlspecialchars($userData['Genero'] ?? ''); ?>"/>
    <datalist id="genero">
        <option value="Masculino"></option>
        <option value="Femenino"></option>
        <option value="Otro"></option>
    </datalist>
    <span class="form_line"></span>
</div>
<!-- Pais de Nacimiento -->
<div class="form_gruop">
    <label for="pais_input">País de Nacimiento:</label>
    <input class="form_input" id="pais_input" list="paises" name="pais" placeholder="Escribe o selecciona" value="<?php echo htmlspecialchars($userData['Pais_de_nac'] ?? ''); ?>"/>
    <datalist id="paises">
        <option value="Argentina"></option>
        <option value="Brasil"></option>
        <option value="Canadá"></option>
        <option value="Chile"></option>
        <option value="Colombia"></option>
        <option value="Estados Unidos"></option>
        <option value="México"></option>
        <option value="España"></option>
    </datalist>
    <span class="form_line"></span>
</div>
<!-- Nacionalidad -->
<div class="form_gruop">
    <label for="nacionalidad_input">Nacionalidad:</label>
    <input class="form_input" id="nacionalidad_input" list="nacionalidad" name="nacionalidad" placeholder="Escribe o selecciona" value="<?php echo htmlspecialchars($userData['Nacionalidad'] ?? ''); ?>"/>
    <datalist id="nacionalidad">
        <option value="Argentina"></option>
        <option value="Brasil"></option>
        <option value="Canadá"></option>
        <option value="Chile"></option>
        <option value="Colombia"></option>
        <option value="Estados Unidos"></option>
        <option value="México"></option>
        <option value="España"></option>
    </datalist>
    <span class="form_line"></span>
</div>
<!-- Correo Electronico -->
<div class="form_gruop">
    <label for="correo">Correo:</label>
    <input class="form_input" id="correo" name="correo" placeholder="Correo electronico" required type="email" value="<?php echo htmlspecialchars($userData['Correo'] ?? ''); ?>"/>
    <span class="form_line"></span>
</div>
<!-- Telefono -->
<div class="form_gruop">
    <label for="telefono">Teléfono:</label>
    <input class="form_input" id="telefono" name="telefono" placeholder="Teléfono" type="tel" value="<?php echo htmlspecialchars($userData['Telefono'] ?? ''); ?>"/>
    <span class="form_line"></span>
</div>
<!-- Contraseña -->
<div class="form_gruop">
    <label for="contrasena">Nueva Contraseña (opcional):</label>
    <input class="form_input" id="contrasena" name="contrasena" placeholder="Inserte nueva contraseña" type="password"/>
    <span class="form_line"></span>
</div>
<button type="submit" name="save_changes" class="form_submit full-width">Guardar Cambios</button>
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