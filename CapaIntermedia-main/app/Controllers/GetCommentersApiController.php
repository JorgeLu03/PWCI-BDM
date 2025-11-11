<?php

class GetCommentersApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        // Validate GET parameter
        $publiId = isset($_GET['publi_id']) ? (int)$_GET['publi_id'] : 0;

        if ($publiId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de publicación inválido.']);
            exit();
        }

        // Get commenters
        $commenters = $this->publicationRepo->getCommenters($publiId);

        echo json_encode([
            'success' => true,
            'commenters' => $commenters
        ]);
    }
}
