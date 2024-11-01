<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT COUNT(*) AS total_filas
FROM cords
WHERE cords_rutas = 1";
    $stmt = $connection->prepare($query);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $total = $row['total_filas'];
        }

        echo json_encode([
            'success' => true,
            'total' => $total
        ]);
    } else {
        return [
            'success' => false,
            'error' => $stmt->error
        ];
    }

    $stmt->close();
} catch (\Throwable $err) {
    throw $err;
}
?>