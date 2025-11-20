<?php

class LikeApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para dar like.']);
            exit();
        }

        $userId = (int)$_SESSION['user_id'];
        $publiId = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;

        if ($publiId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid publication ID']);
            exit();
        }

        $likeStatus = $this->publicationRepo->toggleLike($userId, $publiId);

        $newLikeCount = $this->publicationRepo->getLikeCount($publiId);

        // JSON
        echo json_encode([
            'success' => true,
            'like_status' => $likeStatus,
            'new_like_count' => $newLikeCount
        ]);
    }
}
