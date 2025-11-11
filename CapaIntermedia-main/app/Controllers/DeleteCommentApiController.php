<?php

class DeleteCommentApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        // Validate session
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
            exit();
        }

        // Validate POST data
        $userId = (int)$_SESSION['user_id'];
        $commentId = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

        if ($commentId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de comentario inválido.']);
            exit();
        }

        // Delete comment
        $success = $this->publicationRepo->deleteComment($commentId, $userId);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Comentario eliminado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al eliminar el comentario.']);
        }
    }
}
