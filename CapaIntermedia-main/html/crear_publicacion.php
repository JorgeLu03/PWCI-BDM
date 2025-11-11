<?php
// Bootstrap MVC para crear publicación
session_start();
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Repositories/CatalogRepository.php';
require_once __DIR__ . '/../app/Controllers/CreatePublicationController.php';

$controller = new CreatePublicationController(Database::getConnection());
$controller->handle();
