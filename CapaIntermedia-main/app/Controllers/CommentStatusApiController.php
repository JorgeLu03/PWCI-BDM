<?php

class CommentStatusApiController {
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

        $commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;
        $action = isset($_POST['action']) ? $_POST['action'] : '';

        if ($commentId <= 0 || !in_array($action, ['approve', 'reject'])) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
            exit();
        }

        $newStatus = ($action === 'approve') ? 2 : 3;

        $success = $this->publicationRepo->updateCommentStatus($commentId, $newStatus);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Comentario actualizado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar el comentario.']);
        }
    }
}
