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
$publi_id = isset($_POST['publi_id']) ? (int)$_POST['publi_id'] : 0;
$action = isset($_POST['action']) ? $_POST['action'] : '';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;

if ($publi_id <= 0 || !in_array($action, ['approve', 'reject'])) {
    echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
    exit();
}

if ($action === 'reject' && empty($reason)) {
    echo json_encode(['success' => false, 'error' => 'Se requiere un motivo para rechazar la publicación.']);
    exit();
}

// 3. Determinar el nuevo estatus
$new_status = ($action === 'approve') ? 2 : 3; // 2 = Aprobada, 3 = Rechazada

// 4. Llamar al procedimiento para actualizar el estatus
$stmt = $conn->prepare("CALL SP_UpdatePublicationStatus(?, ?, ?)");
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta.']);
    exit();
}

$stmt->bind_param('iis', $publi_id, $new_status, $reason);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Publicación actualizada con éxito.']);
} else {
    echo json_encode(['success' => false, 'error' => 'Error al actualizar la publicación: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>