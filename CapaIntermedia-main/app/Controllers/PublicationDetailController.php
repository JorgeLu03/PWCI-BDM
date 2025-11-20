<?php
class PublicationDetailController
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
        $publi_id = $_GET['id'] ?? 0;
        
        if ($publi_id <= 0) {
            header("Location: inicio.php");
            exit();
        }

        $current_user_id = $_SESSION['user_id'] ?? 0;
        $userId = $current_user_id > 0 ? $current_user_id : null;
        $userDetails = $this->userRepo->getUserDetails($userId);
        $displayName = $userDetails['displayName'];
        $photoSrc = $userDetails['photoSrc'];
        $userType = $userDetails['userType'];

        try {
            $publication = $this->pubRepo->getPublicationDetail($publi_id);
            
            if ($publication === null) {
                header("Location: inicio.php");
                exit();
            }

            // Obtener likes y comentarios
            $like_count = $this->pubRepo->getLikeCount($publi_id);
            $user_has_liked = $current_user_id > 0 ? $this->pubRepo->checkUserLike($current_user_id, $publi_id) : false;
            
            // Mostrar info del comentador
            $comments = $this->pubRepo->getCommentsWithUserInfo($publi_id, 2);

        } catch (Exception $e) {
            error_log("Error al obtener detalle de publicación: " . $e->getMessage());
            header("Location: inicio.php");
            exit();
        }

        // vista
        extract([
            'displayName' => $displayName,
            'photoSrc' => $photoSrc,
            'userType' => $userType,
            'publi_id' => $publi_id,
            'publication' => $publication,
            'like_count' => $like_count,
            'user_has_liked' => $user_has_liked,
            'current_user_id' => $current_user_id,
            'comments' => $comments
        ]);
        require __DIR__ . '/../Views/comentarios_publicacion.php';
    }
}
