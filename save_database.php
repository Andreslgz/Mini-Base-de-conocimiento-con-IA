<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // Get the JSON input
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data');
    }
    
    // Validate that data is an array
    if (!is_array($data)) {
        throw new Exception('Data must be an array');
    }
    
    // Format the JSON nicely
    $jsonOutput = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    if ($jsonOutput === false) {
        throw new Exception('Failed to encode JSON');
    }
    
    // Write to database.json
    $result = file_put_contents('database.json', $jsonOutput);
    
    if ($result === false) {
        throw new Exception('Failed to write to database.json');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database updated successfully',
        'items_saved' => count($data)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
