<?php
require_once('../../cors.php');
require_once('../../connection.php');
ini_set('memory_limit', '512');

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

            $query = "SELECT subquery.position FROM historialrutas hr INNER JOIN cords c ON hr.hRuta_cord = c.cords_id INNER JOIN users u ON hr.hRuta_user = u.users_id INNER JOIN ( SELECT cords_id, ROW_NUMBER() OVER (ORDER BY cords_id) AS position FROM cords WHERE cords_rutas = 1 ) AS subquery ON hr.hRuta_cord = subquery.cords_id WHERE hr.hRuta_ruta = 1 AND hr.hRuta_user = ?";
            $stmt = $connection->prepare($query);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $position = $row;
            }

            echo json_encode([
                'success' => true,
                'cords' => $cords,  
                'pistas' => $pistas,
                'position' => $position, 
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
                $imagesPerRow = ceil(sqrt($imageCount));  
                $imageSize = 250;  
                $collageWidth = $imagesPerRow * $imageSize;
                $collageHeight = ceil($imageCount / $imagesPerRow) * $imageSize;

                $collage = imagecreatetruecolor($collageWidth, $collageHeight);

                // Fondo transparente para PNGs
                $backgroundColor = imagecolorallocate($collage, 255, 255, 255); 
                imagefill($collage, 0, 0, $backgroundColor);

                foreach ($imagePaths as $index => $imagePath) {
                    if (file_exists($imagePath)) {
                        // Detectar el formato de la imagen
                        $imageInfo = getimagesize($imagePath);
                        $imageMime = $imageInfo['mime'];

                        switch ($imageMime) {
                            case 'image/jpeg':
                                $image = imagecreatefromjpeg($imagePath);
                                break;
                            case 'image/png':
                                $image = imagecreatefrompng($imagePath);
                                
                                // Habilitar la transparencia para las imágenes PNG
                                imagealphablending($image, true);
                                imagesavealpha($image, true);
                                
                                break;
                            default:
                                continue 2; // Saltar si el formato no es compatible
                        }

                        if ($image === false) {
                            continue;
                        }

                        // Crear una imagen redimensionada
                        $resizedImage = imagecreatetruecolor($imageSize, $imageSize);

                        // Si es PNG, preservar la transparencia en la imagen redimensionada
                        if ($imageMime === 'image/png') {
                            imagealphablending($resizedImage, false);
                            imagesavealpha($resizedImage, true);
                            $transparent = imagecolorallocatealpha($resizedImage, 0, 0, 0, 127);
                            imagefill($resizedImage, 0, 0, $transparent);
                        }

                        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $imageSize, $imageSize, imagesx($image), imagesy($image));

                        $x = ($index % $imagesPerRow) * $imageSize;
                        $y = floor($index / $imagesPerRow) * $imageSize;
                        imagecopy($collage, $resizedImage, $x, $y, 0, 0, $imageSize, $imageSize);

                        imagedestroy($image);
                        imagedestroy($resizedImage);
                    }
                }

                ob_start();
                imagepng($collage);  // Cambié a imagepng para manejar mejor la transparencia
                $imageData = ob_get_contents();
                ob_end_clean();
                imagedestroy($collage);

                $base64Collage = base64_encode($imageData);

                // Enviar el collage como respuesta JSON
                echo json_encode([
                    'success' => true,
                    'cords' => [],  
                    'pistas' => [],
                    'position' => '', 
                    'collage' => $base64Collage
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'cords' => [],  
                    'pistas' => [],
                    'position' => '', 
                    'message' => 'No hay imágenes para crear un collage'
                ]);
            }
        }
    } else {
        echo json_encode([
            'success' => false,
            'cords' => [],  
            'pistas' => [],
            'position' => '', 
            'error' => $stmt->error
        ]);
    }

} catch (\Throwable $err) {
    echo json_encode([
        'success' => false,
        'cords' => [],  
        'pistas' => [],
        'position' => '', 
        'error' => $err->getMessage()
    ]);
}
?>
