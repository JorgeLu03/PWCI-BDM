<?php
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/PublicationRepository.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/PublicationActionApiController.php';

$controller = new PublicationActionApiController(
    new PublicationRepository(Database::getConnection()),
    new UserRepository(Database::getConnection())
);
$controller->handle();