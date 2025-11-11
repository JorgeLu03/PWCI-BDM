<?php

class CommentApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        // Validate session
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para poder comentar.']);
            exit();
        }

        // Validate POST data
        $userId = (int)$_SESSION['user_id'];
        $publiId = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($publiId <= 0 || empty($content)) {
            echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
            exit();
        }

        // Add comment
        $success = $this->publicationRepo->addComment($content, $userId, $publiId);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Tu comentario ha sido enviado y está pendiente de revisión.'
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar el comentario.']);
        }
    }
}
