<?php
session_start();
require_once '../BD/Connection/Connection.php';

header('Content-Type: application/json');

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Not logged in']);
    exit();
}

// Obtener datos de la petición
$user_id = (int)$_SESSION['user_id'];
$publi_id = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;

if ($publi_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid publication ID']);
    exit();
}

// 1. Alternar el "Me gusta"
$stmt_toggle = $conn->prepare("CALL SP_ToggleLike(?, ?)");
$stmt_toggle->bind_param('ii', $user_id, $publi_id);
$stmt_toggle->execute();
$result_toggle = $stmt_toggle->get_result()->fetch_assoc();
$like_status = $result_toggle['status'];
$stmt_toggle->close();
while ($conn->more_results() && $conn->next_result()) {;} // Limpiar resultados

// 2. Obtener el nuevo conteo de "Me gusta"
$stmt_count = $conn->prepare("CALL SP_GetLikeCount(?)");
$stmt_count->bind_param('i', $publi_id);
$stmt_count->execute();
$result_count = $stmt_count->get_result()->fetch_assoc();
$new_like_count = $result_count['like_count'];
$stmt_count->close();

// 3. Devolver la respuesta
echo json_encode([
    'success' => true,
    'like_status' => $like_status, // 'liked' o 'unliked'
    'new_like_count' => $new_like_count
]);

$conn->close();
?>