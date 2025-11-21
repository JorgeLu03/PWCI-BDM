<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

header('Content-Type: application/json');

try {
    require_once '../app/Core/Database.php';
    require_once '../app/Repositories/PublicationRepository.php';
    require_once '../app/Controllers/GetLikersApiController.php';
    
    $publicationRepository = new PublicationRepository(Database::getConnection());
    $controller = new GetLikersApiController($publicationRepository);
    $controller->handle();
    
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}