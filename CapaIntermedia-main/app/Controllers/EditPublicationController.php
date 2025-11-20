<?php
class EditPublicationController
{
    private mysqli $db;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
    }

    public function handle(): void
    {
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: Iniciar_sesion.php');
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $pubId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
        if ($pubId <= 0) {
            header('Location: mis_publicaciones.php');
            exit();
        }

        $userRepo = new UserRepository($this->db);
        $pubRepo = new PublicationRepository($this->db);
        $catRepo = new CatalogRepository($this->db);

        $user = $userRepo->getUserDetails($userId);

        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = trim($_POST['Titulo'] ?? '');
            $descripcion = trim($_POST['Descripcion'] ?? '');
            $idCateg = (int)($_POST['ID_categ'] ?? 0);
            $idMundial = (int)($_POST['ID_Mundial'] ?? 0);

            if (empty($titulo) || empty($descripcion) || empty($idCateg) || empty($idMundial)) {
                $error_message = 'Por favor, completa todos los campos.';
            }
            elseif (strlen($titulo) > 255) {
                $error_message = 'El título no puede superar los 255 caracteres.';
            }
            elseif (strlen($descripcion) > 65535) {
                $error_message = 'La descripción es demasiado larga.';
            } else {
                $mediaType = null;
                $mediaTmpPath = null;
                if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
                    $mediaType = $_FILES['Multimedia']['type'] ?? null;
                    $mediaTmpPath = $_FILES['Multimedia']['tmp_name'] ?? null;
                    $mediaSize = $_FILES['Multimedia']['size'] ?? 0;
                    
                    // MIME
                    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo'];
                    if (!in_array($mediaType, $allowed_types)) {
                        $error_message = 'Solo se permiten archivos de imagen (JPEG, PNG, GIF, WebP) o video (MP4, MPEG, MOV, AVI).';
                    }
                    elseif (strpos($mediaType, 'video/') === 0) {
                        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                        $actualType = finfo_file($fileInfo, $mediaTmpPath);
                        finfo_close($fileInfo);
                        
                        if (!str_contains($actualType, 'video/')) {
                            $error_message = 'El archivo no es un video válido.';
                        }
                    }
                    if (strpos($mediaType, 'image/') === 0 && $mediaSize > 5 * 1024 * 1024) {
                        $error_message = 'Las imágenes no pueden superar los 5MB para mejor rendimiento.';
                    }
                }
                
                if (empty($error_message)) {
                    try {
                        $pubRepo->updatePublication($pubId, $titulo, $descripcion, $mediaType, $mediaTmpPath, $idCateg, $idMundial);
                        header('Location: mis_publicaciones.php?edit=success');
                        exit;
                    } catch (RuntimeException $e) {
                        $error_message = $e->getMessage();
                    }
                }
            }
        }

        // Cargar formulario
        $pub_data = $pubRepo->getForEdit($pubId, $userId);
        if (!$pub_data) {
            header('Location: mis_publicaciones.php');
            exit();
        }
        $categorias = $catRepo->getCategorias();
        $mundiales = $catRepo->getMundiales();

        $displayName = $user['displayName'];
        $photoSrc = $user['photoSrc'];
        $userType = $user['userType'];

        // vista
        $data = compact('displayName','photoSrc','userType','pub_data','categorias','mundiales','error_message');
        extract($data);
        require __DIR__ . '/../Views/editar_publicacion.php';
    }
}
