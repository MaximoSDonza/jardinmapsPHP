<?php
require_once('../../cors.php');
require_once('../../connection.php');
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['nombreRuta'])) {
    $nombreRuta = $data['nombreRuta'];

    try {
        $query = "INSERT INTO `rutas` (rutas_nombre) VALUES(?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("s", $nombreRuta);
        
        
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