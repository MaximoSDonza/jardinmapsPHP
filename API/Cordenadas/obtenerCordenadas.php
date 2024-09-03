<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT * FROM cords";
    $stmt = $connection->prepare($query);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $cords = [];

        while ($row = $result->fetch_assoc()) {
            $cords[] = $row;
        }

        echo json_encode([
            'success' => true,
            'cords' => $cords
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