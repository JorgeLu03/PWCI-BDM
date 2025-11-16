<?php

class WorldCupDetailController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Get user details if logged in
        $userDetails = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
        }
        
        // Get mundial ID and sort option from query parameters
        $mundial_id = intval($_GET['id'] ?? 0);
        $sort_by = $_GET['sort'] ?? 'recent';

        // Validate sort option
        if (!in_array($sort_by, ['recent', 'likes', 'comments'])) {
            $sort_by = 'recent';
        }

        // Get mundial details con estadísticas usando V_MundialesConEstadisticas
        $mundial_details = $this->catalogRepo->getWorldCupWithStats($mundial_id);
        
        // If mundial not found, redirect to list
        if ($mundial_details === null) {
            header("Location: mundiales.php");
            exit();
        }

        // Get publications for this mundial
        $publications = $this->catalogRepo->getPublicationsByWorldCup($mundial_id, $sort_by);

        // Prepare images
        $bannerSrc = '../css/PlaceHolder3.jpg';
        if (!empty($mundial_details['Banner'])) {
            $bannerSrc = 'data:image/jpeg;base64,' . base64_encode($mundial_details['Banner']);
        }
        
        $logoSrc = '../css/PlaceHolder3.jpg';
        if (!empty($mundial_details['Logo'])) {
            $logoSrc = 'data:image/png;base64,' . base64_encode($mundial_details['Logo']);
        }

        // Load view
        if ($userDetails) {
            extract($userDetails);
        }
        require __DIR__ . '/../Views/detalle_mundial.php';
    }
}
