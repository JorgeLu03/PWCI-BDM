<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/CatalogRepository.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/DeleteMundialController.php';

$db = Database::getConnection();
$controller = new DeleteMundialController(new CatalogRepository($db), new UserRepository($db));
$controller->handle();
