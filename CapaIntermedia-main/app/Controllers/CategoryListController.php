<?php

class CategoryListController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Detalles del usuario
        $userDetails = null;
        $displayName = 'Mi Perfil';
        $photoSrc = '../css/PlaceHolder3.jpg';
        $userType = null;
        
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
            extract($userDetails);
        }
        
        // Obtener categorías
        $categories = $this->catalogRepo->getCategorias();

        // Vista
        require __DIR__ . '/../Views/categorias.php';
    }
}
