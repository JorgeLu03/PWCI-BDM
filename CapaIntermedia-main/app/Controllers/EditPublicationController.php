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
        // GET: obtener datos para editar; POST: actualizar
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = $_POST['Titulo'] ?? '';
            $descripcion = $_POST['Descripcion'] ?? '';
            $idCateg = (int)($_POST['ID_categ'] ?? 0);
            $idMundial = (int)($_POST['ID_Mundial'] ?? 0);

            if (empty($titulo) || empty($descripcion) || empty($idCateg) || empty($idMundial)) {
                $error_message = 'Por favor, completa todos los campos.';
            } else {
                $mediaType = null;
                $mediaTmpPath = null;
                if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
                    $mediaType = $_FILES['Multimedia']['type'] ?? null;
                    $mediaTmpPath = $_FILES['Multimedia']['tmp_name'] ?? null;
                }
                try {
                    $pubRepo->updatePublication($pubId, $titulo, $descripcion, $mediaType, $mediaTmpPath, $idCateg, $idMundial);
                    header('Location: mis_publicaciones.php?edit=success');
                    exit;
                } catch (RuntimeException $e) {
                    $error_message = $e->getMessage();
                }
            }
        }

        // Cargar datos para el formulario
        $pub_data = $pubRepo->getForEdit($pubId, $userId);
        if (!$pub_data) {
            header('Location: mis_publicaciones.php');
            exit();
        }
        $categorias = $catRepo->getCategorias();
        $mundiales = $catRepo->getMundiales();

        // Variables para la vista
        $displayName = $user['displayName'];
        $photoSrc = $user['photoSrc'];
        $userType = $user['userType'];

        // Renderizar vista
        $error_message_local = $error_message; // para aislar nombre
        // Usamos variables compactadas
        $data = compact('displayName','photoSrc','userType','pub_data','categorias','mundiales','error_message_local');
        extract($data);
        require __DIR__ . '/../Views/edit_publicacion.php';
    }
}
