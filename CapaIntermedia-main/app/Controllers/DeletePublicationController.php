<?php

class DeletePublicationController {
    private PublicationRepository $pubRepo;
    private UserRepository $userRepo;

    public function __construct(PublicationRepository $pubRepo, UserRepository $userRepo) {
        $this->pubRepo = $pubRepo;
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

        // Leer JSON del body
        $input = json_decode(file_get_contents('php://input'), true);
        $publication_id = $input['id'] ?? null;

        if (!$publication_id) {
            echo json_encode(['success' => false, 'message' => 'ID de publicación no proporcionado']);
            return;
        }

        // Verificar que sea el dueño de la publicación o administrador
        $userDetails = $this->userRepo->getUserDetails($_SESSION['user_id']);
        $publication = $this->pubRepo->getPublicationDetail((int)$publication_id);
        
        if (!$publication) {
            echo json_encode(['success' => false, 'message' => 'Publicación no encontrada']);
            return;
        }

        // Permitir si es admin o dueño de la publicación
        $isAdmin = isset($userDetails['userType']) && $userDetails['userType'] === 0;
        $isOwner = ($publication['ID_User_Publicador'] ?? null) == $_SESSION['user_id'];

        if (!$isAdmin && !$isOwner) {
            echo json_encode(['success' => false, 'message' => 'No tienes permisos para eliminar esta publicación']);
            return;
        }

        try {
            $result = $this->pubRepo->deletePublicationComplete((int)$publication_id);
            
            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Publicación eliminada correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar la publicación']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
