<?php
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Controllers/CommentApiController.php';

$controller = new CommentApiController(new PublicationRepository(Database::getConnection()));
$controller->handle();