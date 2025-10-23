<?php
function getUserDetails(mysqli $conn): array
{
    $displayName = 'Mi Perfil';
    $photoSrc = '../css/PlaceHolder3.png';

    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        $uid = (int) $_SESSION['user_id'];
        $stmt = $conn->prepare("CALL SP_GetUserDetails(?)");
        if ($stmt) {
            $stmt->bind_param('i', $uid);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                if (!empty($row['Nombre'])) {
                    $displayName = htmlspecialchars($row['Nombre']);
                }
                if (!empty($row['Foto'])) {
                    $foto = $row['Foto'];
                    if (strpos($foto, 'http') === 0 || strpos($foto, '/') === 0) {
                        $photoSrc = $foto;
                    } else {
                        $photoSrc = '../' . ltrim($foto, '/');
                    }
                }
            }
            $stmt->close();
        }
    }

    return ['displayName' => $displayName, 'photoSrc' => $photoSrc];
}