<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT hr.*, 
       c.*, 
       u.users_nombre, 
       subquery.position 
FROM historialrutas hr
INNER JOIN cords c ON hr.hRuta_cord = c.cords_id
INNER JOIN users u ON hr.hRuta_user = u.users_id
INNER JOIN (
    SELECT cords_id,  
           ROW_NUMBER() OVER (ORDER BY cords_id) AS position
    FROM cords  
    WHERE cords_rutas = 1
) AS subquery ON hr.hRuta_cord = subquery.cords_id
WHERE hr.hRuta_ruta = 1;";
    $stmt = $connection->prepare($query);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $historial = [];

        while ($row = $result->fetch_assoc()) {
            $historial[] = $row;
        }

        echo json_encode([
            'success' => true,
            'historial' => $historial
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