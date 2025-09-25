<?php
// Evitar cualquier output antes de headers
ob_start();

// Configurar manejo de errores para evitar HTML
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json; charset=utf-8');
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['pregunta'])) {
    echo json_encode(['respuesta' => 'Pregunta no recibida.']);
    exit;
}
$pregunta = trim($_POST['pregunta']);

try {

// Función para buscar información relevante en la base de datos
function buscarInformacionRelevante($pregunta, $datos_json) {
    $pregunta_lower = strtolower($pregunta);
    $resultados_relevantes = [];
    
    if (!$datos_json || !isset($datos_json['topics'])) {
        return '';
    }
    
    // Palabras clave comunes en consultas CESCO
    $palabras_clave = [
        'licencia', 'conducir', 'renovar', 'renovación', 'marbete', 'registro', 
        'vehicular', 'costo', 'precio', 'tarifa', 'documento', 'requisito',
        'horario', 'ubicación', 'cesco', 'cita', 'duplicado', 'cambio', 'dirección'
    ];
    
    foreach ($datos_json['topics'] as $tema) {
        $relevancia = 0;
        $titulo_lower = strtolower($tema['title']);
        $contenido_lower = strtolower(strip_tags($tema['content']));
        
        // Buscar coincidencias exactas en título (mayor peso)
        if (strpos($titulo_lower, $pregunta_lower) !== false) {
            $relevancia += 10;
        }
        
        // Buscar palabras de la pregunta en título
        $palabras_pregunta = explode(' ', $pregunta_lower);
        foreach ($palabras_pregunta as $palabra) {
            if (strlen($palabra) > 3) {
                if (strpos($titulo_lower, $palabra) !== false) {
                    $relevancia += 5;
                }
                if (strpos($contenido_lower, $palabra) !== false) {
                    $relevancia += 2;
                }
            }
        }
        
        // Buscar palabras clave específicas de CESCO
        foreach ($palabras_clave as $clave) {
            if (strpos($pregunta_lower, $clave) !== false) {
                if (strpos($titulo_lower, $clave) !== false) {
                    $relevancia += 8;
                }
                if (strpos($contenido_lower, $clave) !== false) {
                    $relevancia += 4;
                }
            }
        }
        
        if ($relevancia > 0) {
            $resultados_relevantes[] = [
                'tema' => $tema,
                'relevancia' => $relevancia
            ];
        }
    }
    
    // Ordenar por relevancia
    usort($resultados_relevantes, function($a, $b) {
        return $b['relevancia'] - $a['relevancia'];
    });
    
    // Retornar los 3 más relevantes
    $informacion_relevante = "\n\nINFORMACIÓN MÁS RELEVANTE PARA TU CONSULTA:\n\n";
    $contador = 0;
    
    foreach ($resultados_relevantes as $resultado) {
        if ($contador >= 3) break;
        
        $tema = $resultado['tema'];
        $categoria_nombre = "General";
        
        // Buscar nombre de categoría
        if (isset($tema['categoryId']) && isset($datos_json['categories'])) {
            foreach ($datos_json['categories'] as $cat) {
                if ($cat['id'] === $tema['categoryId']) {
                    $categoria_nombre = $cat['title'];
                    break;
                }
            }
        }
        
        $informacion_relevante .= "📋 TEMA: {$tema['title']}\n";
        $informacion_relevante .= "📂 CATEGORÍA: {$categoria_nombre}\n";
        $informacion_relevante .= "📝 INFORMACIÓN: " . strip_tags($tema['content']) . "\n\n";
        $informacion_relevante .= "---\n\n";
        
        $contador++;
    }
    
    return $informacion_relevante;
}

// Función para formatear respuesta
function formatearRespuesta($texto) {
    // Convertir saltos de línea a <br>
    $texto = nl2br($texto);
    
    // Formatear listas con bullets
    $texto = preg_replace('/^[\*\-]\s+(.+)$/m', '<li>$1</li>', $texto);
    $texto = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $texto);
    
    // Formatear números de lista
    $texto = preg_replace('/^(\d+[\.\)]\s+)(.+)$/m', '<li class="numbered">$2</li>', $texto);
    $texto = preg_replace('/(<li class="numbered">.*<\/li>)/s', '<ol>$1</ol>', $texto);
    
    // Formatear texto en negrita (**texto**)
    $texto = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $texto);
    
    // Formatear texto en cursiva (*texto*)
    $texto = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $texto);
    
    // Formatear títulos (# Título)
    $texto = preg_replace('/^# (.+)$/m', '<h3>$1</h3>', $texto);
    $texto = preg_replace('/^## (.+)$/m', '<h4>$1</h4>', $texto);
    
    // Formatear código (`código`)
    $texto = preg_replace('/`(.*?)`/', '<code>$1</code>', $texto);
    
    // Agregar wrapper con estilos
    $html = '<div class="respuesta-formateada">' . $texto . '</div>';
    
    return $html;
}

// Función para consultar Gemini
function consultarGemini($prompt, $apiKey) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return null;
    }
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }
    
    return null;
}

