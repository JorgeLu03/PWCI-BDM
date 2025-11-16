<?php

class CategoryListController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Get user details if logged in
        $userDetails = null;
        $displayName = 'Mi Perfil';
        $photoSrc = '../css/PlaceHolder3.jpg';
        $userType = null;
        
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
            extract($userDetails);
        }
        
        // Get all categories
        $categories = $this->catalogRepo->getCategorias();

        // Load view
        require __DIR__ . '/../Views/categorias.php';
    }
}
