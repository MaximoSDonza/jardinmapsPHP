<?php
require_once('../../cors.php');
require_once('../../connection.php');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['userEmail']) && isset($data['userClave'])) {
    $userEmail = $data['userEmail'];
    $userClave = $data['userClave'];

    try {
        $query = "SELECT * FROM users WHERE users_email=? AND users_clave=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ss", $userEmail, $userClave);
        
        
        if ($stmt->execute()) {
            $result = $stmt->get_result();

            if ($row = $result->fetch_assoc()) {
                $userId = $row['users_id'];
                $insertQuery = "INSERT INTO historialLogin (hLogin_user) VALUES (?)";
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
        throw $err;
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>