<?php
require_once('../../cors.php');
require_once('../../connection.php');

$userId = $_GET['userId'];

try {
    $query = "SELECT * FROM historialrutas WHERE hRuta_user=?";
    $stmt = $connection->prepare($query);
    $stmt->bind_param("i", $userId);
    $pistas = [];
    $cords = [];
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        $resultados = [];

        while ($row = $result->fetch_assoc()) {
            $resultados[] = $row;
        }

        if (!empty($resultados)) {
            $cord = $resultados[0]['hRuta_cord'];
        } else {
            $cord = null;
        }

        if($cord > 0) {
            $query = "SELECT * FROM cords WHERE cords_id=?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $cord);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $cords[] = $row;
            }

            $query = "SELECT * FROM pistas WHERE pistas_cordenada=?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $cord);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $pistas[] = $row;
            }

            echo json_encode([
                'success' => true,
                'cords' => $cords,  
                'pistas' => $pistas 
            ]);
        } else {
            // Define $ruta antes de la consulta
            $ruta = 1;

            $query = "SELECT imagenes_img FROM imagenes WHERE imagenes_user = ? AND imagenes_ruta = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("ii", $userId, $ruta);
            $stmt->execute();
            $result = $stmt->get_result();

            $imagePaths = [];
            while ($row = $result->fetch_assoc()) {
                $imagePaths[] = '../../' . $row['imagenes_img'];  // Ruta completa de la imagen
            }
            $stmt->close();

            $imageCount = count($imagePaths);

            // Validación si no hay imágenes
            if ($imageCount > 0) {
                // Establecer las dimensiones del collage dinámicamente
                $imagesPerRow = ceil(sqrt($imageCount));  // Número de imágenes por fila
                $imageSize = 250;  // Tamaño de cada imagen (puedes ajustarlo)
                $collageWidth = $imagesPerRow * $imageSize;
                $collageHeight = ceil($imageCount / $imagesPerRow) * $imageSize;

                // Crear el lienzo del collage
                $collage = imagecreatetruecolor($collageWidth, $collageHeight);

                // Posicionar cada imagen en el collage
                foreach ($imagePaths as $index => $imagePath) {
                    if (file_exists($imagePath)) {
                        $image = @imagecreatefromjpeg($imagePath);  // Cargar la imagen (asumiendo que es JPG)
                        if ($image === false) {
                            continue;
                        }

                        // Redimensionar la imagen
                        $resizedImage = imagecreatetruecolor($imageSize, $imageSize);
                        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $imageSize, $imageSize, imagesx($image), imagesy($image));

                        // Calcular las coordenadas para colocar la imagen en el collage
                        $x = ($index % $imagesPerRow) * $imageSize;
                        $y = floor($index / $imagesPerRow) * $imageSize;
                        imagecopy($collage, $resizedImage, $x, $y, 0, 0, $imageSize, $imageSize);

                        imagedestroy($image);
                        imagedestroy($resizedImage);
                    }
                }

                // Convertir el collage en base64
                ob_start();
                imagejpeg($collage);
                $imageData = ob_get_contents();
                ob_end_clean();
                imagedestroy($collage);

                $base64Collage = base64_encode($imageData);

                // Enviar el collage como respuesta JSON
                echo json_encode([
                    'success' => true,
                    'cords' => [],  
                    'pistas' => [], 
                    'collage' => $base64Collage
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'cords' => [],  
                    'pistas' => [], 
                    'message' => 'No hay imágenes para crear un collage'
                ]);
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'cords' => [],  
            'pistas' => [], 
            'error' => $stmt->error
        ]);
    }

    $stmt->close();
} catch (\Throwable $err) {
    echo json_encode([
        'success' => false,
        'cords' => [],  
        'pistas' => [], 
        'error' => $err->getMessage()
    ]);
}
?>
