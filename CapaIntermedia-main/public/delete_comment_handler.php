<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Controllers/DeleteCommentApiController.php';

$controller = new DeleteCommentApiController(new PublicationRepository(Database::getConnection()));
$controller->handle();
