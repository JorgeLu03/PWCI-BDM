<?php
session_start();
require_once __DIR__ . '/../app/Core/Database.php';
require_once __DIR__ . '/../app/Repositories/AuthRepository.php';
require_once __DIR__ . '/../app/Controllers/LoginController.php';

$controller = new LoginController(Database::getConnection());
$controller->handle();
