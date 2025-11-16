<?php
session_start();

require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/UserRepository.php';
require_once __DIR__ . '/../app/Controllers/ProfileEditController.php';

$db = Database::getConnection();
$controller = new ProfileEditController(new UserRepository($db));
$controller->handle();