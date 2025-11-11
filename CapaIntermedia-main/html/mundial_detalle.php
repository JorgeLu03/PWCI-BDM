<?php
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/CatalogRepository.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/WorldCupDetailController.php';

$db = Database::getConnection();
$controller = new WorldCupDetailController(new CatalogRepository($db), new UserRepository($db));
$controller->handle();
?>
