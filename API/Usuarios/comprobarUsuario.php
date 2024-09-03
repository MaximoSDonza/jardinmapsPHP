<?php
require_once('../../cors.php');
require_once('../../connection.php');

$userEmail = $_GET['userEmail'];
try {
    $query = "SELECT * FROM users WHERE users_email=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $userEmail);
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $resultados = [];

        
        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }

        echo json_encode([
            'success' => true,
            'result' => $resultados
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