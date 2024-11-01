<?php
require_once('../../cors.php');
require_once('../../connection.php');

if (isset($_POST['idCord'])) {
    $idCord = $_POST['idCord'];

    try {
        $query = "SELECT * FROM pistas WHERE pistas_cordenada = ?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idCord);
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

        $query = "DELETE FROM `pistas` WHERE pistas_cordenada=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idCord);
        $stmt->execute();
        
        $query = "DELETE FROM cords WHERE cords_id=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("i", $idCord);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]); 
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);  
        }

        $stmt->close();
    } catch (\Throwable $err) {
        echo json_encode(['success' => false, 'error' => $err->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>