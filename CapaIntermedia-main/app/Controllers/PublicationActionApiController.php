<?php

class PublicationActionApiController {
    private $publicationRepo;
    private $userRepo;

    public function __construct($publicationRepo, $userRepo) {
        $this->publicationRepo = $publicationRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
            exit();
        }

        $userDetails = $this->userRepo->getUserDetails((int)$_SESSION['user_id']);
        if ($userDetails['userType'] !== 0) {
            echo json_encode(['success' => false, 'error' => 'Acceso denegado.']);
            exit();
        }

        $publiId = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;

        if ($publiId <= 0 || !in_array($action, ['approve', 'reject'])) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
            exit();
        }

        if ($action === 'reject' && empty($reason)) {
            echo json_encode(['success' => false, 'error' => 'Se requiere un motivo para rechazar la publicación.']);
            exit();
        }

        $newStatus = ($action === 'approve') ? 2 : 3;

        // Cambiar estatus -- JSON
        $success = $this->publicationRepo->updatePublicationStatus($publiId, $newStatus, $reason);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Publicación actualizada con éxito.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar la publicación.']);
        }
    }
}
