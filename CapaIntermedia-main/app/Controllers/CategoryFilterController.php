<?php

class CategoryFilterController {
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
        
        // Get category ID and sort option from query parameters
        $category_id = intval($_GET['id'] ?? 0);
        $sort_by = $_GET['sort'] ?? 'recent';

        // Validate sort option
        if (!in_array($sort_by, ['recent', 'likes', 'comments'])) {
            $sort_by = 'recent';
        }

        // Get category details
        $category_details = $this->catalogRepo->getCategoryByID($category_id);
        
        // If category not found, show error
        if ($category_details === null) {
            die("Categoría no encontrada.");
        }

        // Get publications for this category
        $publications = $this->catalogRepo->getPublicationsByCategory($category_id, $sort_by);

        // Prepare category image
        $categoryImageSrc = '../css/PlaceHolder3.jpg';
        if (!empty($category_details['Imagen'])) {
            $categoryImageSrc = 'data:image/jpeg;base64,' . base64_encode($category_details['Imagen']);
        }

        // Load view
        if ($userDetails) {
            extract($userDetails);
        }
        require __DIR__ . '/../Views/categoria.php';
    }
}
