<?php
require_once('../../cors.php');
require_once('../../connection.php');

if (isset($_POST['idRuta']) && isset($_POST['idUser'])) {
    $idUser = $_POST['idUser'];
    $ruta = $_POST['idRuta'];

    $query = "SELECT imagenes_img FROM imagenes WHERE imagenes_user = ? AND imagenes_ruta = ?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("ii", $idUser, $ruta);
    $stmt->execute();
    $result = $stmt->get_result();

    $imagePaths = [];
    while ($row = $result->fetch_assoc()) {
        $imagePaths[] = $row['imagenes_img'];
    }

    foreach ($imagePaths as $imagePath) {
        $fullPath = '../../' . $imagePath;  // Asegúrate de construir la ruta completa
        if (file_exists($fullPath)) {
            unlink($fullPath);  // Eliminar el archivo del servidor
        }
    }

    try {
        $deleteQuery = "DELETE FROM imagenes WHERE imagenes_user = ? AND imagenes_ruta = ?";
        $deleteStmt = $connection->prepare($deleteQuery);
        $deleteStmt->bind_param("ii", $idUser, $ruta);
        $deleteStmt->execute();

        $query = "INSERT INTO `terminados`(`terminados_user`, `terminados_ruta`) VALUES (?,?)";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ii", $idUser, $ruta);
        $stmt->execute();

        $query = "DELETE FROM `historialrutas` WHERE hRuta_user=? AND hRuta_ruta=?";
        $stmt = $connection->prepare($query);
        $stmt->bind_param("ii", $idUser, $ruta);
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Imágenes y registros eliminados correctamente.']);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }

        $stmt->close();
    } catch (Throwable $err) {
        echo json_encode(['success' => false, 'error' => $err->getMessage()]);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}
?>