// Función para consultar ChatGPT
function consultarChatGPT($prompt, $apiKey) {
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = [
        'model' => 'gpt-4o',
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ],
        'max_tokens' => 2000,
        'temperature' => 0.7
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return null;
    }
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['choices'][0]['message']['content'])) {
        return $result['choices'][0]['message']['content'];
    }
    
    return null;
}

// Función para consultar Claude
function consultarClaude($prompt, $apiKey) {
    $url = 'https://api.anthropic.com/v1/messages';
    $data = [
        'model' => 'claude-3-5-sonnet-20241022',
        'max_tokens' => 2000,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . $apiKey,
        'anthropic-version: 2023-06-01'
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return null;
    }
    
    if ($httpCode !== 200) {
        return null;
    }
    
    $result = json_decode($response, true);
    
    if (isset($result['content'][0]['text'])) {
        return $result['content'][0]['text'];
    }
    
    return null;
}

// Cargar y procesar base de datos JSON
$datos_json = null;
if (file_exists('database.json')) {
    $contenido_json = file_get_contents('database.json');
    $datos_json = json_decode($contenido_json, true);
}

// Buscar información relevante para la pregunta
$informacionRelevante = $datos_json ? buscarInformacionRelevante($pregunta, $datos_json) : '';

// Leer configuración
$config = [];
if (file_exists('config.json')) {
    $config_content = file_get_contents('config.json');
    $config = json_decode($config_content, true) ?? [];
}

// Determinar qué IA usar
$selectedAI = $config['selected_ai'] ?? 'gemini';
$apiKey = '';
$customPrompt = '';

switch ($selectedAI) {
    case 'chatgpt':
        $apiKey = $config['chatgpt_api_key'] ?? '';
        $customPrompt = $config['chatgpt_prompt'] ?? '';
        break;
    case 'claude':
        $apiKey = $config['claude_api_key'] ?? '';
        $customPrompt = $config['claude_prompt'] ?? '';
        break;
    case 'gemini':
    default:
        $apiKey = $config['gemini_api_key'] ?? '';
        $customPrompt = $config['gemini_prompt'] ?? '';
        break;
}

if (empty($apiKey)) {
    ob_clean();
    echo json_encode(['respuesta' => '<div class="error">❌ API Key no configurada para ' . ucfirst($selectedAI) . '. Ve a Configuración para agregar tu API Key.</div>']);
    exit;
}

// Crear prompt completo
$promptCompleto = "Hola, soy Ana, tu asistente virtual para trámites CESCO en Puerto Rico. Estoy aquí para ayudarte.

Eres un asistente especializado exclusivamente en trámites y servicios de CESCO Puerto Rico. SOLO puedes responder preguntas relacionadas con la información oficial disponible en los siguientes portales:
	•	https://www.cesco.pr.gov
	•	https://www.cesco.pr.gov/solicitudes-y-formularios
	•	https://ayudalegalpr.org
	•	https://www.smarttransportation.pr.gov
	•	https://cesco.turnospr.com
	•	https://cescocitaspr.com
	•	https://cescoonline.com
	•	https://cescoonline.com/list-services

INSTRUCCIONES PRINCIPALES:
	1.	Responde únicamente consultas sobre trámites, licencias, tablillas y servicios de CESCO Puerto Rico.
	2.	Si la pregunta NO está relacionada con CESCO o trámites de Puerto Rico, responde:
\"Lo siento, solo puedo ayudarte con consultas sobre trámites CESCO de Puerto Rico. Por favor, reformula tu pregunta sobre licencias, tablillas, renovaciones o servicios de CESCO.\"
	3.	Basa tus respuestas exclusivamente en la información oficial de CESCO y en la información adicional proporcionada.
	4.	Si no dispones de información específica, indica al usuario que debe contactar directamente a CESCO.
	5.	Prioriza siempre la información más relevante, verificable y actualizada.
	6.	Ofrece respuestas claras, precisas y detalladas, citando la fuente oficial siempre que sea posible.
	7.	Si existe información oficial aplicable, úsala como base principal de tu respuesta.

" . $informacionRelevante . "

Pregunta del usuario: " . $pregunta;

// Consultar la IA seleccionada
$respuestaTexto = null;

switch ($selectedAI) {
    case 'chatgpt':
        $respuestaTexto = consultarChatGPT($promptCompleto, $apiKey);
        break;
    case 'claude':
        $respuestaTexto = consultarClaude($promptCompleto, $apiKey);
        break;
    case 'gemini':
    default:
        $respuestaTexto = consultarGemini($promptCompleto, $apiKey);
        break;
}

if ($respuestaTexto) {
    $respuesta = formatearRespuesta($respuestaTexto);
} else {
    $respuesta = '<div class="error">❌ No se pudo obtener respuesta de ' . ucfirst($selectedAI) . '.</div>';
}

// Limpiar buffer y enviar respuesta
ob_clean();
echo json_encode(['respuesta' => $respuesta]);

} catch (Exception $e) {
    ob_clean();
    echo json_encode(['respuesta' => '<div class="error">❌ Error interno del servidor. Intenta nuevamente.</div>']);
}
exit;
?>
