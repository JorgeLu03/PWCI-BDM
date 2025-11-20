<?php

class DeleteCategoryController {
    private CatalogRepository $catalogRepo;
    private UserRepository $userRepo;

    public function __construct(CatalogRepository $catalogRepo, UserRepository $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión']);
            return;
        }

        // Es administrador
        $userDetails = $this->userRepo->getUserDetails($_SESSION['user_id']);
        if (!isset($userDetails['userType']) || $userDetails['userType'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
            return;
        }

        $category_id = $_POST['category_id'] ?? null;

        if (!$category_id) {
            echo json_encode(['success' => false, 'message' => 'ID de categoría no proporcionado']);
            return;
        }

        try {
            $result = $this->catalogRepo->deleteCategory((int)$category_id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Categoría eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la categoría']);
            }
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            
            if (strpos($errorMsg, 'existen publicaciones') !== false) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No se puede eliminar: existen publicaciones asociadas a esta categoría'
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $errorMsg]);
            }
        }
    }
}
