<?php
class SearchController
{
    private mysqli $db;
    private UserRepository $userRepo;
    private PublicationRepository $pubRepo;

    public function __construct(mysqli $db)
    {
        $this->db = $db;
        $this->userRepo = new UserRepository($db);
        $this->pubRepo = new PublicationRepository($db);
    }

    public function handle(): void
    {
        $userId = $_SESSION['user_id'] ?? null;
        $userDetails = $this->userRepo->getUserDetails($userId);
        $displayName = $userDetails['displayName'];
        $photoSrc = $userDetails['photoSrc'];
        $userType = $userDetails['userType'];

        $search_term = $_GET['q'] ?? '';
        $publications = [];

        if (!empty($search_term)) {
            try {
                $publications = $this->pubRepo->searchPublications($search_term);
            } catch (Exception $e) {
                error_log("Error en búsqueda: " . $e->getMessage());
                $publications = [];
            }
        }

        // vista
        extract([
            'displayName' => $displayName,
            'photoSrc' => $photoSrc,
            'userType' => $userType,
            'search_term' => $search_term,
            'publications' => $publications
        ]);
        require __DIR__ . '/../Views/buscar.php';
    }
}
