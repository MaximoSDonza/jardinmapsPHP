<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT * FROM pistas p INNER JOIN cords c ON p.pistas_cordenada = c.cords_id";
    $stmt = $connection->prepare($query);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $pistas = [];

        while ($row = $result->fetch_assoc()) {
            $pistas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'pistas' => $pistas
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