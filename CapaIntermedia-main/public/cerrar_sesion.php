<?php
session_start();

require_once '../app/Controllers/LogoutController.php';

$controller = new LogoutController();
$controller->handle();
?>