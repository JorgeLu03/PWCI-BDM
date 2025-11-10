<?php
session_start();
require_once '../BD/Connection/Connection.php';
require_once '../BD/Querys/user_functions.php';

header('Content-Type: application/json');

// 1. Validar que el usuario sea administrador
$userDetails = getUserDetails($conn);
if ($userDetails['userType'] !== 0) {
    echo json_encode(['success' => false, 'error' => 'Acceso denegado.']);
    exit();
}

// 2. Validar datos de entrada
$comment_id = isset($_POST['comment_id']) ? (int)$_POST['comment_id'] : 0;

if ($comment_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID de comentario inválido.']);
    exit();
}

// 3. Llamar al procedimiento para eliminar el comentario
$stmt = $conn->prepare("CALL SP_DeleteCommentByAdmin(?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta.']);
    exit();
}

$stmt->bind_param('i', $comment_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comentario eliminado con éxito.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al eliminar el comentario: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>