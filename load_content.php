<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $content = [];
    
    // Leer el archivo database.json si existe (más fácil de parsear)
    $jsonFile = 'database.json';
    if (file_exists($jsonFile)) {
        $jsonContent = file_get_contents($jsonFile);
        $jsonData = json_decode($jsonContent, true);
        
        if ($jsonData && is_array($jsonData)) {
            foreach ($jsonData as $entry) {
                if (isset($entry['title']) && isset($entry['content'])) {
                    // Crear un ID único basado en el título
                    $id = strtolower(trim($entry['title']));
                    $id = preg_replace('/[^a-z0-9\s]/', '', $id);
                    $id = str_replace(' ', '-', $id);
                    
                    $content[$id] = [
                        'title' => $entry['title'],
                        'content' => $entry['content'],
                        'timestamp' => isset($entry['timestamp']) ? $entry['timestamp'] : date('c')
                    ];
                }
            }
        }
    }
    
    // Si no existe database.json, intentar leer database.txt
    if (empty($content)) {
        $txtFile = 'database.txt';
        if (file_exists($txtFile)) {
            $txtContent = file_get_contents($txtFile);
            
            // Parsear el contenido del archivo de texto
            $entries = explode('================================================================================', $txtContent);
            
            foreach ($entries as $entry) {
                $entry = trim($entry);
                if (empty($entry) || strpos($entry, 'NUEVA ENTRADA') === false) {
                    continue;
                }
                
                // Extraer título
                if (preg_match('/TÍTULO:\s*(.+?)(?:\n|$)/s', $entry, $titleMatch)) {
                    $title = trim($titleMatch[1]);
                    
                    // Extraer contenido
                    if (preg_match('/CONTENIDO:\s*(.+?)(?=\n=|$)/s', $entry, $contentMatch)) {
                        $entryContent = trim($contentMatch[1]);
                        
                        // Crear ID único
                        $id = strtolower(trim($title));
                        $id = preg_replace('/[^a-z0-9\s]/', '', $id);
                        $id = str_replace(' ', '-', $id);
                        
                        // Extraer timestamp si existe
                        $timestamp = date('c');
                        if (preg_match('/NUEVA ENTRADA - (.+?)(?:\n|$)/', $entry, $timestampMatch)) {
                            $timestamp = date('c', strtotime($timestampMatch[1]));
                        }
                        
                        $content[$id] = [
                            'title' => $title,
                            'content' => $entryContent,
                            'timestamp' => $timestamp
                        ];
                    }
                }
            }
        }
    }
    
    // Devolver el contenido organizado por categorías
    $response = [
        'success' => true,
        'content' => $content,
        'categories' => [
            'renovaciones' => [],
            'documentacion' => [],
            'costos' => [],
            'horarios' => [],
            'especiales' => [],
            'faq' => [],
            'otros' => []
        ]
    ];
    
    // Categorizar automáticamente basado en palabras clave
    foreach ($content as $id => $item) {
        $title = strtolower($item['title']);
        
        if (strpos($title, 'renovación') !== false || strpos($title, 'renovar') !== false) {
            $response['categories']['renovaciones'][] = $id;
        } elseif (strpos($title, 'documento') !== false || strpos($title, 'formulario') !== false || strpos($title, 'certificado') !== false) {
            $response['categories']['documentacion'][] = $id;
        } elseif (strpos($title, 'costo') !== false || strpos($title, 'tarifa') !== false || strpos($title, 'precio') !== false || strpos($title, 'pago') !== false) {
            $response['categories']['costos'][] = $id;
        } elseif (strpos($title, 'horario') !== false || strpos($title, 'ubicación') !== false || strpos($title, 'centro') !== false || strpos($title, 'oficina') !== false) {
            $response['categories']['horarios'][] = $id;
        } elseif (strpos($title, 'duplicado') !== false || strpos($title, 'cambio') !== false || strpos($title, 'transferencia') !== false || strpos($title, 'suspensión') !== false) {
            $response['categories']['especiales'][] = $id;
        } elseif (strpos($title, '¿') !== false || strpos($title, 'cómo') !== false || strpos($title, 'qué') !== false) {
            $response['categories']['faq'][] = $id;
        } else {
            $response['categories']['otros'][] = $id;
        }
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log("Error en load_content.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'Error al cargar contenido: ' . $e->getMessage(),
        'content' => [],
        'categories' => []
    ]);
}
?>