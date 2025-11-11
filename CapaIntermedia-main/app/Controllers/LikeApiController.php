<?php

class LikeApiController {
    private $publicationRepo;

    public function __construct($publicationRepo) {
        $this->publicationRepo = $publicationRepo;
    }

    public function handle() {
        header('Content-Type: application/json');

        // Validate session
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para dar like.']);
            exit();
        }

        // Validate POST data
        $userId = (int)$_SESSION['user_id'];
        $publiId = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;

        if ($publiId <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid publication ID']);
            exit();
        }

        // Toggle like
        $likeStatus = $this->publicationRepo->toggleLike($userId, $publiId);

        // Get new like count
        $newLikeCount = $this->publicationRepo->getLikeCount($publiId);

        // Return response
        echo json_encode([
            'success' => true,
            'like_status' => $likeStatus,
            'new_like_count' => $newLikeCount
        ]);
    }
}
