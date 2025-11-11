<?php
class HomeController
{
    private mysqli $db;
    private UserRepository $userRepo;
    private PublicationRepository $pubRepo;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
        $this->pubRepo = new PublicationRepository($db);
    }

    public function handle(): void
    {
        // Obtener detalles del usuario (si está logueado)
        $userId = $_SESSION['user_id'] ?? null;
        $userDetails = $this->userRepo->getUserDetails($userId);
        $displayName = $userDetails['displayName'];
        $photoSrc = $userDetails['photoSrc'];
        $userType = $userDetails['userType'];

        // Determinar el filtro activo
        $sort_by = $_GET['sort'] ?? 'recent';

        // Obtener las publicaciones según el filtro
        try {
            $publications = $this->pubRepo->getApprovedPublications($sort_by);
        } catch (Exception $e) {
            $publications = [];
            error_log("Error al obtener publicaciones: " . $e->getMessage());
        }

        // Renderizar la vista
        extract([
            'displayName' => $displayName,
            'photoSrc' => $photoSrc,
            'userType' => $userType,
            'sort_by' => $sort_by,
            'publications' => $publications
        ]);
        require __DIR__ . '/../Views/inicio.php';
    }
}
