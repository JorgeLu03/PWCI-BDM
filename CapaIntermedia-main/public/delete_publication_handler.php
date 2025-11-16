<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/DeletePublicationController.php';

$db = Database::getConnection();
$controller = new DeletePublicationController(new PublicationRepository($db), new UserRepository($db));
$controller->handle();
