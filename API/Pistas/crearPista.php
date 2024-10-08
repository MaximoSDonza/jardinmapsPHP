<?php
require_once('../../cors.php');
require_once('../../connection.php');

if (isset($_FILES['file']) && isset($_POST['pistaCord'])) {
    $pistaDesc = $_POST['pistaDesc'];
    $pistaCord = $_POST['pistaCord'];
    $file = $_FILES['file'];

    // Verificar si el archivo es una imagen válida
    $allowedTypes = ['image/jpeg', 'image/png'];
    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'error' => 'Tipo de archivo no permitido.']);
        exit;
    }

    // Generar un nombre único para la imagen
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newFileName = uniqid('pista_', true) . '.' . $ext;
    $uploadDir = '../../uploads/';  // Carpeta donde se subirán las imágenes
    $uploadFile = $uploadDir . $newFileName;

    // Mover la imagen al directorio de destino
    if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
        // Guardar solo la ruta relativa de la imagen en la base de datos
        $filePath = 'uploads/' . $newFileName;

        try {
            $query = "INSERT INTO pistas (pistas_img, pistas_desc, pistas_cordenada) VALUES (?, ?, ?)";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("sss", $filePath, $pistaDesc, $pistaCord);

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