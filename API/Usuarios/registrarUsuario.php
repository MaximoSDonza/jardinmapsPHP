<?php
require_once('../../cors.php');
require_once('../../connection.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['userNombre']) && isset($data['userClave']) && isset($data['userEmail'])) {
    $userNombre = $data['userNombre'];
    $userClave = $data['userClave'];
    $userEmail = $data['userEmail'];
    try {
        $query = "INSERT INTO `users` (users_nombre,users_email,users_clave,users_rango) VALUES(?,?,?,2)";
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => $connection->error
            ]);
            exit;
        }

        $stmt->bind_param("sss", $userNombre, $userEmail, $userClave);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario registrado exitosamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => $stmt->error
            ]);
        }

        $stmt->close();
    } catch (\Throwable $err) {
        echo json_encode([
            'success' => false,
            'error' => $err->getMessage()
        ]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>