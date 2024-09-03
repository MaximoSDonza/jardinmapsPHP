<?php
require_once('../../cors.php');
require_once('../../connection.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['cordTitulo']) && isset($data['cordRuta']) && isset($data['cordLongitud']) && isset($data['cordLatitud'])) {
    $cordTit = $data['cordTitulo'];
    $cordRut = $data['cordRuta'];
    $cordLong = $data['cordLongitud'];
    $cordLat = $data['cordLatitud'];
    $zoom=10;
    try {
        $query = "INSERT INTO cords (cords_longitude, cords_latitude, cords_zoom, cords_titulo, cords_rutas) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => $connection->error
            ]);
            exit;
        }

        $stmt->bind_param("ssisi", $cordLong, $cordLat, $zoom, $cordTit, $cordRut);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Cordenada creada exitosamente.'
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

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>