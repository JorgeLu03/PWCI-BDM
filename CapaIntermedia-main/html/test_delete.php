<?php
session_start();

header('Content-Type: application/json');

echo json_encode([
    'test' => 'works',
    'session_user_id' => $_SESSION['user_id'] ?? 'not set',
    'post_data' => $_POST
]);
