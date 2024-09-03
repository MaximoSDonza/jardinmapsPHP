<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT * FROM rutas";
    $stmt = $connection->prepare($query);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $rutas = [];

        while ($row = $result->fetch_assoc()) {
            $rutas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'rutas' => $rutas
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