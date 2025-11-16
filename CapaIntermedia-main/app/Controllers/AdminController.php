<?php
class AdminController
{
    private mysqli $db;
    private UserRepository $userRepo;
    private PublicationRepository $pubRepo;
    private CatalogRepository $catalogRepo;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
        $this->pubRepo = new PublicationRepository($db);
        $this->catalogRepo = new CatalogRepository($db);
    }

    public function handle(): void
    {
        // Restringir acceso solo a administradores
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: Iniciar_sesion.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        $userDetails = $this->userRepo->getUserDetails($userId);
        $displayName = $userDetails['displayName'];
        $photoSrc = $userDetails['photoSrc'];
        $userType = $userDetails['userType'];

        // Verificar que sea administrador
        if ($userType !== 0) {
            header('Location: inicio.php');
            exit();
        }

        // Manejar mensajes de feedback
        $feedback_message = $_SESSION['feedback_message'] ?? '';
        $feedback_type = $_SESSION['feedback_type'] ?? '';
        unset($_SESSION['feedback_message'], $_SESSION['feedback_type']);

        // Determinar pestaña activa
        $active_tab = $_GET['tab'] ?? 'publis';

        // Manejar creación de categoría
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_category'])) {
            $this->handleCreateCategory();
            return;
        }

        // Manejar creación de mundial
        if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['create_mundial'])) {
            $this->handleCreateMundial();
            return;
        }

        // Obtener publicaciones y comentarios pendientes
        try {
            $pending_publications = $this->pubRepo->getPendingPublications();
            $pending_comments = $this->pubRepo->getPendingComments();
        } catch (Exception $e) {
            $pending_publications = [];
            $pending_comments = [];
            error_log("Error al obtener datos pendientes: " . $e->getMessage());
        }

        // Renderizar la vista
        extract([
            'displayName' => $displayName,
            'photoSrc' => $photoSrc,
            'userType' => $userType,
            'feedback_message' => $feedback_message,
            'feedback_type' => $feedback_type,
            'active_tab' => $active_tab,
            'pending_publications' => $pending_publications,
            'pending_comments' => $pending_comments
        ]);
        require __DIR__ . '/../Views/administrar_publicaciones.php';
    }

    private function handleCreateCategory(): void
    {
        $nombre = $_POST['categoria_nombre'] ?? '';
        $descripcion = $_POST['categoria_desc'] ?? '';
        
        if (!empty($nombre) && !empty($descripcion) && isset($_FILES['categoria_imagen']) && $_FILES['categoria_imagen']['error'] == 0) {
            $imagen_data = file_get_contents($_FILES['categoria_imagen']['tmp_name']);
            
            if ($imagen_data !== false) {
                $stmt = $this->db->prepare("CALL SP_NewCategory(?, ?, ?)");
                if ($stmt) {
                    $null = NULL;
                    $stmt->bind_param('ssb', $nombre, $descripcion, $null);
                    $stmt->send_long_data(2, $imagen_data);
                    
                    if ($stmt->execute()) {
                        $_SESSION['feedback_message'] = "¡Categoría creada con éxito!";
                        $_SESSION['feedback_type'] = 'success';
                    } else {
                        $_SESSION['feedback_message'] = "Error al crear la categoría: " . $stmt->error;
                        $_SESSION['feedback_type'] = 'error';
                    }
                    $stmt->close();
                    while ($this->db->more_results() && $this->db->next_result()) {;}
                }
            }
        } else {
            $_SESSION['feedback_message'] = "Por favor, completa todos los campos, incluyendo la imagen.";
            $_SESSION['feedback_type'] = 'error';
        }
        
        header("Location: administrar_publicaciones.php?tab=create");
        exit();
    }

    private function handleCreateMundial(): void
    {
        $nombre = $_POST['mundial_nombre'] ?? '';
        $anio = $_POST['mundial_anio'] ?? '';
        $descripcion = $_POST['mundial_resena'] ?? '';
        $sedes = $_POST['mundial_sedes'] ?? '';
        $balon = $_POST['mundial_balon'] ?? '';
        $campeon = $_POST['mundial_campeon'] ?? '';
        $subcampeon = $_POST['mundial_subcampeon'] ?? '';
        $tercer_lugar = $_POST['mundial_tercer_lugar'] ?? '';
        $cuarto_lugar = $_POST['mundial_cuarto_lugar'] ?? '';
        $final_fecha = $_POST['mundial_final_fecha'] ?? '';
        $final_lugar = $_POST['mundial_final_lugar'] ?? '';
        $marcador_final = $_POST['mundial_marcador'] ?? '';
        $tiempo_extra = $_POST['mundial_tiempo_extra'] ?? 0;
        $goleador = $_POST['mundial_goleador'] ?? '';
        $alineacion = $_POST['mundial_alineacion'] ?? '';
        $cantante = $_POST['mundial_cantante'] ?? null;
        $id_user = $_SESSION['user_id'] ?? 0;

        // Cargar PlaceHolder3.jpg si no se proporciona logo o banner
        $placeholder_path = __DIR__ . '/../../css/PlaceHolder3.jpg';
        $placeholder_data = file_exists($placeholder_path) ? file_get_contents($placeholder_path) : null;

        $logo_data = (isset($_FILES['mundial_logo']) && $_FILES['mundial_logo']['error'] == 0) 
            ? file_get_contents($_FILES['mundial_logo']['tmp_name']) : $placeholder_data;
        $banner_data = (isset($_FILES['mundial_banner']) && $_FILES['mundial_banner']['error'] == 0) 
            ? file_get_contents($_FILES['mundial_banner']['tmp_name']) : $placeholder_data;

        $stmt = $this->db->prepare("CALL SP_NewMundial(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $null = NULL;
            $stmt->bind_param('sisisssssssssisssbb', $nombre, $anio, $descripcion, $id_user, $sedes, $balon, 
                $campeon, $subcampeon, $tercer_lugar, $cuarto_lugar, $final_fecha, $final_lugar, 
                $marcador_final, $tiempo_extra, $goleador, $alineacion, $cantante, $null, $null);
            
            if ($logo_data) {
                $stmt->send_long_data(17, $logo_data);
            }
            if ($banner_data) {
                $stmt->send_long_data(18, $banner_data);
            }
            
            if ($stmt->execute()) {
                $_SESSION['feedback_message'] = "¡Mundial creado con éxito!";
                $_SESSION['feedback_type'] = 'success';
            } else {
                $_SESSION['feedback_message'] = "Error al crear el mundial: " . $stmt->error;
                $_SESSION['feedback_type'] = 'error';
            }
            $stmt->close();
            while ($this->db->more_results() && $this->db->next_result()) {;}
        }
        
        header("Location: administrar_publicaciones.php?tab=create");
        exit();
    }
}
