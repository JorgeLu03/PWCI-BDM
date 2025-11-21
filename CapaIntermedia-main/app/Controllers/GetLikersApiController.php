<?php

class GetLikersApiController {
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
            error_log("Intentando obtener likers para publi_id: " . $publiId);
            $likers = $this->publicationRepo->getLikers($publiId);
            error_log("Likers obtenidos: " . count($likers));

            echo json_encode([
                'success' => true,
                'likers' => $likers,
                'count' => count($likers)
            ]);
        } catch (Exception $e) {
            error_log("Error en GetLikersApiController: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener likers: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (Throwable $e) {
            error_log("Error fatal en GetLikersApiController: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'error' => 'Error fatal: ' . $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
