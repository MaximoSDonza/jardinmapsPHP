<?php
require_once('../../cors.php');
require_once('../../connection.php');

try {
    $query = "SELECT users_nombre, MAX(hLogin_fechaUlt) AS ultima_fecha_logueo FROM historiallogin h INNER JOIN users u ON h.hLogin_user = u.users_id GROUP BY h.hLogin_user";
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