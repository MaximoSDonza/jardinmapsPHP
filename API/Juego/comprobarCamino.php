<?php
require_once('../../cors.php');
require_once('../../connection.php');

$userId = $_GET['userId'];

try {
    $query = "SELECT * FROM historialrutas WHERE hRuta_user=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $resultados = [];

        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }
        
        if (count($resultados)==0) {
            $cordsQuery = "SELECT cords_id FROM cords WHERE cords_rutas=1 ORDER BY cords_id ASC LIMIT 1";
            $cordsStmt = $connection->prepare($cordsQuery);

            if ($cordsStmt->execute()) {
                $cordResult = $cordsStmt->get_result();
                if ($cordRow = $cordResult->fetch_assoc()) {
                    $cordId = $cordRow['cords_id'];
                    $insertQuery = "INSERT INTO historialrutas (hRuta_ruta, hRuta_user, hRuta_cord) VALUES (1, ?, ?)";
                    $insertStmt = $connection->prepare($insertQuery);
                    $insertStmt->bind_param("ii", $userId, $cordId);
                    $insertStmt->execute();
                }
            }
        }
    
        
        echo json_encode([
            'success' => true
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
?>
