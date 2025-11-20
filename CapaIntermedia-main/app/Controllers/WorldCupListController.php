<?php

class WorldCupListController {
    private $catalogRepo;
    private $userRepo;

    public function __construct($catalogRepo, $userRepo) {
        $this->catalogRepo = $catalogRepo;
        $this->userRepo = $userRepo;
    }

    public function handle() {
        $userDetails = null;
        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];
            $userDetails = $this->userRepo->getUserDetails($userId);
        }
        
        $mundiales = $this->catalogRepo->getWorldCupsWithStats();

        // vista
        if ($userDetails) {
            extract($userDetails);
        }
        require __DIR__ . '/../Views/mundiales.php';
    }
}
