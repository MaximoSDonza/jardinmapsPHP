<?php
require_once('../../cors.php');
require_once('../../connection.php');
ini_set('memory_limit', '512');

if (isset($_FILES['file']) && isset($_POST['cord']) && isset($_POST['user'])) {
    $cord = $_POST['cord'];
    $idUser = $_POST['user'];
    $file = $_FILES['file'];
    $ruta = $_POST['ruta'];

    $cordsQuery = "SELECT cords_id FROM cords WHERE cords_id > ? AND cords_rutas = ? ORDER BY cords_id ASC LIMIT 1";
    $cordsStmt = $connection->prepare($cordsQuery);
    $cordsStmt->bind_param("ii", $cord, $ruta);

    if ($cordsStmt->execute()) {
        $cordResult = $cordsStmt->get_result();
        if ($cordRow = $cordResult->fetch_assoc()) {
            $nextCordId = $cordRow['cords_id'];
        }
    }

    // Verificar si el archivo es una imagen válida
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido.']);
        exit;
    }

    // Generar un nombre único para la imagen
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid('juego_', true) . '.' . $ext;
    $uploadDir = '../../uploads/';  // Carpeta donde se subirán las imágenes
    $uploadFile = $uploadDir . $newFileName;

    // Mover la imagen al directorio de destino
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        // Guardar solo la ruta relativa de la imagen en la base de datos
        $filePath = 'uploads/' . $newFileName;

        try {
            $query = "INSERT INTO `imagenes`(`imagenes_img`, `imagenes_user`, `imagenes_ruta`, `imagenes_cord`) VALUES (?,?,?,?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("siii", $filePath, $idUser, $ruta, $cord);
            $stmt->execute();

            $query = "UPDATE `historialrutas` SET `hRuta_cord`=?, `hRuta_fechaUlt`=CURRENT_TIMESTAMP WHERE hRuta_user=? AND hRuta_ruta=?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("iii", $nextCordId, $idUser, $ruta);
            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $stmt->error]);
            }

            $stmt->close();
        } catch (Throwable $err) {
            echo json_encode(['success' => false, 'error' => $err->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al subir la imagen.']);
    }

} else {
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
}

?>