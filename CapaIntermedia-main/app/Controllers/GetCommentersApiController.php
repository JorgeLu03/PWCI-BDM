<?php

class GetCommentersApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        $publiId = isset($_GET['publi_id']) ? (int)$_GET['publi_id'] : 0;

        if ($publiId <= 0) {
            echo json_encode(['success' => false, 'error' => 'ID de publicación inválido.']);
            return;
        }

        try {
            $commenters = $this->publicationRepo->getCommenters($publiId);

            echo json_encode([
                'success' => true,
                'commenters' => $commenters
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener commenters: ' . $e->getMessage()
            ]);
        }
    }
}
