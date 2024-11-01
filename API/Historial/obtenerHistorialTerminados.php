<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT 
        u.users_nombre, 
        COUNT(t.terminados_user) AS veces_usuario_en_tabla, 
        MAX(t.terminados_fecha) AS ultima_fecha_ingreso
        FROM 
            terminados t
        INNER JOIN 
            users u 
            ON t.terminados_user = u.users_id
        GROUP BY 
            t.terminados_user, u.users_nombre";

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
        echo json_encode([
            'success' => false,
            'error' => $stmt->error
        ]);
    }

    $stmt->close();
} catch (\Throwable $err) {
    throw $err;
}
?>