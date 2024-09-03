<?php
require_once('../../cors.php');
require_once('../../connection.php');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['pistaDesc']) && isset($data['pistaCord'])) {
    $pistaDesc = $data['pistaDesc'];
    $pistaCord = $data['pistaCord'];

    try {
        $query = "INSERT INTO pistas (pistas_desc, pistas_cordenada) VALUES (?, ?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("si", $pistaDesc, $pistaCord);
        
        
        if ($stmt->execute()) {
            return [
                'success' => true
            ];
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

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>