<?php
require_once('../../cors.php');
require_once('../../connection.php');

if (isset($_POST['idPista'])) {
    $idPista = $_POST['idPista'];

    try {
        $query = "SELECT * FROM pistas WHERE pistas_id = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idPista);
        $stmt->execute();
        $result = $stmt->get_result();

        $imagePaths = [];
        while ($row = $result->fetch_assoc()) {
            $imagePaths[] = $row['pistas_img'];
        }

        foreach ($imagePaths as $imagePath) {
            $fullPath = '../../' . $imagePath;  // Asegúrate de construir la ruta completa
            if (file_exists($fullPath)) {
                unlink($fullPath);  // Eliminar el archivo del servidor
            }
        }

        $query = "DELETE FROM `pistas` WHERE pistas_id=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idPista);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
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

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>