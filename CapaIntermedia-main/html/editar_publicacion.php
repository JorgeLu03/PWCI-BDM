<?php
// Bootstrap MVC para editar publicación
session_start();
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Repositories/CatalogRepository.php';
require_once __DIR__ . '/../app/Controllers/EditPublicationController.php';

$controller = new EditPublicationController(Database::getConnection());
$controller->handle();
