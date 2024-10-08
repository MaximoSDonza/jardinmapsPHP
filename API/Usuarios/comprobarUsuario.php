<?php
require_once('../../cors.php');
require_once('../../connection.php');

$userNumero = $_GET['userNumero'];

try {
    $query = "SELECT * FROM users WHERE users_Numero=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("s", $userNumero);
    
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
