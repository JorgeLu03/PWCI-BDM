<?php
session_start();

require_once '../app/Core/Database.php';
require_once '../app/Repositories/UserRepository.php';
require_once '../app/Repositories/PublicationRepository.php';
require_once '../app/Repositories/CatalogRepository.php';
require_once '../app/Controllers/AdminController.php';

$db = Database::getConnection();
$controller = new AdminController($db);
$controller->handle();
?>
