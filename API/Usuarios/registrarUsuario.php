<?php
require_once('../../cors.php');
require_once('../../connection.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['userNombre']) && isset($data['userNumero'])) {
    $userNombre = $data['userNombre'];
    $userNumero = $data['userNumero'];
    try {
        $query = "INSERT INTO `users` (users_nombre,users_numero,users_rango) VALUES(?,?,2)";
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => $connection->error
            ]);
            exit;
        }

        $stmt->bind_param("ss", $userNombre, $userNumero);
        
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