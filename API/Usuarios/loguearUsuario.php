<?php
require_once('../../cors.php');
require_once('../../connection.php');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Error al decodificar JSON.']);
    exit;
}

if (isset($data['userNombre']) && isset($data['userNumero'])) {
    $userNombre = $data['userNombre'];
    $userNumero = $data['userNumero'];

    try {
        $query = "SELECT * FROM users WHERE users_nombre=? AND users_numero=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $userNombre, $userNumero);
        
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $userId = $row['users_id'];
                $insertQuery = "INSERT INTO historiallogin (hLogin_user) VALUES (?)";
                $insertStmt = $connection->prepare($insertQuery);
                $insertStmt->bind_param("i", $userId);

                if ($insertStmt->execute()) {
                    echo json_encode([
                        'success' => true
                    ]);
                } else {
                    echo json_encode(["success" => false, "error" => $insertStmt->error]);
                }

                $insertStmt->close();
            } else {
                return [
                    'success' => false,
                    'error' => $stmt->error
                ];
            }
        }
        $stmt->close();
    } catch (\Throwable $err) {
        echo json_encode(['success' => false, 'error' => $err->getMessage()]);
        exit;
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>