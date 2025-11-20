<?php

class CategoryFilterController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Detalles del usuario
        $userDetails = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
        }
        
        // Obtiene ID categoría y la opción de filtro
        $category_id = intval($_GET['id'] ?? 0);
        $sort_by = $_GET['sort'] ?? 'recent';

        if (!in_array($sort_by, ['recent', 'likes', 'comments'])) {
            $sort_by = 'recent';
        }

        // Obtiene detalles de categoría
        $category_details = $this->catalogRepo->getCategoryByID($category_id);
                if ($category_details === null) {
            die("Categoría no encontrada.");
        }

        // Obtener las publis del la categoría (con filtro)
        $publications = $this->catalogRepo->getPublicationsByCategory($category_id, $sort_by);

        // Imagen default
        $categoryImageSrc = '../css/PlaceHolder3.jpg';
        if (!empty($category_details['Imagen'])) {
            $categoryImageSrc = 'data:image/jpeg;base64,' . base64_encode($category_details['Imagen']);
        }

        // Vista
        if ($userDetails) {
            extract($userDetails);
        }
        require __DIR__ . '/../Views/categoria.php';
    }
}
