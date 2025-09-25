<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar que sea una petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
    exit;
}

try {
    // Obtener los datos JSON del request
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    // Validar datos requeridos
    if (!isset($data['title']) || !isset($data['content'])) {
        echo json_encode(['success' => false, 'error' => 'Faltan datos requeridos']);
        exit;
    }
    
    $title = trim($data['title']);
    $content = trim($data['content']);
    $timestamp = isset($data['timestamp']) ? $data['timestamp'] : date('c');
    
    // Validar que no estén vacíos
    if (empty($title) || empty($content)) {
        echo json_encode(['success' => false, 'error' => 'Título y contenido no pueden estar vacíos']);
        exit;
    }
    
    // Preparar los datos para guardar
    $entry = [
        'timestamp' => $timestamp,
        'title' => $title,
        'content' => $content,
        'id' => strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $title)),
        'id' => str_replace(' ', '-', $entry['id'])
    ];
    
    // Crear el contenido formateado para el archivo
    $fileContent = "\n" . str_repeat('=', 80) . "\n";
    $fileContent .= "NUEVA ENTRADA - " . date('Y-m-d H:i:s', strtotime($timestamp)) . "\n";
    $fileContent .= str_repeat('=', 80) . "\n";
    $fileContent .= "TÍTULO: " . $title . "\n";
    $fileContent .= str_repeat('-', 40) . "\n";
    $fileContent .= "CONTENIDO:\n";
    $fileContent .= $content . "\n";
    $fileContent .= str_repeat('=', 80) . "\n";
    
    // Intentar escribir al archivo database.txt
    $filename = 'database.txt';
    
    // Crear el archivo si no existe
    if (!file_exists($filename)) {
        $initialContent = "BASE DE CONOCIMIENTOS CESCO - ARCHIVO DE DATOS\n";
        $initialContent .= "Generado automáticamente - " . date('Y-m-d H:i:s') . "\n";
        $initialContent .= str_repeat('=', 80) . "\n";
        file_put_contents($filename, $initialContent);
    }
    
    // Agregar la nueva entrada al archivo
    $result = file_put_contents($filename, $fileContent, FILE_APPEND | LOCK_EX);
    
    if ($result === false) {
        echo json_encode(['success' => false, 'error' => 'No se pudo escribir al archivo database.txt']);
        exit;
    }
    
    // También guardar en formato JSON para facilitar la lectura programática
    $jsonFilename = 'database.json';
    $jsonData = [];
    
    // Leer datos existentes si el archivo existe
    if (file_exists($jsonFilename)) {
        $existingJson = file_get_contents($jsonFilename);
        $jsonData = json_decode($existingJson, true) ?: [];
    }
    
    // Agregar nueva entrada
    $jsonData[] = $entry;
    
    // Guardar el archivo JSON actualizado
    file_put_contents($jsonFilename, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true, 
        'message' => 'Información guardada exitosamente',
        'entry_id' => $entry['id']
    ]);
    
} catch (Exception $e) {
    error_log("Error en save_info.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error interno del servidor: ' . $e->getMessage()]);
}
?>