<?php

class DeleteMundialController {
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

        $userDetails = $this->userRepo->getUserDetails($_SESSION['user_id']);
        if (!isset($userDetails['userType']) || $userDetails['userType'] !== 0) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para esta acción']);
            return;
        }

        $mundial_id = $_POST['mundial_id'] ?? null;

        if (!$mundial_id) {
            echo json_encode(['success' => false, 'message' => 'ID de mundial no proporcionado']);
            return;
        }

        try {
            $result = $this->catalogRepo->deleteMundial((int)$mundial_id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Mundial eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el mundial']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()]);
        }
    }
}
