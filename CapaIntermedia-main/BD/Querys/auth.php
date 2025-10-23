<?php

function attemptLogin(mysqli $conn, string $username, string $password): string|bool
{
    $stmt = $conn->prepare("CALL SP_InicSes(?, ?)");
    if (!$stmt) {
        return 'Error interno del servidor. Por favor, intenta de nuevo más tarde.';
    }
 
    $stmt->bind_param('ss', $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
 
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['ID_User'];
        $_SESSION['username'] = $user['Nombre'];
        return true;
    }
 
    return 'Usuario o contraseña incorrectos.';
}