<?php
require_once('../../cors.php');
require_once('../../connection.php');

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['cordTitulo']) && isset($data['cordRuta']) && isset($data['cordFake1']) && isset($data['cordFake2'])) {
    $cordTit = $data['cordTitulo'];
    $cordRut = $data['cordRuta'];
    $cordFake1 = $data['cordFake1'];
    $cordFake2 = $data['cordFake2'];
    $zoom=10;
    try {
        $query = "INSERT INTO cords (cords_fake1, cords_fake2, cords_zoom, cords_titulo, cords_rutas) VALUES (?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($query);
        
        if (!$stmt) {
            echo json_encode([
                'success' => false,
                'error' => $connection->error
            ]);
            exit;
        }

        $stmt->bind_param("ssisi", $cordFake1, $cordFake2, $zoom, $cordTit, $cordRut);
        
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