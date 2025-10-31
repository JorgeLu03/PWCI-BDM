<?php
function getUserDetails(mysqli $conn): array
{
    $displayName = 'Mi Perfil';
    $photoSrc = '../css/PlaceHolder3.png';
    $userType = null; // Valor por defecto para el tipo de usuario

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
                    $displayName = $row['Nombre'];
                }

                if (!empty($row['Foto'])) {
                    // Convertir los datos BLOB a una Data URI para mostrar la imagen
                    $photoSrc = 'data:image/jpeg;base64,' . base64_encode($row['Foto']);
                }

                if (isset($row['Tipo_usuario'])) {
                    $userType = (int) $row['Tipo_usuario'];
                }
            }
            $stmt->close();
        }
    }

    return ['displayName' => $displayName, 'photoSrc' => $photoSrc, 'userType' => $userType];
}