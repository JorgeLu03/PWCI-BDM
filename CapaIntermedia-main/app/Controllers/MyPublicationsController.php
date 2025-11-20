<?php
class MyPublicationsController
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
        if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
            header('Location: Iniciar_sesion.php');
            exit();
        }

        $userId = $_SESSION['user_id'];
        
        // Detalles de usuario
        $userDetails = $this->userRepo->getUserDetails($userId);
        $displayName = $userDetails['displayName'];
        $photoSrc = $userDetails['photoSrc'];
        $userType = $userDetails['userType'];

        $userStats = $this->pubRepo->getUserPublicationStats($userId);
        
        $userAge = $this->userRepo->getUserAge($userId);

        // Publicaciones del usuario
        try {
            $user_publications = $this->pubRepo->getUserPublications($userId);
        } catch (Exception $e) {
            $user_publications = [];
            error_log("Error al obtener publicaciones del usuario: " . $e->getMessage());
        }

        // Vista
        extract([
            'displayName' => $displayName,
            'photoSrc' => $photoSrc,
            'userType' => $userType,
            'user_publications' => $user_publications,
            'userStats' => $userStats,
            'userAge' => $userAge
        ]);
        require __DIR__ . '/../Views/mis_publicaciones.php';
    }
}
