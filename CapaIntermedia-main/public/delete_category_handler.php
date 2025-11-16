<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/CatalogRepository.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/DeleteCategoryController.php';

$db = Database::getConnection();
$controller = new DeleteCategoryController(new CatalogRepository($db), new UserRepository($db));
$controller->handle();
