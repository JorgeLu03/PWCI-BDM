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
$action = isset($_POST['action']) ? $_POST['action'] : '';

if ($comment_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit();
}

// 3. Determinar el nuevo estatus
$new_status = ($action === 'approve') ? 'A' : 'R'; // A = Aprobado, R = Rechazado/Eliminado

// 4. Llamar al procedimiento para actualizar el estatus
$stmt = $conn->prepare("CALL SP_UpdateCommentStatus(?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta.']);
    exit();
}

$stmt->bind_param('is', $comment_id, $new_status);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Comentario actualizado con éxito.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar el comentario: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>