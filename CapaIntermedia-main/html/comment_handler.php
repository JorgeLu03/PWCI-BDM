<?php
session_start();
require_once '../BD/Connection/Connection.php';

header('Content-Type: application/json');

// 1. Validar sesión y datos de entrada
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Usuario no autenticado.']);
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$publi_id = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if ($publi_id <= 0 || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit();
}

// 2. Llamar al procedimiento para añadir el comentario
$stmt = $conn->prepare("CALL SP_AddComment(?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta.']);
    exit();
}

$stmt->bind_param('sii', $content, $user_id, $publi_id);

if ($stmt->execute()) {
    // 3. Devolver una respuesta de éxito simple
    echo json_encode([
        'success' => true,
        'message' => 'Tu comentario ha sido enviado y está pendiente de revisión.'
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al guardar el comentario: ' . $stmt->error]);
}