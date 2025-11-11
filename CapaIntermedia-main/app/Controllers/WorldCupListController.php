<?php

class WorldCupListController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        // Get user details if logged in
        $userDetails = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
        }
        
        // Get all world cups
        $mundiales = $this->catalogRepo->getMundiales();

        // Load view
        if ($userDetails) {
            extract($userDetails);
        }
        require __DIR__ . '/../Views/mundiales.php';
    }
}
