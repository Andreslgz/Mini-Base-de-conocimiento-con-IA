<?php
header('Content-Type: application/json');

// Configuración de subida de archivos
$uploadDir = 'uploads/';
$maxFileSize = 5 * 1024 * 1024; // 5MB
$allowedTypes = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];

// Crear directorio de uploads si no existe
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function uploadFiles($files) {
    global $uploadDir, $maxFileSize, $allowedTypes;
    
    $uploadedFiles = [];
    $errors = [];
    
    if (!isset($files['name']) || !is_array($files['name'])) {
        return ['files' => [], 'errors' => []];
    }
    
    $fileCount = count($files['name']);
    
    for ($i = 0; $i < $fileCount; $i++) {
        // Verificar si hay archivo
        if ($files['error'][$i] === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        
        // Verificar errores de subida
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            $errors[] = "Error al subir archivo: " . $files['name'][$i];
            continue;
        }
        
        // Verificar tamaño
        if ($files['size'][$i] > $maxFileSize) {
            $errors[] = "Archivo muy grande: " . $files['name'][$i] . " (máximo 5MB)";
            continue;
        }
        
        // Verificar tipo de archivo
        $fileExtension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = "Tipo de archivo no permitido: " . $files['name'][$i];
            continue;
        }
        
        // Generar nombre único
        $fileName = uniqid() . '_' . time() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        // Mover archivo
        if (move_uploaded_file($files['tmp_name'][$i], $filePath)) {
            $uploadedFiles[] = [
                'original_name' => $files['name'][$i],
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_size' => $files['size'][$i],
                'file_type' => $fileExtension,
                'upload_date' => date('Y-m-d H:i:s')
            ];
        } else {
            $errors[] = "Error al guardar archivo: " . $files['name'][$i];
        }
    }
    
    return ['files' => $uploadedFiles, 'errors' => $errors];
}

// Procesar subida si es POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $result = uploadFiles($_FILES['files']);
    echo json_encode($result);
} else {
    echo json_encode(['files' => [], 'errors' => ['No se recibieron archivos']]);
}
?>
