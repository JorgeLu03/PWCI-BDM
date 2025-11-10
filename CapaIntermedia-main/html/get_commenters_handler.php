<?php
session_start();
require_once '../BD/Connection/Connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit();
}

$publi_id = isset($_GET['publi_id']) ? (int)$_GET['publi_id'] : 0;

if ($publi_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de publicación inválido.']);
    exit();
}

$stmt = $conn->prepare("CALL SP_GetCommentersByPost(?)");
$stmt->bind_param('i', $publi_id);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Convertir las imágenes a base64
foreach ($users as &$user) {
    if (!empty($user['Foto'])) {
        $user['Foto'] = base64_encode($user['Foto']);
    }
}

echo json_encode(['success' => true, 'users' => $users]);

$conn->close();
?>