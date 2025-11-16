<?php
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Controllers/LikeApiController.php';

$controller = new LikeApiController(new PublicationRepository(Database::getConnection()));
$controller->handle();