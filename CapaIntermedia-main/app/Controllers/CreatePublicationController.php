<?php
class CreatePublicationController
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
        $userRepo = new UserRepository($this->db);
        $catRepo = new CatalogRepository($this->db);
        $pubRepo = new PublicationRepository($this->db);

        $user = $userRepo->getUserDetails($userId);
        $error_message = '';

        // Manejo de alerta de éxito vía GET
        $show_success = isset($_GET['publicado']) && $_GET['publicado'] === 'exitoso';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $titulo = $_POST['Titulo'] ?? '';
            $descripcion = $_POST['Descripcion'] ?? '';
            $idCateg = (int)($_POST['ID_categ'] ?? 0);
            $idMundial = (int)($_POST['ID_Mundial'] ?? 0);

            $estatus = 1; // Pendiente
            $views = 0;
            $fecAprob = null;
            $fecPub = date('Y-m-d');

            if (empty($titulo) || empty($descripcion) || empty($idCateg) || empty($idMundial)) {
                $error_message = 'Por favor, completa el título, descripción, categoría y mundial.';
            } elseif (empty($userId)) {
                $error_message = 'Error de sesión: No se pudo identificar al usuario. Por favor, inicia sesión de nuevo.';
            } else {
                $mediaType = null;
                $mediaTmpPath = null;
                if (isset($_FILES['Multimedia']) && $_FILES['Multimedia']['error'] === UPLOAD_ERR_OK) {
                    $mediaType = $_FILES['Multimedia']['type'] ?? null;
                    $mediaTmpPath = $_FILES['Multimedia']['tmp_name'] ?? null;
                }
                try {
                    $pubRepo->insertPublication($titulo, $descripcion, $estatus, $views, $fecAprob, $fecPub, $mediaType, $mediaTmpPath, $idCateg, $userId, $idMundial);
                    header('Location: crear_publicacion.php?publicado=exitoso');
                    exit;
                } catch (RuntimeException $e) {
                    $error_message = $e->getMessage();
                }
            }
        }

        // Cargar datos para el formulario
        $categorias = $catRepo->getCategorias();
        $mundiales = $catRepo->getMundiales();

        $displayName = $user['displayName'];
        $photoSrc = $user['photoSrc'];
        $userType = $user['userType'];

        $data = compact('displayName','photoSrc','userType','categorias','mundiales','error_message','show_success');
        extract($data);
        require __DIR__ . '/../Views/create_publicacion.php';
    }
}
