<?php
session_start();

// Credenciales de acceso
$valid_username = 'admin';
$valid_password = 'pr@2025';

// Procesar login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if ($username === $valid_username && $password === $valid_password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $error_message = 'Usuario o contraseña incorrectos';
    }
}

// Procesar logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Procesar guardado de configuración
if (isset($_POST['action']) && $_POST['action'] === 'save_config') {
    $config = [
        'gemini_api_key' => $_POST['gemini_api_key'] ?? '',
        'gemini_prompt' => $_POST['gemini_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'
    ];
    
    file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
    $_SESSION['config_saved'] = true;
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Cargar configuración
$config = [];
if (file_exists('config.json')) {
    $config = json_decode(file_get_contents('config.json'), true) ?? [];
}

// Verificar si está logueado
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CESCO Online - Centro de Ayuda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#1e40af',
                        'primary-dark': '#1e3a8a',
                        'sidebar': '#f8fafc',
                        'sidebar-hover': '#e2e8f0',
                    },
                    fontFamily: {
                        'inter': ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .sidebar-item:hover { background-color: #e2e8f0; }
        .sidebar-item.active { background-color: #dbeafe; color: #1e40af; }
        .content-area { min-height: calc(100vh - 64px); }
        .login-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        @keyframes blink {
            0%, 50% { opacity: 1; }
            51%, 100% { opacity: 0.3; }
        }
        .typing-dots span {
            animation: blink 1.4s infinite;
        }
        .typing-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }
        .typing-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }
    </style>
</head>
<body class="bg-gray-50 font-inter">

<?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container min-h-screen flex items-center justify-center">
        <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
            <div class="text-center mb-8">
                <div class="mb-6">
                    <img src="https://cescoonline.com/assets/images/logo.png" alt="CESCO Online" 
                         class="mx-auto max-w-32 h-auto" />
                </div>
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Acceso al Sistema</h1>
                <p class="text-gray-600">Base de Conocimientos CESCO</p>
            </div>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        Usuario
                    </label>
                    <input type="text" id="username" name="username" required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-cesco-green focus:outline-none transition-colors"
                           placeholder="Ingrese su usuario">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <input type="password" id="password" name="password" required
                           class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-cesco-green focus:outline-none transition-colors"
                           placeholder="Ingrese su contraseña">
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-cesco-green to-cesco-light text-white py-3 px-4 rounded-lg font-semibold hover:opacity-90 transition-opacity shadow-lg">
                    Iniciar Sesión
                </button>
            </form>

            <div class="mt-8 text-center">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">
                        <strong>Credenciales de acceso:</strong><br>
                        Usuario: admin<br>
                        Contraseña: pr@2025
                    </p>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <!-- Header tipo help.tawk.to -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="flex items-center justify-between px-6 py-4">
            <div class="flex items-center space-x-4">
                <img src="https://cescoonline.com/assets/images/logo.png" alt="CESCO Online" class="h-8 w-auto">
                <div class="hidden md:block">
                    <h1 class="text-xl font-semibold text-gray-900">Centro de Ayuda CESCO</h1>
                    <p class="text-sm text-gray-500">Base de conocimientos y asistencia</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Search -->
                <div class="relative hidden md:block">
                    <input type="text" id="headerSearch" 
                           class="w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                           placeholder="Buscar en la base de conocimientos...">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-600">Hola, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-2 text-gray-600 hover:text-gray-900">
                            <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <i class="fas fa-chevron-down text-xs"></i>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                            <a href="#" onclick="showSection('settings')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Configuración
                            </a>
                            <hr class="my-1">
                            <a href="?action=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Layout Principal -->
    <div class="flex h-screen pt-16">
        <!-- Sidebar -->
        <aside class="w-64 bg-sidebar border-r border-gray-200 overflow-y-auto">
            <nav class="p-4">
                <div class="space-y-2">
                    <button onclick="showSection('dashboard')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-home mr-3 text-gray-400"></i>
                        <span>Inicio</span>
                    </button>
                    
                    <button onclick="showSection('categories')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-folder mr-3 text-gray-400"></i>
                        <span>Categorías</span>
                    </button>
                    
                    <button onclick="showSection('content')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                        <span>Contenido</span>
                    </button>
                    
                    <button onclick="showSection('search')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-search mr-3 text-gray-400"></i>
                        <span>Búsqueda Avanzada</span>
                    </button>
                    
                    <button onclick="showSection('chat')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-robot mr-3 text-gray-400"></i>
                        <span>Asistente IA</span>
                    </button>
                    
                    <hr class="my-4">
                    
                    <button onclick="showSection('settings')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors">
                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                        <span>Configuración</span>
                    </button>
                </div>
            </nav>
        </aside>

        <!-- Contenido Principal -->
        <main class="flex-1 overflow-y-auto">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section p-6">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-2">¡Bienvenido al Centro de Ayuda CESCO!</h2>
                        <p class="text-gray-600">Encuentra toda la información que necesitas sobre trámites vehiculares en Puerto Rico.</p>
                    </div>

                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-blue-100 rounded-lg">
                                    <i class="fas fa-folder text-blue-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Categorías</p>
                                    <p id="categoriesCount" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-green-100 rounded-lg">
                                    <i class="fas fa-file-alt text-green-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Artículos</p>
                                    <p id="articlesCount" class="text-2xl font-bold text-gray-900">0</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <div class="flex items-center">
                                <div class="p-3 bg-purple-100 rounded-lg">
                                    <i class="fas fa-robot text-purple-600"></i>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Asistente IA</p>
                                    <p class="text-sm font-bold text-gray-900" id="aiStatus">Configurar</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <button onclick="openAddCategoryModal()" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>
                                <span class="text-sm font-medium">Nueva Categoría</span>
                            </button>
                            
                            <button onclick="openAddTopicModal()" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-plus text-green-600 mr-3"></i>
                                <span class="text-sm font-medium">Nuevo Artículo</span>
                            </button>
                            
                            <button onclick="showSection('chat')" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-robot text-purple-600 mr-3"></i>
                                <span class="text-sm font-medium">Abrir Chat IA</span>
                            </button>
                            
                            <button onclick="showSection('settings')" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cog text-gray-600 mr-3"></i>
                                <span class="text-sm font-medium">Configuración</span>
                            </button>
                        </div>
                    </div>

                    <!-- Recent Content -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contenido Reciente</h3>
                        <div id="recentContent" class="space-y-4">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div id="categories-section" class="content-section hidden p-6">
                <div class="max-w-6xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Categorías</h2>
                            <p class="text-gray-600">Organiza tu contenido por categorías</p>
                        </div>
                        <button onclick="openAddCategoryModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nueva Categoría
                        </button>
                    </div>
                    
                    <div id="categoriesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Se llenará dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div id="content-section" class="content-section hidden p-6">
                <div class="max-w-6xl mx-auto">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">Gestión de Contenido</h2>
                            <p class="text-gray-600">Administra todos tus artículos y documentos</p>
                        </div>
                        <button onclick="openAddTopicModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                            <i class="fas fa-plus mr-2"></i>Nuevo Artículo
                        </button>
                    </div>
                    
                    <!-- Filters -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="flex flex-wrap items-center gap-4">
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Ordenar:</label>
                                <select id="sortBy" onchange="updateContentList()" class="border border-gray-300 rounded px-3 py-1 text-sm">
                                    <option value="date">Por Fecha</option>
                                    <option value="category">Por Categoría</option>
                                    <option value="title">Por Título</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <label class="text-sm font-medium text-gray-700">Filtrar:</label>
                                <select id="filterCategory" onchange="updateContentList()" class="border border-gray-300 rounded px-3 py-1 text-sm">
                                    <option value="all">Todas las Categorías</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div id="contentListContainer" class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <!-- Se llenará dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div id="search-section" class="content-section hidden p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Búsqueda Avanzada</h2>
                        <p class="text-gray-600">Encuentra información específica en toda la base de conocimientos</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <div class="relative">
                            <input type="text" id="searchInput" 
                                   class="w-full pl-12 pr-4 py-4 text-lg border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                   placeholder="Buscar información sobre trámites CESCO...">
                            <i class="fas fa-search absolute left-4 top-5 text-gray-400"></i>
                            <button onclick="searchKnowledge()" 
                                    class="absolute right-2 top-2 bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                                Buscar
                            </button>
                        </div>
                    </div>
                    
                    <div id="searchResults" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="text-center text-gray-500 py-8">
                            <i class="fas fa-search text-4xl mb-4"></i>
                            <p>Ingresa un término de búsqueda para comenzar</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chat Section -->
            <div id="chat-section" class="content-section hidden p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Asistente Virtual ANA</h2>
                        <p class="text-gray-600">Chatea con nuestro asistente especializado en trámites CESCO</p>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 h-96 flex flex-col">
                        <div class="flex-1 p-4 overflow-y-auto" id="chatContainer">
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <div class="flex items-center mb-2">
                                    <div class="w-8 h-8 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-robot text-sm"></i>
                                    </div>
                                    <span class="font-semibold text-gray-900">ANA - Asistente Virtual</span>
                                </div>
                                <p class="text-gray-700">¡Hola! Soy ANA, tu asistente virtual especializada en trámites de CESCO. ¿En qué puedo ayudarte hoy?</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 p-4">
                            <form id="chatForm" class="flex space-x-2">
                                <input type="text" id="chatInput" 
                                       class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Escribe tu pregunta aquí..." required>
                                <button type="submit" 
                                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="content-section hidden p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">Configuración</h2>
                        <p class="text-gray-600">Configura tu asistente IA y otras preferencias del sistema</p>
                    </div>

                    <?php if (isset($_SESSION['config_saved'])): ?>
                        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
                            <div class="flex items-center">
                                <i class="fas fa-check-circle mr-2"></i>
                                Configuración guardada exitosamente
                            </div>
                        </div>
                        <?php unset($_SESSION['config_saved']); ?>
                    <?php endif; ?>

                    <!-- Gemini Configuration -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-robot mr-2 text-primary"></i>
                            Configuración del Asistente IA (Gemini)
                        </h3>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="save_config">
                            
                            <div>
                                <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-key mr-1"></i>
                                    API Key de Google Gemini
                                </label>
                                <input type="password" id="gemini_api_key" name="gemini_api_key" 
                                       value="<?php echo htmlspecialchars($config['gemini_api_key'] ?? ''); ?>"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                       placeholder="Ingresa tu API Key de Google Gemini">
                                <p class="mt-2 text-sm text-gray-500">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Obtén tu API Key gratuita en <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-primary hover:underline">Google AI Studio</a>
                                </p>
                            </div>
                            
                            <div>
                                <label for="gemini_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-comment-dots mr-1"></i>
                                    Prompt Personalizado del Asistente
                                </label>
                                <textarea id="gemini_prompt" name="gemini_prompt" rows="6"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"
                                          placeholder="Define cómo debe comportarse tu asistente IA..."><?php echo htmlspecialchars($config['gemini_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'); ?></textarea>
                                <p class="mt-2 text-sm text-gray-500">
                                    <i class="fas fa-lightbulb mr-1"></i>
                                    Define la personalidad y el conocimiento específico de tu asistente IA
                                </p>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <div class="w-3 h-3 rounded-full <?php echo !empty($config['gemini_api_key']) ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                                    <span class="text-sm text-gray-600">
                                        Estado: <?php echo !empty($config['gemini_api_key']) ? 'Configurado' : 'No configurado'; ?>
                                    </span>
                                </div>
                                
                                <button type="submit" 
                                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-save mr-2"></i>
                                    Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- System Information -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-info-circle mr-2 text-primary"></i>
                            Información del Sistema
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-700">Versión del Sistema</div>
                                <div class="text-lg font-semibold text-gray-900">CESCO v2.0</div>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-700">Usuario Activo</div>
                                <div class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['username']); ?></div>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-700">Base de Datos</div>
                                <div class="text-lg font-semibold text-gray-900">JSON Local</div>
                            </div>
                            
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="text-sm font-medium text-gray-700">IA Configurada</div>
                                <div class="text-lg font-semibold text-gray-900"><?php echo !empty($config['gemini_api_key']) ? 'Sí' : 'No'; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Modals y JavaScript -->
    <script>
        // Variables globales
        let allCategories = {};
        let dynamicContent = [];
        let currentEditingTopic = null;

        // Configuración de Gemini
        const geminiConfig = {
            apiKey: '<?php echo htmlspecialchars($config['gemini_api_key'] ?? ''); ?>',
            prompt: '<?php echo htmlspecialchars($config['gemini_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'); ?>'
        };

        // Funciones de navegación
        function showSection(sectionName) {
            // Ocultar todas las secciones
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.add('hidden');
            });
            
            // Remover clase active de todos los botones del sidebar
            document.querySelectorAll('.sidebar-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Mostrar la sección seleccionada
            const targetSection = document.getElementById(sectionName + '-section');
            if (targetSection) {
                targetSection.classList.remove('hidden');
            }
            
            // Agregar clase active al botón correspondiente
            const activeButton = document.querySelector(`[onclick="showSection('${sectionName}')"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }

            // Cargar contenido específico según la sección
            switch(sectionName) {
                case 'dashboard':
                    updateDashboardStats();
                    loadRecentContent();
                    break;
                case 'categories':
                    loadCategoriesGrid();
                    break;
                case 'content':
                    updateContentList();
                    break;
            }
        }

        function toggleUserMenu() {
            const menu = document.getElementById('userMenu');
            menu.classList.toggle('hidden');
        }

        // Funciones del dashboard
        function updateDashboardStats() {
            document.getElementById('categoriesCount').textContent = Object.keys(allCategories).length;
            document.getElementById('articlesCount').textContent = dynamicContent.length;
            document.getElementById('aiStatus').textContent = geminiConfig.apiKey ? 'Configurado' : 'Configurar';
        }

        function loadRecentContent() {
            const recentContainer = document.getElementById('recentContent');
            const recentItems = dynamicContent.slice(0, 5);
            
            if (recentItems.length === 0) {
                recentContainer.innerHTML = '<p class="text-gray-500 text-center py-4">No hay contenido reciente</p>';
                return;
            }
            
            recentContainer.innerHTML = recentItems.map(item => `
                <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">${item.title}</h4>
                        <p class="text-sm text-gray-500">${new Date(item.timestamp).toLocaleDateString('es-ES')}</p>
                    </div>
                    <button onclick="openTopicModal('${item.id}')" class="text-primary hover:text-primary-dark">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            `).join('');
        }

        // Funciones de categorías
        function loadCategoriesGrid() {
            const grid = document.getElementById('categoriesGrid');
            
            if (Object.keys(allCategories).length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">No hay categorías creadas</p>
                        <button onclick="openAddCategoryModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">
                            Crear Primera Categoría
                        </button>
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = Object.entries(allCategories).map(([id, category]) => `
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-3xl">${category.icon}</div>
                        <button onclick="editCategory('${id}')" class="text-gray-400 hover:text-gray-600">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">${category.title}</h3>
                    <p class="text-gray-600 text-sm mb-4">${category.description}</p>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">${getTopicsInCategory(id).length} artículos</span>
                        <button onclick="openCategoryModal('${id}')" class="text-primary hover:text-primary-dark text-sm font-medium">
                            Ver contenido
                        </button>
                    </div>
                </div>
            `).join('');
        }

        function getTopicsInCategory(categoryId) {
            return dynamicContent.filter(topic => topic.categoryId === categoryId);
        }

        // Funciones de contenido
        function updateContentList() {
            const container = document.getElementById('contentListContainer');
            const sortBy = document.getElementById('sortBy').value;
            const filterCategory = document.getElementById('filterCategory').value;
            
            let filteredContent = [...dynamicContent];
            
            // Filtrar por categoría
            if (filterCategory !== 'all') {
                filteredContent = filteredContent.filter(item => item.categoryId === filterCategory);
            }
            
            // Ordenar
            filteredContent.sort((a, b) => {
                switch(sortBy) {
                    case 'date':
                        return new Date(b.timestamp) - new Date(a.timestamp);
                    case 'category':
                        const catA = allCategories[a.categoryId]?.title || 'Sin categoría';
                        const catB = allCategories[b.categoryId]?.title || 'Sin categoría';
                        return catA.localeCompare(catB);
                    case 'title':
                        return a.title.localeCompare(b.title);
                    default:
                        return 0;
                }
            });
            
            if (filteredContent.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <i class="fas fa-file-alt text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">No hay contenido disponible</p>
                        <button onclick="openAddTopicModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">
                            Crear Primer Artículo
                        </button>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <div class="divide-y divide-gray-200">
                    ${filteredContent.map(item => `
                        <div class="p-4 hover:bg-gray-50 flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-3 mb-2">
                                    <h4 class="font-medium text-gray-900">${item.title}</h4>
                                    ${item.categoryId && allCategories[item.categoryId] ? `
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            ${allCategories[item.categoryId].icon} ${allCategories[item.categoryId].title}
                                        </span>
                                    ` : ''}
                                </div>
                                <p class="text-sm text-gray-500">${new Date(item.timestamp).toLocaleDateString('es-ES')} • ${item.content.substring(0, 100)}...</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="openTopicModal('${item.id}')" class="text-primary hover:text-primary-dark">
                                    <i class="fas fa-eye"></i>
                                </button>
                                <button onclick="editTopic('${item.id}')" class="text-gray-600 hover:text-gray-800">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Funciones de búsqueda
        function searchKnowledge() {
            const query = document.getElementById('searchInput').value.trim();
            if (!query) return;
            
            const results = dynamicContent.filter(item => 
                item.title.toLowerCase().includes(query.toLowerCase()) ||
                item.content.toLowerCase().includes(query.toLowerCase())
            );
            
            displaySearchResults(results, query);
        }

        function displaySearchResults(results, query) {
            const container = document.getElementById('searchResults');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-search text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500">No se encontraron resultados para "${query}"</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = `
                <div class="mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Resultados para "${query}" (${results.length})</h3>
                </div>
                <div class="space-y-4">
                    ${results.map(item => `
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer" onclick="openTopicModal('${item.id}')">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="font-medium text-gray-900">${item.title}</h4>
                                ${item.categoryId && allCategories[item.categoryId] ? `
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        ${allCategories[item.categoryId].icon} ${allCategories[item.categoryId].title}
                                    </span>
                                ` : ''}
                            </div>
                            <p class="text-sm text-gray-600">${item.content.substring(0, 200)}...</p>
                            <p class="text-xs text-gray-500 mt-2">${new Date(item.timestamp).toLocaleDateString('es-ES')}</p>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Funciones de chat con Gemini
        async function sendChatMessage(message) {
            if (!geminiConfig.apiKey) {
                addChatMessage('Sistema', 'Por favor configura tu API Key de Gemini en la sección de Configuración.', 'system');
                return;
            }

            try {
                addChatMessage('Usuario', message, 'user');
                addTypingIndicator();

                const response = await fetch(`https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${geminiConfig.apiKey}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        contents: [{
                            parts: [{
                                text: `${geminiConfig.prompt}\n\nUsuario: ${message}\n\nContexto de la base de datos: ${JSON.stringify(dynamicContent.slice(0, 5))}`
                            }]
                        }]
                    })
                });

                const data = await response.json();
                removeTypingIndicator();

                if (data.candidates && data.candidates[0]) {
                    const aiResponse = data.candidates[0].content.parts[0].text;
                    addChatMessage('ANA', aiResponse, 'ai');
                } else {
                    addChatMessage('Sistema', 'Error al obtener respuesta de Gemini. Verifica tu API Key.', 'system');
                }
            } catch (error) {
                removeTypingIndicator();
                addChatMessage('Sistema', 'Error de conexión con Gemini: ' + error.message, 'system');
            }
        }

        function addChatMessage(sender, message, type) {
            const container = document.getElementById('chatContainer');
            const messageDiv = document.createElement('div');
            
            let bgColor = 'bg-gray-100';
            let textColor = 'text-gray-800';
            let icon = 'fas fa-user';
            
            if (type === 'ai') {
                bgColor = 'bg-blue-50 border border-blue-200';
                icon = 'fas fa-robot';
            } else if (type === 'user') {
                bgColor = 'bg-primary text-white';
                textColor = 'text-white';
            } else if (type === 'system') {
                bgColor = 'bg-red-50 border border-red-200';
                textColor = 'text-red-800';
                icon = 'fas fa-exclamation-triangle';
            }
            
            messageDiv.className = `${bgColor} rounded-lg p-4 mb-4 ${textColor}`;
            messageDiv.innerHTML = `
                <div class="flex items-center mb-2">
                    <div class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                        <i class="${icon} text-xs"></i>
                    </div>
                    <span class="font-semibold text-sm">${sender}</span>
                </div>
                <p class="whitespace-pre-wrap">${message}</p>
            `;
            
            container.appendChild(messageDiv);
            container.scrollTop = container.scrollHeight;
        }

        function addTypingIndicator() {
            const container = document.getElementById('chatContainer');
            const typingDiv = document.createElement('div');
            typingDiv.id = 'typing-indicator';
            typingDiv.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4';
            typingDiv.innerHTML = `
                <div class="flex items-center">
                    <div class="w-6 h-6 bg-primary text-white rounded-full flex items-center justify-center mr-3">
                        <i class="fas fa-robot text-xs"></i>
                    </div>
                    <span class="font-semibold text-sm mr-2">ANA está escribiendo</span>
                    <div class="typing-dots">
                        <span>•</span>
                        <span>•</span>
                        <span>•</span>
                    </div>
                </div>
            `;
            container.appendChild(typingDiv);
            container.scrollTop = container.scrollHeight;
        }

        function removeTypingIndicator() {
            const indicator = document.getElementById('typing-indicator');
            if (indicator) {
                indicator.remove();
            }
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar dashboard por defecto
            showSection('dashboard');
            
            // Cargar datos dinámicos
            loadDynamicContent();
            
            // Event listener para el chat
            document.getElementById('chatForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const input = document.getElementById('chatInput');
                const message = input.value.trim();
                if (message) {
                    sendChatMessage(message);
                    input.value = '';
                }
            });
            
            // Cerrar menú de usuario al hacer click fuera
            document.addEventListener('click', function(e) {
                const userMenu = document.getElementById('userMenu');
                const userButton = document.querySelector('[onclick="toggleUserMenu()"]');
                if (!userButton.contains(e.target) && !userMenu.contains(e.target)) {
                    userMenu.classList.add('hidden');
                }
            });
        });

        // Funciones placeholder para compatibilidad
        function loadDynamicContent() {
            // Cargar desde database.json o localStorage
            // Esta función se implementará con el sistema existente
            console.log('Cargando contenido dinámico...');
        }

        function openAddCategoryModal() {
            console.log('Abrir modal de agregar categoría');
        }

        function openAddTopicModal() {
            console.log('Abrir modal de agregar tema');
        }

        function openTopicModal(topicId) {
            console.log('Abrir modal de tema:', topicId);
        }

        function openCategoryModal(categoryId) {
            console.log('Abrir modal de categoría:', categoryId);
        }

        function editCategory(categoryId) {
            console.log('Editar categoría:', categoryId);
        }

        function editTopic(topicId) {
            console.log('Editar tema:', topicId);
        }
    </script>

                    <button onclick="searchKnowledge()" 
                            class="absolute right-2 top-2 bg-cesco-green hover:bg-cesco-light text-white p-3 rounded-full transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                    </button>
                    
                    <!-- Search Suggestions Dropdown -->
                    <div id="searchSuggestions" class="absolute top-full left-0 right-0 bg-white border-2 border-gray-200 rounded-lg shadow-lg mt-2 hidden z-10">
                        <!-- Suggestions will be populated here -->
                    </div>
                </div>
                
            </div>
            

        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        
        <!-- Knowledge Categories -->
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-8 mb-8">
            <div class="flex flex-col sm:flex-row items-center justify-between mb-8">
                <h2 class="text-3xl font-bold text-cesco-green mb-4 sm:mb-0">Categorías de Información</h2>
                <button onclick="openAddCategoryModal()" 
                        class="flex items-center gap-2 bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Nueva Categoría
                </button>
            </div>
            
            <div id="categoriesGrid" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Categories will be loaded dynamically -->
            </div>
        </div>

        <!-- Content List Module -->
        <div class="bg-gradient-to-br from-white via-blue-50 to-indigo-100 rounded-3xl shadow-2xl border border-gray-200 overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r from-blue-600 via-purple-600 to-indigo-700 p-4 text-white">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white bg-opacity-20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold">Centro de Contenido</h2>
                            <p class="text-blue-100 text-sm">Explora y gestiona toda tu información</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div id="contentStats" class="text-blue-100 text-xs">
                            <!-- Stats will be populated here -->
                        </div>
                        <button onclick="openAllContentModal()" 
                                class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg font-medium transition-all backdrop-blur-sm flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                            Vista Completa
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Controls Section -->
            <div class="bg-white bg-opacity-60 backdrop-blur-sm border-b border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path>
                            </svg>
                            <label class="text-sm font-semibold text-gray-700">Ordenar:</label>
                        </div>
                        <select id="sortBy" onchange="updateContentList()" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-medium shadow-sm hover:shadow-md transition-shadow focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="date">📅 Por Fecha</option>
                            <option value="category">📂 Por Categoría</option>
                            <option value="title">🔤 Por Título</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <label class="text-sm font-semibold text-gray-700">Filtrar:</label>
                        </div>
                        <select id="filterCategory" onchange="updateContentList()" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-sm font-medium shadow-sm hover:shadow-md transition-shadow focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="all">🌟 Todas las Categorías</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Content Section -->
            <div class="p-8">
                <div id="contentListContainer">
                    <!-- Content list will be populated here -->
                </div>
            </div>
        </div>
    </main>

    <!-- All Modals and JavaScript content will be added here -->
    <!-- Category Modal -->
    <div id="categoryModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg w-11/12 max-w-4xl max-h-[90vh] overflow-hidden shadow-xl border">
            <div class="flex items-center justify-between p-4 border-b bg-gray-50">
                <h2 id="categoryTitle" class="text-xl font-semibold text-gray-800">Categoría</h2>
                <button onclick="closeModal('categoryModal')" 
                        class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="categoryContent" class="p-6 overflow-y-auto max-h-96">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Topic Modal -->
    <div id="topicModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[95vh] overflow-hidden shadow-2xl border-0 flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex-shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 id="topicTitle" class="text-xl font-bold text-gray-900">Tema</h2>
                        <p class="text-sm text-gray-600">Vista detallada del contenido</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button id="editTopicButton" onclick="editCurrentTopic()" 
                            class="flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-medium transition-colors text-sm shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Editar
                    </button>
                    <button onclick="closeModal('topicModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div id="topicContent" class="flex-1 overflow-y-auto p-6">
                <!-- Content will be loaded here -->
            </div>
            
            <div id="noInfoSection" class="hidden p-6 border-t bg-gray-50 flex-shrink-0">
                <div class="text-center">
                    <p class="text-gray-600 mb-4">No se encontró información específica sobre este tema.</p>
                    <button onclick="suggestAddInfo()" 
                            class="bg-orange-500 hover:bg-orange-600 text-white px-6 py-3 rounded-lg font-semibold transition-colors">
                        ¿Desea agregar información referente al tema?
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot Button -->
    <button onclick="openChat()" 
            class="fixed bottom-6 right-6 w-16 h-16 bg-gradient-to-r from-cesco-green to-cesco-light rounded-full shadow-lg hover:scale-110 transition-transform z-40">
        <div class="w-10 h-10 bg-white rounded-full mx-auto flex items-center justify-center text-2xl">
            👩‍💼
        </div>
    </button>

    <!-- Chat Modal -->
    <div id="chatModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-lg h-5/6 max-h-[600px] flex flex-col overflow-hidden">
            <div class="bg-gradient-to-r from-cesco-green to-cesco-light text-white p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center text-2xl">👩‍💼</div>
                        <div>
                            <h3 class="text-xl font-bold">ANA - Asistente Virtual</h3>
                            <p class="text-sm opacity-90">Especialista en trámites CESCO</p>
                        </div>
                    </div>
                    <button onclick="closeChat()" class="text-white hover:bg-white hover:bg-opacity-20 rounded-full p-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <div id="chatContainer" class="flex-1 p-4 overflow-y-auto space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                    <h4 class="text-cesco-green font-bold text-lg mb-2">¡Bienvenido!</h4>
                    <p>Soy ANA, tu asistente virtual de CESCO Online. ¿En qué puedo ayudarte hoy?</p>
                    <p class="text-xs opacity-70 mt-2">Pregúntame sobre renovaciones, documentos, costos, horarios y más.</p>
                </div>
            </div>
            
            <div class="p-4 border-t bg-gray-50">
                <form id="chatForm" class="flex gap-2">
                    <input type="text" id="chatInput" 
                           class="flex-1 border-2 border-gray-300 rounded-full px-4 py-3 focus:border-cesco-green focus:outline-none"
                           placeholder="Escribe tu pregunta aquí..." required>
                    <button type="submit" 
                            class="bg-cesco-green hover:bg-cesco-light text-white rounded-full w-12 h-12 flex items-center justify-center transition-colors">
                        ➤
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Search Results Modal -->
    <div id="searchResultsModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg w-11/12 max-w-4xl max-h-[90vh] overflow-hidden shadow-xl border">
            <div class="flex items-center justify-between p-4 border-b bg-gray-50">
                <h2 id="searchResultsTitle" class="text-xl font-semibold text-gray-800">Resultados de Búsqueda</h2>
                <button onclick="closeModal('searchResultsModal')" 
                        class="text-gray-400 hover:text-gray-600 p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            <div id="searchResultsContent" class="p-6 overflow-y-auto max-h-96">
                <!-- Search results will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div id="editCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Editar Categoría</h2>
                            <p class="text-sm text-gray-500">Modificar información de la categoría</p>
                        </div>
                    </div>
                    <button onclick="closeModal('editCategoryModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <form id="editCategoryForm" class="space-y-6">
                    <input type="hidden" id="editCategoryId" name="categoryId">
                    
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="editCategoryName" class="block text-sm font-semibold text-gray-800">
                            📂 Nombre de la Categoría
                        </label>
                        <input type="text" id="editCategoryName" name="name" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 focus:outline-none transition-all">
                    </div>
                    
                    <!-- Description -->
                    <div class="space-y-2">
                        <label for="editCategoryDescription" class="block text-sm font-semibold text-gray-800">
                            📝 Descripción
                        </label>
                        <textarea id="editCategoryDescription" name="description" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 focus:outline-none transition-all resize-vertical"></textarea>
                    </div>
                    
                    <!-- Icon -->
                    <div class="space-y-2">
                        <label for="editCategoryIcon" class="block text-sm font-semibold text-gray-800">
                            😀 Icono (Emoji)
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <input type="text" id="editCategoryIcon" name="icon" required maxlength="2"
                                       class="w-20 px-4 py-3 border border-gray-200 rounded-xl focus:border-purple-400 focus:ring-2 focus:ring-purple-100 focus:outline-none transition-all text-center text-2xl">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">Elige un emoji de la lista o escribe uno personalizado</p>
                                </div>
                            </div>
                            
                            <!-- Lista organizada de iconos -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="space-y-3">
                                    <!-- Documentos y Oficina -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">📄 Documentos y Oficina</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setEditIcon('📋')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Documentos">📋</button>
                                            <button type="button" onclick="setEditIcon('📄')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Formularios">📄</button>
                                            <button type="button" onclick="setEditIcon('📝')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Trámites">📝</button>
                                            <button type="button" onclick="setEditIcon('🏢')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Oficinas">🏢</button>
                                            <button type="button" onclick="setEditIcon('🏛️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Gobierno">🏛️</button>
                                            <button type="button" onclick="setEditIcon('📊')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Reportes">📊</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Vehículos y Transporte -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">🚗 Vehículos y Transporte</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setEditIcon('🚗')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Vehículos">🚗</button>
                                            <button type="button" onclick="setEditIcon('🚙')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="SUV">🚙</button>
                                            <button type="button" onclick="setEditIcon('🚛')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Camiones">🚛</button>
                                            <button type="button" onclick="setEditIcon('🏍️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Motocicletas">🏍️</button>
                                            <button type="button" onclick="setEditIcon('🚌')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Transporte Público">🚌</button>
                                            <button type="button" onclick="setEditIcon('⛽')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Combustible">⛽</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Finanzas y Pagos -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">💰 Finanzas y Pagos</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setEditIcon('💳')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Pagos">💳</button>
                                            <button type="button" onclick="setEditIcon('💰')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Dinero">💰</button>
                                            <button type="button" onclick="setEditIcon('🏦')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Banco">🏦</button>
                                            <button type="button" onclick="setEditIcon('💵')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Facturas">💵</button>
                                            <button type="button" onclick="setEditIcon('🧾')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Recibos">🧾</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Servicios Públicos -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">🏥 Servicios Públicos</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setEditIcon('🏥')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Salud">🏥</button>
                                            <button type="button" onclick="setEditIcon('🎓')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Educación">🎓</button>
                                            <button type="button" onclick="setEditIcon('⚖️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Legal">⚖️</button>
                                            <button type="button" onclick="setEditIcon('👮')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Policía">👮</button>
                                            <button type="button" onclick="setEditIcon('🚨')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Emergencias">🚨</button>
                                            <button type="button" onclick="setEditIcon('🔒')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Seguridad">🔒</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Otros -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">⭐ Otros</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setEditIcon('📞')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Contacto">📞</button>
                                            <button type="button" onclick="setEditIcon('📍')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Ubicación">📍</button>
                                            <button type="button" onclick="setEditIcon('⏰')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Horarios">⏰</button>
                                            <button type="button" onclick="setEditIcon('ℹ️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Información">ℹ️</button>
                                            <button type="button" onclick="setEditIcon('❓')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Preguntas">❓</button>
                                            <button type="button" onclick="setEditIcon('⭐')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Destacado">⭐</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Color -->
                    <div class="space-y-2">
                        <label for="editCategoryColor" class="block text-sm font-semibold text-gray-800">
                            🎨 Color de la Categoría
                        </label>
                        <div class="grid grid-cols-6 gap-3">
                            <button type="button" onclick="setEditColor('blue')" class="w-12 h-12 bg-blue-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-blue-600" data-edit-color="blue"></button>
                            <button type="button" onclick="setEditColor('green')" class="w-12 h-12 bg-green-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-green-600" data-edit-color="green"></button>
                            <button type="button" onclick="setEditColor('purple')" class="w-12 h-12 bg-purple-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-purple-600" data-edit-color="purple"></button>
                            <button type="button" onclick="setEditColor('red')" class="w-12 h-12 bg-red-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-red-600" data-edit-color="red"></button>
                            <button type="button" onclick="setEditColor('yellow')" class="w-12 h-12 bg-yellow-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-yellow-600" data-edit-color="yellow"></button>
                            <button type="button" onclick="setEditColor('indigo')" class="w-12 h-12 bg-indigo-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-indigo-600" data-edit-color="indigo"></button>
                        </div>
                        <input type="hidden" id="editCategoryColor" name="color">
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex justify-between items-center">
                    <button type="button" onclick="deleteCategoryConfirm()" 
                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors">
                        🗑️ Eliminar
                    </button>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeModal('editCategoryModal')" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                            Cancelar
                        </button>
                        <button type="submit" form="editCategoryForm"
                                class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-lg font-medium transition-colors text-sm">
                            ✨ Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Topic Modal -->
    <div id="editTopicModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-3xl max-h-[95vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Editar Tema</h2>
                            <p class="text-sm text-gray-500">Modificar información existente</p>
                        </div>
                    </div>
                    <button onclick="closeModal('editTopicModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
                <form id="editTopicForm" class="space-y-6" enctype="multipart/form-data">
                    <input type="hidden" id="editTopicId" name="topicId">
                    
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="editTopicTitle" class="block text-sm font-semibold text-gray-800">
                            📝 Título del Tema
                        </label>
                        <input type="text" id="editTopicTitle" name="title" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none transition-all">
                    </div>
                    
                    <!-- Category -->
                    <div class="space-y-2">
                        <label for="editTopicCategory" class="block text-sm font-semibold text-gray-800">
                            📂 Categoría
                        </label>
                        <select id="editTopicCategory" name="category" required
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none transition-all">
                            <option value="">Seleccionar categoría</option>
                        </select>
                    </div>
                    
                    <!-- Content -->
                    <div class="space-y-2">
                        <label for="editTopicContent" class="block text-sm font-semibold text-gray-800">
                            📄 Contenido Detallado
                        </label>
                        <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition-all">
                            <!-- Editor Toolbar -->
                            <div class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-1">
                                <button type="button" onclick="formatText('bold')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-bold" title="Negrita">
                                    <strong>B</strong>
                                </button>
                                <button type="button" onclick="formatText('italic')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm italic" title="Cursiva">
                                    <em>I</em>
                                </button>
                                <button type="button" onclick="formatText('underline')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm underline" title="Subrayado">
                                    U
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatText('insertUnorderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista con viñetas">
                                    • Lista
                                </button>
                                <button type="button" onclick="formatText('insertOrderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista numerada">
                                    1. Lista
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatText('formatBlock', 'h3')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-semibold" title="Título 3">
                                    H3
                                </button>
                                <button type="button" onclick="formatText('formatBlock', 'h4')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-medium" title="Título 4">
                                    H4
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatText('insertLineBreak')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Salto de línea">
                                    ↵ BR
                                </button>
                                <button type="button" onclick="formatText('insertParagraph')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Párrafo">
                                    ¶ P
                                </button>
                            </div>
                            
                            <!-- Editor Content -->
                            <div id="editTopicContentEditor" 
                                 contenteditable="true" 
                                 class="w-full min-h-[200px] p-4 focus:outline-none"
                                 style="max-height: 400px; overflow-y: auto;"
                                 placeholder="Escribe el contenido detallado aquí. Puedes usar los botones de arriba para dar formato al texto.">
                            </div>
                            
                            <!-- Hidden textarea para enviar datos -->
                            <textarea id="editTopicContent" name="content" required class="hidden"></textarea>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Usa la barra de herramientas para dar formato al texto. El contenido se guardará con formato HTML.
                        </p>
                    </div>
                    
                    <!-- Files -->
                    <div class="space-y-2">
                        <label for="editTopicFiles" class="block text-sm font-semibold text-gray-800">
                            📎 Archivos Adjuntos
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-blue-300 transition-colors">
                            <input type="file" id="editTopicFiles" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                   class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                Agregar nuevos archivos (los existentes se mantendrán)<br>
                                <span class="font-medium">PDF, DOC, DOCX, JPG, PNG, GIF</span> • Máximo 5MB por archivo
                            </p>
                        </div>
                        <div id="existingFiles" class="mt-2">
                            <!-- Archivos existentes se mostrarán aquí -->
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex justify-between items-center">
                    <button type="button" onclick="deleteTopicConfirm()" 
                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded text-xs transition-colors">
                        🗑️ Eliminar
                    </button>
                    <div class="flex gap-2">
                        <button type="button" onclick="closeModal('editTopicModal')" 
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                            Cancelar
                        </button>
                        <button type="submit" form="editTopicForm"
                                class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg font-medium transition-colors text-sm">
                            ✨ Actualizar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Topic to Category Modal -->
    <div id="addTopicToCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-3xl max-h-[95vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Nuevo Tema</h2>
                            <p class="text-sm text-gray-500">Agregar información a la categoría</p>
                        </div>
                    </div>
                    <button onclick="closeModal('addTopicToCategoryModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
                <!-- Category Info -->
                <div id="categoryInfoDisplay" class="mb-8 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                    <!-- Category info will be displayed here -->
                </div>
                
                <form id="addTopicToCategoryForm" class="space-y-6" enctype="multipart/form-data">
                    <input type="hidden" id="targetCategoryId" name="categoryId">
                    
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="categoryTopicTitle" class="block text-sm font-semibold text-gray-800">
                            📝 Título del Tema
                        </label>
                        <input type="text" id="categoryTopicTitle" name="title" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-400 focus:ring-2 focus:ring-green-100 focus:outline-none transition-all"
                               placeholder="Ej: Renovación de Licencia de Conducir">
                    </div>
                    
                    <!-- Content -->
                    <div class="space-y-2">
                        <label for="categoryTopicContent" class="block text-sm font-semibold text-gray-800">
                            📄 Contenido Detallado
                        </label>
                        <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:border-green-400 focus-within:ring-2 focus-within:ring-green-100 transition-all">
                            <!-- Editor Toolbar -->
                            <div class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-1">
                                <button type="button" onclick="formatCategoryTopicText('bold')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-bold" title="Negrita">
                                    <strong>B</strong>
                                </button>
                                <button type="button" onclick="formatCategoryTopicText('italic')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm italic" title="Cursiva">
                                    <em>I</em>
                                </button>
                                <button type="button" onclick="formatCategoryTopicText('underline')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm underline" title="Subrayado">
                                    U
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatCategoryTopicText('insertUnorderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista con viñetas">
                                    • Lista
                                </button>
                                <button type="button" onclick="formatCategoryTopicText('insertOrderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista numerada">
                                    1. Lista
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatCategoryTopicText('formatBlock', 'h3')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-semibold" title="Título 3">
                                    H3
                                </button>
                                <button type="button" onclick="formatCategoryTopicText('formatBlock', 'h4')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-medium" title="Título 4">
                                    H4
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatCategoryTopicText('insertLineBreak')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Salto de línea">
                                    ↵ BR
                                </button>
                                <button type="button" onclick="formatCategoryTopicText('insertParagraph')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Párrafo">
                                    ¶ P
                                </button>
                            </div>
                            
                            <!-- Editor Content -->
                            <div id="categoryTopicContentEditor" 
                                 contenteditable="true" 
                                 class="w-full min-h-[200px] p-4 focus:outline-none"
                                 style="max-height: 400px; overflow-y: auto;"
                                 placeholder="Describe detalladamente el proceso, requisitos, documentos necesarios, costos, etc. Puedes usar los botones de arriba para dar formato al texto.">
                            </div>
                            
                            <!-- Hidden textarea para enviar datos -->
                            <textarea id="categoryTopicContent" name="content" required class="hidden"></textarea>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Usa la barra de herramientas para dar formato al texto. El contenido se guardará con formato HTML.
                        </p>
                    </div>
                    
                    <!-- Files -->
                    <div class="space-y-2">
                        <label for="categoryTopicFiles" class="block text-sm font-semibold text-gray-800">
                            📎 Archivos Adjuntos
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-green-300 transition-colors">
                            <input type="file" id="categoryTopicFiles" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                   class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                Arrastra archivos aquí o haz clic para seleccionar<br>
                                <span class="font-medium">PDF, DOC, DOCX, JPG, PNG, GIF</span> • Máximo 5MB por archivo
                            </p>
                        </div>
                    </div>
                    
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal('addTopicToCategoryModal')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                        Cancelar
                    </button>
                    <button type="submit" form="addTopicToCategoryForm"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors text-sm">
                        ✨ Agregar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- All Content Modal -->
    <div id="allContentModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-6xl max-h-[95vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Todo el Contenido</h2>
                            <p class="text-sm text-gray-500">Vista completa de todos los temas</p>
                        </div>
                    </div>
                    <button onclick="closeModal('allContentModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
                <div id="allContentContainer">
                    <!-- All content will be populated here -->
                </div>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex justify-end">
                    <button onclick="closeModal('allContentModal')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Category Modal -->
    <div id="addCategoryModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Nueva Categoría</h2>
                            <p class="text-sm text-gray-500">Crear una nueva categoría de información</p>
                        </div>
                    </div>
                    <button onclick="closeModal('addCategoryModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                <form id="addCategoryForm" class="space-y-6">
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="newCategoryTitle" class="block text-sm font-semibold text-gray-800">
                            📂 Nombre de la Categoría
                        </label>
                        <input type="text" id="newCategoryTitle" name="title" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-400 focus:ring-2 focus:ring-green-100 focus:outline-none transition-all"
                               placeholder="Ej: Renovaciones, Documentos, Trámites...">
                    </div>
                    
                    <!-- Description -->
                    <div class="space-y-2">
                        <label for="newCategoryDescription" class="block text-sm font-semibold text-gray-800">
                            📝 Descripción
                        </label>
                        <textarea id="newCategoryDescription" name="description" required rows="3"
                                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-green-400 focus:ring-2 focus:ring-green-100 focus:outline-none transition-all resize-vertical"
                                  placeholder="Describe brevemente qué tipo de información contendrá esta categoría..."></textarea>
                    </div>
                    
                    <!-- Icon -->
                    <div class="space-y-2">
                        <label for="newCategoryIcon" class="block text-sm font-semibold text-gray-800">
                            😀 Icono (Emoji)
                        </label>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3">
                                <input type="text" id="newCategoryIcon" name="icon" required maxlength="2"
                                       class="w-20 px-4 py-3 border border-gray-200 rounded-xl focus:border-green-400 focus:ring-2 focus:ring-green-100 focus:outline-none transition-all text-center text-2xl"
                                       placeholder="📋">
                                <div class="flex-1">
                                    <p class="text-sm text-gray-600">Elige un emoji de la lista o escribe uno personalizado</p>
                                </div>
                            </div>
                            
                            <!-- Lista organizada de iconos -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <div class="space-y-3">
                                    <!-- Documentos y Oficina -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">📄 Documentos y Oficina</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setIcon('📋')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Documentos">📋</button>
                                            <button type="button" onclick="setIcon('📄')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Formularios">📄</button>
                                            <button type="button" onclick="setIcon('📝')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Trámites">📝</button>
                                            <button type="button" onclick="setIcon('🏢')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Oficinas">🏢</button>
                                            <button type="button" onclick="setIcon('🏛️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Gobierno">🏛️</button>
                                            <button type="button" onclick="setIcon('📊')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Reportes">📊</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Vehículos y Transporte -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">🚗 Vehículos y Transporte</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setIcon('🚗')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Vehículos">🚗</button>
                                            <button type="button" onclick="setIcon('🚙')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="SUV">🚙</button>
                                            <button type="button" onclick="setIcon('🚛')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Camiones">🚛</button>
                                            <button type="button" onclick="setIcon('🏍️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Motocicletas">🏍️</button>
                                            <button type="button" onclick="setIcon('🚌')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Transporte Público">🚌</button>
                                            <button type="button" onclick="setIcon('⛽')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Combustible">⛽</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Finanzas y Pagos -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">💰 Finanzas y Pagos</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setIcon('💳')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Pagos">💳</button>
                                            <button type="button" onclick="setIcon('💰')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Dinero">💰</button>
                                            <button type="button" onclick="setIcon('🏦')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Banco">🏦</button>
                                            <button type="button" onclick="setIcon('💵')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Facturas">💵</button>
                                            <button type="button" onclick="setIcon('🧾')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Recibos">🧾</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Servicios Públicos -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">🏥 Servicios Públicos</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setIcon('🏥')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Salud">🏥</button>
                                            <button type="button" onclick="setIcon('🎓')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Educación">🎓</button>
                                            <button type="button" onclick="setIcon('⚖️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Legal">⚖️</button>
                                            <button type="button" onclick="setIcon('👮')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Policía">👮</button>
                                            <button type="button" onclick="setIcon('🚨')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Emergencias">🚨</button>
                                            <button type="button" onclick="setIcon('🔒')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Seguridad">🔒</button>
                                        </div>
                                    </div>
                                    
                                    <!-- Otros -->
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 mb-2 uppercase tracking-wide">⭐ Otros</p>
                                        <div class="flex flex-wrap gap-2">
                                            <button type="button" onclick="setIcon('📞')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Contacto">📞</button>
                                            <button type="button" onclick="setIcon('📍')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Ubicación">📍</button>
                                            <button type="button" onclick="setIcon('⏰')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Horarios">⏰</button>
                                            <button type="button" onclick="setIcon('ℹ️')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Información">ℹ️</button>
                                            <button type="button" onclick="setIcon('❓')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Preguntas">❓</button>
                                            <button type="button" onclick="setIcon('⭐')" class="text-2xl hover:bg-white p-2 rounded-lg transition-colors border border-transparent hover:border-gray-200" title="Destacado">⭐</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Color -->
                    <div class="space-y-2">
                        <label for="newCategoryColor" class="block text-sm font-semibold text-gray-800">
                            🎨 Color de la Categoría
                        </label>
                        <div class="grid grid-cols-6 gap-3">
                            <button type="button" onclick="setColor('blue')" class="w-12 h-12 bg-blue-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-blue-600" data-color="blue"></button>
                            <button type="button" onclick="setColor('green')" class="w-12 h-12 bg-green-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-green-600" data-color="green"></button>
                            <button type="button" onclick="setColor('purple')" class="w-12 h-12 bg-purple-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-purple-600" data-color="purple"></button>
                            <button type="button" onclick="setColor('red')" class="w-12 h-12 bg-red-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-red-600" data-color="red"></button>
                            <button type="button" onclick="setColor('yellow')" class="w-12 h-12 bg-yellow-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-yellow-600" data-color="yellow"></button>
                            <button type="button" onclick="setColor('indigo')" class="w-12 h-12 bg-indigo-500 rounded-xl hover:scale-110 transition-transform border-2 border-transparent hover:border-indigo-600" data-color="indigo"></button>
                        </div>
                        <input type="hidden" id="newCategoryColor" name="color" value="blue">
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal('addCategoryModal')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                        Cancelar
                    </button>
                    <button type="submit" form="addCategoryForm"
                            class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors text-sm">
                        ✨ Crear Categoría
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Information Modal -->
    <div id="addInfoModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-2xl w-11/12 max-w-3xl max-h-[95vh] overflow-hidden shadow-2xl border-0">
            <!-- Header -->
            <div class="bg-white border-b border-gray-100 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-gray-900">Agregar Nueva Información</h2>
                            <p class="text-sm text-gray-500">Crear un nuevo tema de información</p>
                        </div>
                    </div>
                    <button onclick="closeModal('addInfoModal')" 
                            class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Content -->
            <div class="p-6 overflow-y-auto max-h-[calc(95vh-140px)]">
                <form id="addInfoForm" class="space-y-6" enctype="multipart/form-data">
                    <!-- Title -->
                    <div class="space-y-2">
                        <label for="infoTitle" class="block text-sm font-semibold text-gray-800">
                            📝 Título del Tema
                        </label>
                        <input type="text" id="infoTitle" name="title" required
                               class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-orange-400 focus:ring-2 focus:ring-orange-100 focus:outline-none transition-all"
                               placeholder="Ej: Renovación de Licencia Comercial">
                    </div>
                    
                    <!-- Category -->
                    <div class="space-y-2">
                        <label for="infoCategory" class="block text-sm font-semibold text-gray-800">
                            📂 Categoría
                        </label>
                        <select id="infoCategory" name="category"
                                class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:border-orange-400 focus:ring-2 focus:ring-orange-100 focus:outline-none transition-all">
                            <option value="">Sin categoría específica</option>
                        </select>
                    </div>
                    
                    <!-- Content -->
                    <div class="space-y-2">
                        <label for="infoContent" class="block text-sm font-semibold text-gray-800">
                            📄 Contenido Detallado
                        </label>
                        <div class="border border-gray-200 rounded-xl overflow-hidden focus-within:border-orange-400 focus-within:ring-2 focus-within:ring-orange-100 transition-all">
                            <!-- Editor Toolbar -->
                            <div class="bg-gray-50 border-b border-gray-200 p-3 flex flex-wrap gap-1">
                                <button type="button" onclick="formatInfoText('bold')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-bold" title="Negrita">
                                    <strong>B</strong>
                                </button>
                                <button type="button" onclick="formatInfoText('italic')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm italic" title="Cursiva">
                                    <em>I</em>
                                </button>
                                <button type="button" onclick="formatInfoText('underline')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm underline" title="Subrayado">
                                    U
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatInfoText('insertUnorderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista con viñetas">
                                    • Lista
                                </button>
                                <button type="button" onclick="formatInfoText('insertOrderedList')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Lista numerada">
                                    1. Lista
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatInfoText('formatBlock', 'h3')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-semibold" title="Título 3">
                                    H3
                                </button>
                                <button type="button" onclick="formatInfoText('formatBlock', 'h4')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm font-medium" title="Título 4">
                                    H4
                                </button>
                                <div class="w-px bg-gray-300 mx-1"></div>
                                <button type="button" onclick="formatInfoText('insertLineBreak')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Salto de línea">
                                    ↵ BR
                                </button>
                                <button type="button" onclick="formatInfoText('insertParagraph')" class="px-3 py-1 bg-white border border-gray-300 rounded hover:bg-gray-100 transition-colors text-sm" title="Párrafo">
                                    ¶ P
                                </button>
                            </div>
                            
                            <!-- Editor Content -->
                            <div id="infoContentEditor" 
                                 contenteditable="true" 
                                 class="w-full min-h-[200px] p-4 focus:outline-none"
                                 style="max-height: 400px; overflow-y: auto;"
                                 placeholder="Describe detalladamente el proceso, requisitos, costos, etc. Puedes usar los botones de arriba para dar formato al texto.">
                            </div>
                            
                            <!-- Hidden textarea para enviar datos -->
                            <textarea id="infoContent" name="content" required class="hidden"></textarea>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">
                            Usa la barra de herramientas para dar formato al texto. El contenido se guardará con formato HTML.
                        </p>
                    </div>
                    
                    <!-- Files -->
                    <div class="space-y-2">
                        <label for="infoFiles" class="block text-sm font-semibold text-gray-800">
                            📎 Archivos Adjuntos (Opcional)
                        </label>
                        <div class="border-2 border-dashed border-gray-200 rounded-xl p-6 hover:border-orange-300 transition-colors">
                            <input type="file" id="infoFiles" name="files[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                   class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                            <p class="text-xs text-gray-500 mt-2 text-center">
                                Arrastra archivos aquí o haz clic para seleccionar<br>
                                <span class="font-medium">PDF, DOC, DOCX, JPG, PNG, GIF</span> • Máximo 5MB por archivo
                            </p>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- Footer -->
            <div class="bg-gray-50 border-t border-gray-100 p-4">
                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="closeModal('addInfoModal')" 
                            class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm">
                        Cancelar
                    </button>
                    <button type="submit" form="addInfoForm"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-lg font-medium transition-colors text-sm">
                        ✨ Agregar Información
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-gray-400 text-center py-8 mt-12">
        <p>© 2025 CESCO Online. Todos los derechos reservados. | Base de Conocimientos CESCO</p>
    </footer>

    <script>
        // Variables globales para el contenido dinámico
        let dynamicContent = {};
        let dynamicCategories = {};
        let customCategories = {};
        let allCategories = {};
        let searchDatabase = {};
        let knowledgeBase = {};
        let currentTopicId = null; // Para guardar el ID del tema actual

        const topicDetails = {};

        // Función para cargar contenido dinámico desde database.json
        async function loadDynamicContent() {
            try {
                const response = await fetch('database.json');
                const data = await response.json();
                
                if (data && typeof data === 'object') {
                    // Limpiar contenido existente
                    dynamicContent = {};
                    allCategories = {};
                    searchDatabase = {};
                    knowledgeBase = {};
                    
                    // Cargar categorías desde JSON
                    if (data.categories && Array.isArray(data.categories)) {
                        data.categories.forEach(category => {
                            allCategories[category.id] = {
                                id: category.id,
                                title: category.title,
                                description: category.description,
                                icon: category.icon,
                                color: category.color,
                                topics: category.topics || []
                            };
                            
                            // Crear estructura para knowledgeBase
                            knowledgeBase[category.id] = {
                                title: category.title,
                                topics: category.topics || []
                            };
                        });
                        
                        console.log('Categorías cargadas desde database.json:', data.categories.length, 'categorías');
                    }
                    
                    // Cargar temas desde JSON
                    if (data.topics && Array.isArray(data.topics)) {
                        data.topics.forEach((item, index) => {
                            if (item.title && item.content) {
                                const topicId = item.id || item.title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
                                
                                dynamicContent[topicId] = {
                                    title: item.title,
                                    content: item.content,
                                    timestamp: item.timestamp || new Date().toISOString(),
                                    id: topicId,
                                    categoryId: item.categoryId || null, // Cargar categoría asignada
                                    files: item.files || [] // Cargar archivos adjuntos
                                };
                                
                                // Crear entradas para búsqueda
                                const searchKey = item.title.toLowerCase();
                                const categoryName = item.categoryId && allCategories[item.categoryId] 
                                    ? allCategories[item.categoryId].title 
                                    : "Información";
                                    
                                searchDatabase[searchKey] = {
                                    title: item.title,
                                    category: categoryName,
                                    content: item.content.substring(0, 200) + "..."
                                };
                                
                                topicDetails[topicId] = {
                                    title: item.title,
                                    content: `
                                        <h3 class="text-xl font-bold text-cesco-green mb-4">${item.title}</h3>
                                        <div class="space-y-4">
                                            <div class="prose max-w-none">
                                                ${item.content}
                                            </div>
                                        </div>
                                    `
                                };
                            }
                        });
                        
                        console.log('Temas cargados desde database.json:', data.topics.length, 'temas');
                    }
                    
                    // Recargar categorías en la interfaz
                    loadCategories();
                    buildSearchDatabase();
                } else {
                    console.warn('database.json no contiene un objeto válido');
                    loadFallbackData();
                }
            } catch (error) {
                console.warn('Error al cargar database.json:', error);
                loadFallbackData();
            }
        }

        // Función para construir base de datos de búsqueda dinámicamente
        function buildSearchDatabase() {
            // Agregar categorías a la búsqueda
            for (const [categoryId, category] of Object.entries(allCategories)) {
                const searchKey = category.title.toLowerCase();
                searchDatabase[searchKey] = {
                    title: category.title,
                    category: "Categoría",
                    content: category.description
                };
                
                // Agregar palabras clave relacionadas
                const keywords = category.title.toLowerCase().split(' ');
                keywords.forEach(keyword => {
                    if (keyword.length > 3) {
                        searchDatabase[keyword] = {
                            title: category.title,
                            category: "Categoría",
                            content: `Categoría relacionada con ${category.description.toLowerCase()}`
                        };
                    }
                });
            }
        }

        // Función de respaldo si no se puede cargar database.json
        function loadFallbackData() {
            console.log('Cargando datos de respaldo desde localStorage...');
            
            try {
                const backupData = localStorage.getItem('cescoDatabase');
                if (backupData) {
                    const data = JSON.parse(backupData);
                    
                    if (data.categories) {
                        data.categories.forEach(category => {
                            allCategories[category.id] = category;
                        });
                    }
                    
                    if (data.topics) {
                        data.topics.forEach(item => {
                            const topicId = item.id || item.title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
                            dynamicContent[topicId] = item;
                        });
                    }
                    
                    loadCategories();
                    buildSearchDatabase();
                } else {
                    console.log('No hay datos de respaldo disponibles');
                    showEmptyState();
                }
            } catch (error) {
                console.warn('Error al cargar datos de respaldo:', error);
                showEmptyState();
            }
        }

        // Mostrar estado vacío cuando no hay datos
        function showEmptyState() {
            const categoriesGrid = document.getElementById('categoriesGrid');
            categoriesGrid.innerHTML = `
                <div class="col-span-full text-center py-12">
                    <div class="text-6xl mb-4">📝</div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">No hay contenido disponible</h3>
                    <p class="text-gray-600 mb-4">El sistema está listo para recibir contenido dinámico</p>
                    <p class="text-sm text-gray-500">Los datos se cargarán desde database.json o localStorage</p>
                </div>
            `;
        }

        // Function to get color classes for categories
        function getColorClasses(color) {
            const colorMap = {
                blue: 'from-blue-50 to-blue-100',
                green: 'from-green-50 to-green-100',
                yellow: 'from-yellow-50 to-yellow-100',
                purple: 'from-purple-50 to-purple-100',
                red: 'from-red-50 to-red-100',
                indigo: 'from-indigo-50 to-indigo-100',
                pink: 'from-pink-50 to-pink-100',
                gray: 'from-gray-50 to-gray-100'
            };
            return colorMap[color] || 'from-gray-50 to-gray-100';
        }

        // Function to load and render all categories
        function loadCategories() {
            const categoriesGrid = document.getElementById('categoriesGrid');
            
            if (Object.keys(allCategories).length === 0) {
                showEmptyState();
                return;
            }
            
            let html = '';
            
            for (const [categoryId, category] of Object.entries(allCategories)) {
                const colorClasses = getColorClasses(category.color);
                html += `
                    <div class="relative bg-gradient-to-br ${colorClasses} rounded-xl p-6 hover:shadow-lg transition-shadow group">
                        <!-- Edit button -->
                        <button onclick="openEditCategoryModal('${categoryId}')" 
                                class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity bg-white hover:bg-gray-100 text-gray-600 p-2 rounded-full shadow-md">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </button>
                        
                        <!-- Category content -->
                        <div class="cursor-pointer" onclick="openCategoryModal('${categoryId}')">
                            <div class="text-4xl mb-4">${category.icon}</div>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">${category.title}</h3>
                            <p class="text-gray-600">${category.description}</p>
                        </div>
                    </div>
                `;
            }
            
            categoriesGrid.innerHTML = html;
            
            // Actualizar lista de contenido y filtros
            populateCategoryFilter();
            populateCategorySelectors();
            updateContentList();
        }

        // Modal functions
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function openCategoryModal(categoryId) {
            const modal = document.getElementById('categoryModal');
            const title = document.getElementById('categoryTitle');
            const content = document.getElementById('categoryContent');
            
            const category = allCategories[categoryId];
            if (category) {
                title.textContent = category.title;
                
                // Buscar temas relacionados con esta categoría
                const relatedTopics = Object.values(dynamicContent).filter(topic => {
                    // Buscar por categoría asignada directamente
                    if (topic.categoryId === categoryId) return true;
                    
                    // Buscar por palabras clave en título y contenido
                    const categoryKeywords = category.title.toLowerCase().split(' ');
                    const topicText = (topic.title + ' ' + topic.content).toLowerCase();
                    
                    return categoryKeywords.some(keyword => 
                        keyword.length > 3 && topicText.includes(keyword)
                    );
                });
                
                let topicsHtml = '';
                if (relatedTopics.length > 0) {
                    topicsHtml = `
                        <div class="space-y-4">
                            ${relatedTopics.map(topic => `
                                <div class="bg-gray-50 p-5 rounded-lg border border-gray-200 hover:bg-gray-100 transition-colors group relative w-full">
                                    <button onclick="openEditTopicModal('${topic.id}')" 
                                            class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition-opacity bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                    </button>
                                    <div class="cursor-pointer pr-10 text-left" onclick="openTopicModal('${topic.id}')">
                                        <h5 class="font-semibold text-gray-900 text-lg mb-3 text-left">${topic.title}</h5>
                                        <p class="text-gray-600 leading-relaxed text-left text-base">${topic.content.replace(/<[^>]*>/g, '').substring(0, 200)}...</p>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                } else {
                    topicsHtml = '';
                }
                
                content.innerHTML = `
                    <div class="py-8">
                        <!-- Header con icono, título y botón -->
                        <div class="flex items-start justify-between mb-6">
                            <div class="flex items-center gap-4">
                                <div class="text-6xl">${category.icon}</div>
                                <div>
                                    <h3 class="text-2xl font-bold text-gray-900">${category.title}</h3>
                                </div>
                            </div>
                            <button onclick="openAddTopicToCategoryModal('${categoryId}')" 
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Agregar
                            </button>
                        </div>
                        
                        <!-- Descripción -->
                        <div class="mb-8">
                            <p class="text-gray-600 text-lg leading-relaxed">${category.description}</p>
                        </div>
                        
                        ${topicsHtml}
                    </div>
                `;
            }
            
            modal.classList.remove('hidden');
        }

        function openTopicModal(topicId) {
            const modal = document.getElementById('topicModal');
            const title = document.getElementById('topicTitle');
            const content = document.getElementById('topicContent');
            
            // Guardar el ID del tema actual
            currentTopicId = topicId;
            
            const topic = dynamicContent[topicId];
            if (topic) {
                title.textContent = topic.title;
                
                // Generar HTML para archivos adjuntos
                let filesHtml = '';
                if (topic.files && topic.files.length > 0) {
                    filesHtml = `
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg border">
                            <h4 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                </svg>
                                Archivos Adjuntos
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                ${topic.files.map(file => `
                                    <div class="flex items-center gap-3 p-3 bg-white rounded-lg border hover:shadow-sm transition-shadow">
                                        <div class="text-2xl">${getFileIcon(file.file_type)}</div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-900 truncate">${file.original_name}</p>
                                            <p class="text-sm text-gray-500">${formatFileSize(file.file_size)} • ${file.file_type.toUpperCase()}</p>
                                        </div>
                                        <a href="${file.file_path}" target="_blank" download="${file.original_name}"
                                           class="flex-shrink-0 bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                        </a>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `;
                }
                
                content.innerHTML = `
                    <div class="prose max-w-none">
                        <div class="text-gray-700 text-lg leading-relaxed mb-6">${topic.content}</div>
                        ${filesHtml}
                    </div>
                `;
            }
            
            // Cerrar modal de categoría si está abierto
            document.getElementById('categoryModal').classList.add('hidden');
            modal.classList.remove('hidden');
        }

        function openAddInfoModal() {
            // Limpiar el editor
            const editor = document.getElementById('infoContentEditor');
            const textarea = document.getElementById('infoContent');
            if (editor && textarea) {
                editor.innerHTML = '';
                textarea.value = '';
            }
            
            // Configurar editor de texto enriquecido
            setupInfoRichTextEditor();
            
            document.getElementById('addInfoModal').classList.remove('hidden');
        }

        // Función para editar el tema actual desde el modal de visualización
        function editCurrentTopic() {
            if (currentTopicId) {
                openEditTopicModal(currentTopicId);
            }
        }

        // Función para abrir modal de agregar categoría
        function openAddCategoryModal() {
            // Limpiar formulario
            document.getElementById('addCategoryForm').reset();
            document.getElementById('newCategoryColor').value = 'blue';
            
            // Resetear selección de colores
            document.querySelectorAll('[data-color]').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-offset-2');
            });
            document.querySelector('[data-color="blue"]').classList.add('ring-4', 'ring-offset-2', 'ring-blue-300');
            
            document.getElementById('addCategoryModal').classList.remove('hidden');
        }

        // Función para establecer icono
        function setIcon(emoji) {
            document.getElementById('newCategoryIcon').value = emoji;
        }

        // Función para establecer color
        function setColor(color) {
            document.getElementById('newCategoryColor').value = color;
            
            // Actualizar selección visual
            document.querySelectorAll('[data-color]').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-offset-2');
                btn.classList.remove('ring-blue-300', 'ring-green-300', 'ring-purple-300', 'ring-red-300', 'ring-yellow-300', 'ring-indigo-300');
            });
            
            const selectedBtn = document.querySelector(`[data-color="${color}"]`);
            selectedBtn.classList.add('ring-4', 'ring-offset-2', `ring-${color}-300`);
        }

        // Funciones para el modal de editar categoría
        function setEditIcon(emoji) {
            document.getElementById('editCategoryIcon').value = emoji;
        }

        function setEditColor(color) {
            document.getElementById('editCategoryColor').value = color;
            
            // Actualizar selección visual
            document.querySelectorAll('[data-edit-color]').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-offset-2');
                btn.classList.remove('ring-blue-300', 'ring-green-300', 'ring-purple-300', 'ring-red-300', 'ring-yellow-300', 'ring-indigo-300');
            });
            
            const selectedBtn = document.querySelector(`[data-edit-color="${color}"]`);
            selectedBtn.classList.add('ring-4', 'ring-offset-2', `ring-${color}-300`);
        }

        // Funciones para el editor de texto enriquecido
        function formatText(command, value = null) {
            document.execCommand(command, false, value);
            
            // Sincronizar contenido con textarea oculto
            const editor = document.getElementById('editTopicContentEditor');
            const textarea = document.getElementById('editTopicContent');
            if (editor && textarea) {
                textarea.value = editor.innerHTML;
            }
        }

        // Sincronizar contenido del editor con textarea al escribir
        function setupRichTextEditor() {
            const editor = document.getElementById('editTopicContentEditor');
            const textarea = document.getElementById('editTopicContent');
            
            if (editor && textarea) {
                // Sincronizar al escribir
                editor.addEventListener('input', function() {
                    textarea.value = this.innerHTML;
                });
                
                // Sincronizar al pegar
                editor.addEventListener('paste', function() {
                    setTimeout(() => {
                        textarea.value = this.innerHTML;
                    }, 10);
                });
                
                // Manejar placeholder
                editor.addEventListener('focus', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
                
                editor.addEventListener('blur', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
            }
        }

        // Funciones para el editor de "Agregar Nueva Información"
        function formatInfoText(command, value = null) {
            document.execCommand(command, false, value);
            
            // Sincronizar contenido con textarea oculto
            const editor = document.getElementById('infoContentEditor');
            const textarea = document.getElementById('infoContent');
            if (editor && textarea) {
                textarea.value = editor.innerHTML;
            }
        }

        // Configurar editor de texto enriquecido para "Agregar Nueva Información"
        function setupInfoRichTextEditor() {
            const editor = document.getElementById('infoContentEditor');
            const textarea = document.getElementById('infoContent');
            
            if (editor && textarea) {
                // Sincronizar al escribir
                editor.addEventListener('input', function() {
                    textarea.value = this.innerHTML;
                });
                
                // Sincronizar al pegar
                editor.addEventListener('paste', function() {
                    setTimeout(() => {
                        textarea.value = this.innerHTML;
                    }, 10);
                });
                
                // Manejar placeholder
                editor.addEventListener('focus', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
                
                editor.addEventListener('blur', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
            }
        }

        // Funciones para el editor de "Nuevo Tema" (categoría)
        function formatCategoryTopicText(command, value = null) {
            document.execCommand(command, false, value);
            
            // Sincronizar contenido con textarea oculto
            const editor = document.getElementById('categoryTopicContentEditor');
            const textarea = document.getElementById('categoryTopicContent');
            if (editor && textarea) {
                textarea.value = editor.innerHTML;
            }
        }

        // Configurar editor de texto enriquecido para "Nuevo Tema"
        function setupCategoryTopicRichTextEditor() {
            const editor = document.getElementById('categoryTopicContentEditor');
            const textarea = document.getElementById('categoryTopicContent');
            
            if (editor && textarea) {
                // Sincronizar al escribir
                editor.addEventListener('input', function() {
                    textarea.value = this.innerHTML;
                });
                
                // Sincronizar al pegar
                editor.addEventListener('paste', function() {
                    setTimeout(() => {
                        textarea.value = this.innerHTML;
                    }, 10);
                });
                
                // Manejar placeholder
                editor.addEventListener('focus', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
                
                editor.addEventListener('blur', function() {
                    if (this.innerHTML === '' || this.innerHTML === '<br>') {
                        this.innerHTML = '';
                    }
                });
            }
        }

        // Función para abrir modal con todo el contenido
        function openAllContentModal() {
            const modal = document.getElementById('allContentModal');
            const container = document.getElementById('allContentContainer');
            
            // Obtener todos los temas
            const topics = Object.values(dynamicContent);
            
            if (topics.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">📝</div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No hay contenido disponible</h3>
                        <p class="text-gray-600">El contenido aparecerá aquí cuando se agregue información al sistema.</p>
                    </div>
                `;
            } else {
                // Ordenar por fecha (más reciente primero)
                topics.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
                
                let html = '<div class="space-y-8">';
                
                topics.forEach((topic, index) => {
                    const category = topic.categoryId && allCategories[topic.categoryId] 
                        ? allCategories[topic.categoryId] 
                        : { title: 'Sin categoría', icon: '📄', color: 'gray' };
                    
                    const date = new Date(topic.timestamp);
                    const formattedDate = date.toLocaleDateString('es-ES', {
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    });
                    
                    const hasFiles = topic.files && topic.files.length > 0;
                    
                    // Generar HTML para archivos si los hay
                    let filesHtml = '';
                    if (hasFiles) {
                        filesHtml = `
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg border">
                                <h5 class="text-sm font-semibold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                    </svg>
                                    Archivos Adjuntos (${topic.files.length})
                                </h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    ${topic.files.map(file => `
                                        <div class="flex items-center gap-2 p-2 bg-white rounded border text-xs">
                                            <span class="text-lg">${getFileIcon(file.file_type)}</span>
                                            <div class="flex-1 min-w-0">
                                                <p class="font-medium text-gray-900 truncate">${file.original_name}</p>
                                                <p class="text-gray-500">${formatFileSize(file.file_size)}</p>
                                            </div>
                                            <a href="${file.file_path}" target="_blank" download="${file.original_name}"
                                               class="bg-blue-500 hover:bg-blue-600 text-white p-1 rounded transition-colors">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }
                    
                    html += `
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <span class="text-3xl">${category.icon}</span>
                                    <div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-1 cursor-pointer hover:text-blue-600 transition-colors" onclick="openTopicModal('${topic.id}')">${topic.title}</h3>
                                        <div class="flex items-center gap-2 text-sm text-gray-600">
                                            <span class="px-2 py-1 bg-gray-100 rounded-full text-xs font-medium">
                                                ${category.title}
                                            </span>
                                            <span>•</span>
                                            <span>${formattedDate}</span>
                                            ${hasFiles ? `
                                                <span>•</span>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    ${topic.files.length} archivo${topic.files.length > 1 ? 's' : ''}
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button onclick="openTopicModal('${topic.id}')" 
                                            class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs transition-colors">
                                        Ver
                                    </button>
                                    <button onclick="openEditTopicModal('${topic.id}')" 
                                            class="bg-gray-500 hover:bg-gray-600 text-white px-3 py-1.5 rounded text-xs transition-colors">
                                        Editar
                                    </button>
                                </div>
                            </div>
                            
                            <div class="text-gray-700 leading-relaxed cursor-pointer hover:bg-gray-50 p-3 rounded-lg transition-colors" onclick="openTopicModal('${topic.id}')">
                                ${topic.content}
                            </div>
                            
                            ${filesHtml}
                        </div>
                    `;
                });
                
                html += '</div>';
                container.innerHTML = html;
            }
            
            modal.classList.remove('hidden');
        }

        // Función para abrir modal de agregar tema a categoría específica
        function openAddTopicToCategoryModal(categoryId) {
            const category = allCategories[categoryId];
            if (!category) return;
            
            // Mostrar información de la categoría
            const categoryInfoDisplay = document.getElementById('categoryInfoDisplay');
            categoryInfoDisplay.innerHTML = `
                <div class="flex items-center gap-3">
                    <div class="text-3xl">${category.icon}</div>
                    <div>
                        <h3 class="font-bold text-gray-800">${category.title}</h3>
                        <p class="text-sm text-gray-600">${category.description}</p>
                    </div>
                </div>
            `;
            
            // Establecer la categoría objetivo
            document.getElementById('targetCategoryId').value = categoryId;
            
            // Limpiar formulario
            document.getElementById('categoryTopicTitle').value = '';
            
            // Limpiar el editor
            const editor = document.getElementById('categoryTopicContentEditor');
            const textarea = document.getElementById('categoryTopicContent');
            if (editor && textarea) {
                editor.innerHTML = '';
                textarea.value = '';
            }
            
            // Configurar editor de texto enriquecido
            setupCategoryTopicRichTextEditor();
            
            // Cerrar modal de categoría si está abierto
            document.getElementById('categoryModal').classList.add('hidden');
            
            // Abrir modal
            document.getElementById('addTopicToCategoryModal').classList.remove('hidden');
        }

        // Funciones de edición de categorías
        function openEditCategoryModal(categoryId) {
            const category = allCategories[categoryId];
            if (!category) return;
            
            // Llenar campos del formulario
            document.getElementById('editCategoryId').value = categoryId;
            document.getElementById('editCategoryName').value = category.title;
            document.getElementById('editCategoryDescription').value = category.description;
            document.getElementById('editCategoryIcon').value = category.icon;
            document.getElementById('editCategoryColor').value = category.color;
            
            // Actualizar selección visual de color
            document.querySelectorAll('[data-edit-color]').forEach(btn => {
                btn.classList.remove('ring-4', 'ring-offset-2');
                btn.classList.remove('ring-blue-300', 'ring-green-300', 'ring-purple-300', 'ring-red-300', 'ring-yellow-300', 'ring-indigo-300');
            });
            
            const selectedBtn = document.querySelector(`[data-edit-color="${category.color}"]`);
            if (selectedBtn) {
                selectedBtn.classList.add('ring-4', 'ring-offset-2', `ring-${category.color}-300`);
            }
            
            document.getElementById('editCategoryModal').classList.remove('hidden');
        }

        // Funciones de edición de temas
        function openEditTopicModal(topicId) {
            const topic = dynamicContent[topicId];
            if (!topic) return;
            
            document.getElementById('editTopicId').value = topicId;
            document.getElementById('editTopicTitle').value = topic.title;
            
            // Cargar contenido en el editor de texto enriquecido
            const editor = document.getElementById('editTopicContentEditor');
            const textarea = document.getElementById('editTopicContent');
            if (editor && textarea) {
                editor.innerHTML = topic.content || '';
                textarea.value = topic.content || '';
            }
            
            // Establecer la categoría actual
            const categorySelect = document.getElementById('editTopicCategory');
            if (categorySelect && topic.categoryId) {
                categorySelect.value = topic.categoryId;
            }
            
            // Configurar editor de texto enriquecido
            setupRichTextEditor();
            
            // Cerrar otros modales
            document.getElementById('categoryModal').classList.add('hidden');
            document.getElementById('topicModal').classList.add('hidden');
            
            document.getElementById('editTopicModal').classList.remove('hidden');
        }

        // Confirmación de eliminación de categoría
        function deleteCategoryConfirm() {
            const categoryId = document.getElementById('editCategoryId').value;
            const category = allCategories[categoryId];
            
            if (confirm(`¿Estás seguro de que quieres eliminar la categoría "${category.title}"?`)) {
                deleteCategory(categoryId);
            }
        }

        // Confirmación de eliminación de tema
        function deleteTopicConfirm() {
            const topicId = document.getElementById('editTopicId').value;
            const topic = dynamicContent[topicId];
            
            if (confirm(`¿Estás seguro de que quieres eliminar el tema "${topic.title}"?`)) {
                deleteTopic(topicId);
            }
        }

        // Eliminar categoría
        async function deleteCategory(categoryId) {
            delete allCategories[categoryId];
            
            const data = getDatabaseJsonData();
            const result = await saveToDatabaseJson(data);
            
            if (result.success) {
                alert('Categoría eliminada exitosamente');
                loadCategories();
                closeModal('editCategoryModal');
            } else {
                alert('Error al eliminar la categoría: ' + result.error);
            }
        }

        // Eliminar tema
        async function deleteTopic(topicId) {
            delete dynamicContent[topicId];
            
            // Eliminar de la base de datos de búsqueda
            const topic = dynamicContent[topicId];
            if (topic) {
                delete searchDatabase[topic.title.toLowerCase()];
            }
            
            const data = getDatabaseJsonData();
            const result = await saveToDatabaseJson(data);
            
            if (result.success) {
                alert('Tema eliminado exitosamente');
                loadCategories();
                buildSearchDatabase();
                closeModal('editTopicModal');
            } else {
                alert('Error al eliminar el tema: ' + result.error);
            }
        }


        function openChat() {
            document.getElementById('chatModal').classList.remove('hidden');
        }

        function closeChat() {
            document.getElementById('chatModal').classList.add('hidden');
        }

        function searchKnowledge() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase().trim();
            
            if (!searchTerm) {
                alert('Por favor ingresa un término de búsqueda');
                return;
            }

            const results = performSearch(searchTerm);
            displaySearchResults(searchTerm, results);
        }

        function performSearch(searchTerm) {
            const results = [];
            
            // Verificar si la búsqueda está relacionada con CESCO
            if (!isValidCESCOSearch(searchTerm)) {
                return [{
                    id: 'invalid-search',
                    title: 'Búsqueda fuera del alcance',
                    category: 'Sistema',
                    categoryIcon: '⚠️',
                    content: 'Lo siento, solo puedo ayudarte con consultas sobre trámites CESCO de Puerto Rico. Por favor, reformula tu pregunta sobre licencias, tablillas, renovaciones o servicios de CESCO.',
                    files: [],
                    relevance: 100,
                    isSystemMessage: true
                }];
            }
            
            // Search in dynamicContent (all topics)
            for (const [topicId, topic] of Object.entries(dynamicContent)) {
                const relevance = calculateRelevance(searchTerm, topic);
                if (relevance > 0) {
                    const category = topic.categoryId && allCategories[topic.categoryId] 
                        ? allCategories[topic.categoryId] 
                        : { title: 'Sin categoría', icon: '📄' };
                    
                    results.push({
                        id: topicId,
                        title: topic.title,
                        category: category.title,
                        categoryIcon: category.icon,
                        content: topic.content,
                        files: topic.files || [],
                        relevance: relevance
                    });
                }
            }
            
            return results.sort((a, b) => b.relevance - a.relevance);
        }

        // Función para validar si la búsqueda está relacionada con CESCO
        function isValidCESCOSearch(searchTerm) {
            const term = searchTerm.toLowerCase().trim();
            
            // Términos válidos relacionados con CESCO
            const validTerms = [
                // Servicios principales
                'licencia', 'tablilla', 'marbete', 'renovacion', 'renovación',
                'registro', 'vehiculo', 'vehículo', 'conducir', 'chofer',
                
                // Documentos
                'documento', 'formulario', 'certificado', 'identificacion', 'identificación',
                'pasaporte', 'tarjeta', 'cedula', 'cédula',
                
                // Trámites
                'tramite', 'trámite', 'solicitud', 'permiso', 'endoso',
                'duplicado', 'reposicion', 'reposición', 'cambio',
                
                // Costos y pagos
                'costo', 'precio', 'pago', 'tarifa', 'multa', 'recargo',
                
                // Ubicaciones y horarios
                'oficina', 'horario', 'ubicacion', 'ubicación', 'direccion', 'dirección',
                'cita', 'turno', 'appointment',
                
                // Tipos de vehículos
                'auto', 'carro', 'motora', 'motorcycle', 'camion', 'camión',
                'comercial', 'particular', 'publico', 'público',
                
                // CESCO específico
                'cesco', 'dtop', 'transporte', 'puerto rico', 'pr',
                
                // Procesos específicos
                'examen', 'prueba', 'teorico', 'teórico', 'practico', 'práctico',
                'vision', 'visión', 'medico', 'médico',
                
                // Estados y condiciones
                'vencido', 'vencida', 'suspendido', 'suspendida', 'perdido', 'perdida',
                'robado', 'robada', 'dañado', 'dañada'
            ];
            
            // Verificar si contiene al menos un término válido
            return validTerms.some(validTerm => term.includes(validTerm));
        }

        function calculateRelevance(searchTerm, topic) {
            let relevance = 0;
            const term = searchTerm.toLowerCase();
            
            // Exact match in title gets highest score
            if (topic.title.toLowerCase().includes(term)) {
                if (topic.title.toLowerCase() === term) {
                    relevance += 20; // Exact match
                } else {
                    relevance += 15; // Partial match
                }
            }
            
            // Match in content
            if (topic.content.toLowerCase().includes(term)) {
                const contentLower = topic.content.toLowerCase();
                // Count occurrences for better relevance
                const occurrences = (contentLower.match(new RegExp(term, 'g')) || []).length;
                relevance += occurrences * 5;
            }
            
            // Match in category
            if (topic.categoryId && allCategories[topic.categoryId]) {
                const categoryTitle = allCategories[topic.categoryId].title.toLowerCase();
                if (categoryTitle.includes(term)) {
                    relevance += 8;
                }
            }
            
            // Bonus for having files
            if (topic.files && topic.files.length > 0) {
                relevance += 2;
            }
            
            return relevance;
        }

        function displaySearchResults(searchTerm, results) {
            const modal = document.getElementById('searchResultsModal');
            const title = document.getElementById('searchResultsTitle');
            const content = document.getElementById('searchResultsContent');
            
            title.textContent = `Resultados para: "${searchTerm}"`;
            
            if (results.length === 0) {
                content.innerHTML = `
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">No se encontraron resultados</h3>
                        <p class="text-gray-600">Intenta con otros términos de búsqueda relacionados con el contenido.</p>
                    </div>
                `;
            } else {
                let html = `
                    <div class="mb-4">
                        <p class="text-sm text-gray-600">Se encontraron ${results.length} resultado(s)</p>
                    </div>
                `;
                
                results.forEach((result, index) => {
                    const hasFiles = result.files && result.files.length > 0;
                    const contentPreview = result.content.replace(/<[^>]*>/g, '').substring(0, 200);
                    
                    // Mensaje del sistema (búsqueda inválida)
                    if (result.isSystemMessage) {
                        html += `
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-4">
                                <div class="flex items-start gap-3">
                                    <span class="text-3xl">${result.categoryIcon}</span>
                                    <div class="flex-1">
                                        <h4 class="font-bold text-lg text-yellow-800 mb-2">${result.title}</h4>
                                        <p class="text-yellow-700 leading-relaxed mb-4">${result.content}</p>
                                        <div class="bg-white border border-yellow-200 rounded-lg p-4">
                                            <h5 class="font-semibold text-yellow-800 mb-2">Puedes buscar información sobre:</h5>
                                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 text-sm">
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Licencias de conducir</span>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Tablillas y marbetes</span>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Renovaciones</span>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Registros vehiculares</span>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Costos y tarifas</span>
                                                <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded">• Horarios y ubicaciones</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        // Resultado normal
                        html += `
                            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-4 hover:shadow-md transition-all cursor-pointer" onclick="openTopicFromSearch('${result.id}')">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <span class="text-2xl">${result.categoryIcon}</span>
                                            <div>
                                                <h4 class="font-bold text-lg text-gray-900 hover:text-blue-600 transition-colors">${result.title}</h4>
                                                <div class="flex items-center gap-2 text-sm text-gray-600 mt-1">
                                                    <span class="px-2 py-1 bg-gray-100 rounded-full text-xs font-medium">
                                                        ${result.category}
                                                    </span>
                                                    ${hasFiles ? `
                                                        <span class="flex items-center gap-1 text-blue-600">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                            </svg>
                                                            ${result.files.length} archivo${result.files.length > 1 ? 's' : ''}
                                                        </span>
                                                    ` : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-gray-700 leading-relaxed">${contentPreview}${result.content.length > 200 ? '...' : ''}</p>
                                    </div>
                                    <div class="ml-4 flex flex-col items-end gap-2">
                                        <div class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">
                                            Relevancia: ${Math.round((result.relevance / 20) * 100)}%
                                        </div>
                                        <button onclick="event.stopPropagation(); openTopicFromSearch('${result.id}')" 
                                                class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs transition-colors">
                                            Ver completo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `;
                    }
                });
                
                content.innerHTML = html;
            }
            
            modal.classList.remove('hidden');
        }

        function getCategoryColor(category) {
            const colorMap = {
                'Renovaciones': 'border-blue-500',
                'Documentación': 'border-green-500',
                'Costos': 'border-yellow-500',
                'Horarios': 'border-purple-500',
                'Especiales': 'border-red-500',
                'Preguntas Frecuentes': 'border-indigo-500',
                'Categoría': 'border-gray-500'
            };
            return colorMap[category] || 'border-gray-400';
        }

        // Función para abrir tema desde resultados de búsqueda
        function openTopicFromSearch(topicId) {
            // No hacer nada si es un mensaje del sistema
            if (topicId === 'invalid-search') {
                return;
            }
            
            // Cerrar modal de búsqueda
            closeModal('searchResultsModal');
            // Abrir modal del tema
            openTopicModal(topicId);
        }

        // Search suggestions functionality
        function showSearchSuggestions(input) {
            const suggestions = document.getElementById('searchSuggestions');
            const inputValue = input.toLowerCase().trim();
            
            if (inputValue.length < 2) {
                suggestions.classList.add('hidden');
                return;
            }

            // Generar términos dinámicamente desde el contenido cargado
            const allTerms = [];
            
            // Agregar títulos de categorías
            Object.values(allCategories).forEach(category => {
                allTerms.push(category.title.toLowerCase());
            });
            
            // Agregar títulos de temas
            Object.values(dynamicContent).forEach(topic => {
                allTerms.push(topic.title.toLowerCase());
                // Agregar palabras clave del contenido
                const words = topic.content.toLowerCase().split(/\s+/).filter(word => word.length > 4);
                allTerms.push(...words.slice(0, 3)); // Solo las primeras 3 palabras relevantes
            });
            
            // Agregar términos de la base de datos de búsqueda
            allTerms.push(...Object.keys(searchDatabase));
            
            // Eliminar duplicados y filtrar
            const uniqueTerms = [...new Set(allTerms)];
            const matchingSuggestions = uniqueTerms.filter(term => 
                term.includes(inputValue) && term !== inputValue
            ).slice(0, 5);

            if (matchingSuggestions.length > 0) {
                let html = '';
                matchingSuggestions.forEach(suggestion => {
                    html += `
                        <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer border-b border-gray-100 last:border-b-0" 
                             onclick="selectSuggestion('${suggestion}')">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                <span class="text-gray-700">${suggestion}</span>
                            </div>
                        </div>
                    `;
                });
                suggestions.innerHTML = html;
                suggestions.classList.remove('hidden');
            } else {
                suggestions.classList.add('hidden');
            }
        }

        function selectSuggestion(suggestion) {
            document.getElementById('searchInput').value = suggestion;
            document.getElementById('searchSuggestions').classList.add('hidden');
            searchKnowledge();
        }

        function hideSuggestions() {
            setTimeout(() => {
                document.getElementById('searchSuggestions').classList.add('hidden');
            }, 200);
        }

        // Función para guardar datos en database.json
        async function saveToDatabaseJson(data) {
            try {
                const response = await fetch('save_database.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });
                
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error saving to database.json:', error);
                // Fallback: guardar en localStorage
                localStorage.setItem('cescoDatabase', JSON.stringify(data));
                return { 
                    success: true, 
                    message: 'Datos guardados localmente como respaldo',
                    fallback: true
                };
            }
        }

        // Función para obtener datos en formato database.json
        function getDatabaseJsonData() {
            const jsonData = {
                categories: [],
                topics: []
            };
            
            // Agregar categorías
            for (const [id, category] of Object.entries(allCategories)) {
                jsonData.categories.push({
                    id: category.id,
                    title: category.title,
                    description: category.description,
                    icon: category.icon,
                    color: category.color,
                    topics: category.topics || []
                });
            }
            
            // Agregar temas
            for (const [id, item] of Object.entries(dynamicContent)) {
                jsonData.topics.push({
                    timestamp: item.timestamp || new Date().toISOString(),
                    title: item.title,
                    content: item.content,
                    id: item.id || id,
                    categoryId: item.categoryId || null, // Incluir categoría asignada
                    files: item.files || [] // Incluir archivos adjuntos
                });
            }
            
            return jsonData;
        }

        function downloadDatabaseJson() {
            const data = getDatabaseJsonData();
            const jsonString = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonString], { type: 'application/json' });
            const url = URL.createObjectURL(blob);
            
            const downloadLink = document.createElement('a');
            downloadLink.href = url;
            downloadLink.download = 'database.json';
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
            URL.revokeObjectURL(url);
        }

        // Form handlers
        document.getElementById('addInfoForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const title = formData.get('title');
            const content = formData.get('content');
            const categoryId = document.getElementById('infoCategory').value;
            const files = document.getElementById('infoFiles').files;
            
            if (title && content) {
                // Subir archivos primero
                const uploadResult = await uploadFiles(files);
                
                if (uploadResult.errors.length > 0) {
                    alert('Errores al subir archivos:\n' + uploadResult.errors.join('\n'));
                }
                
                // Crear nuevo tema
                const topicId = title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
                const newTopic = {
                    id: topicId,
                    title: title,
                    content: content,
                    categoryId: categoryId || null,
                    files: uploadResult.files || [],
                    timestamp: new Date().toISOString()
                };
                
                // Agregar al contenido dinámico
                dynamicContent[topicId] = newTopic;
                
                // Agregar a la base de datos de búsqueda
                const searchKey = title.toLowerCase();
                const categoryName = categoryId && allCategories[categoryId] 
                    ? allCategories[categoryId].title 
                    : "Información";
                    
                searchDatabase[searchKey] = {
                    title: title,
                    category: categoryName,
                    content: content.substring(0, 200) + "..."
                };
                
                // Guardar en database.json
                const data = getDatabaseJsonData();
                const result = await saveToDatabaseJson(data);
                
                if (result.success) {
                    alert(`Información "${title}" guardada exitosamente.`);
                    // Recargar categorías para mostrar el nuevo contenido
                    loadCategories();
                } else {
                    alert('Error al guardar la información: ' + result.error);
                }
                
                closeModal('addInfoModal');
                this.reset();
            }
        });

        // Form handler para editar categoría
        document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const categoryId = document.getElementById('editCategoryId').value;
            const name = document.getElementById('editCategoryName').value;
            const description = document.getElementById('editCategoryDescription').value;
            const icon = document.getElementById('editCategoryIcon').value;
            const color = document.getElementById('editCategoryColor').value;
            
            if (name && description && icon && color) {
                // Actualizar categoría
                allCategories[categoryId] = {
                    id: categoryId,
                    title: name,
                    description: description,
                    icon: icon,
                    color: color,
                    topics: allCategories[categoryId].topics || []
                };
                
                // Guardar en database.json
                const data = getDatabaseJsonData();
                const result = await saveToDatabaseJson(data);
                
                if (result.success) {
                    alert(`Categoría "${name}" actualizada exitosamente.`);
                    loadCategories();
                    buildSearchDatabase();
                } else {
                    alert('Error al actualizar la categoría: ' + result.error);
                }
                
                closeModal('editCategoryModal');
            }
        });

        // Form handler para crear nueva categoría
        document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const title = formData.get('title');
            const description = formData.get('description');
            const icon = formData.get('icon');
            const color = formData.get('color');
            
            if (title && description && icon) {
                // Generar ID único para la categoría
                const categoryId = title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
                
                // Verificar que no exista ya una categoría con ese ID
                if (allCategories[categoryId]) {
                    alert('Ya existe una categoría con ese nombre. Por favor, elige un nombre diferente.');
                    return;
                }
                
                // Crear nueva categoría
                const newCategory = {
                    id: categoryId,
                    title: title,
                    description: description,
                    icon: icon,
                    color: color,
                    topics: []
                };
                
                // Agregar a las categorías
                allCategories[categoryId] = newCategory;
                
                // Guardar en database.json
                const data = getDatabaseJsonData();
                const result = await saveToDatabaseJson(data);
                
                if (result.success) {
                    alert(`Categoría "${title}" creada exitosamente.`);
                    // Recargar categorías para mostrar la nueva
                    loadCategories();
                    populateCategoryFilter();
                    populateCategorySelectors();
                    updateContentList();
                } else {
                    alert('Error al crear la categoría: ' + result.error);
                }
                
                closeModal('addCategoryModal');
            }
        });

        // Form handler para editar tema
        document.getElementById('editTopicForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const topicId = formData.get('topicId');
            const title = formData.get('title');
            const content = formData.get('content');
            const categoryId = document.getElementById('editTopicCategory').value;
            const files = document.getElementById('editTopicFiles').files;
            
            if (title && content) {
                // Subir nuevos archivos
                const uploadResult = await uploadFiles(files);
                
                if (uploadResult.errors.length > 0) {
                    alert('Errores al subir archivos:\n' + uploadResult.errors.join('\n'));
                }
                
                // Mantener archivos existentes y agregar nuevos
                const existingFiles = dynamicContent[topicId].files || [];
                const allFiles = [...existingFiles, ...uploadResult.files];
                
                // Actualizar tema
                dynamicContent[topicId] = {
                    id: topicId,
                    title: title,
                    content: content,
                    categoryId: categoryId || null,
                    files: allFiles,
                    timestamp: dynamicContent[topicId].timestamp || new Date().toISOString()
                };
                
                // Actualizar base de datos de búsqueda
                const searchKey = title.toLowerCase();
                const categoryName = categoryId && allCategories[categoryId] 
                    ? allCategories[categoryId].title 
                    : "Información";
                    
                searchDatabase[searchKey] = {
                    title: title,
                    category: categoryName,
                    content: content.substring(0, 200) + "..."
                };
                
                // Guardar en database.json
                const data = getDatabaseJsonData();
                const result = await saveToDatabaseJson(data);
                
                if (result.success) {
                    alert(`Tema "${title}" actualizado exitosamente.`);
                    loadCategories();
                } else {
                    alert('Error al actualizar el tema: ' + result.error);
                }
                
                closeModal('editTopicModal');
            }
        });

        // Form handler para agregar tema a categoría específica
        document.getElementById('addTopicToCategoryForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const categoryId = formData.get('categoryId');
            const title = formData.get('title');
            const content = formData.get('content');
            const files = document.getElementById('categoryTopicFiles').files;
            
            if (title && content && categoryId) {
                // Subir archivos primero
                const uploadResult = await uploadFiles(files);
                
                if (uploadResult.errors.length > 0) {
                    alert('Errores al subir archivos:\n' + uploadResult.errors.join('\n'));
                }
                
                // Crear nuevo tema con categoría asignada
                const topicId = title.toLowerCase().replace(/\s+/g, '-').replace(/[^\w-]/g, '');
                const newTopic = {
                    id: topicId,
                    title: title,
                    content: content,
                    categoryId: categoryId, // Asignar categoría específica
                    files: uploadResult.files || [],
                    timestamp: new Date().toISOString()
                };
                
                // Agregar al contenido dinámico
                dynamicContent[topicId] = newTopic;
                
                // Agregar a la categoría
                if (!allCategories[categoryId].topics) {
                    allCategories[categoryId].topics = [];
                }
                allCategories[categoryId].topics.push(title);
                
                // Agregar a la base de datos de búsqueda
                const searchKey = title.toLowerCase();
                searchDatabase[searchKey] = {
                    title: title,
                    category: allCategories[categoryId].title,
                    content: content.substring(0, 200) + "..."
                };
                
                // Guardar en database.json
                const data = getDatabaseJsonData();
                const result = await saveToDatabaseJson(data);
                
                if (result.success) {
                    alert(`Tema "${title}" agregado exitosamente a la categoría "${allCategories[categoryId].title}".`);
                    // Recargar categorías para mostrar el nuevo contenido
                    loadCategories();
                    buildSearchDatabase();
                } else {
                    alert('Error al guardar el tema: ' + result.error);
                }
                
                closeModal('addTopicToCategoryModal');
                this.reset();
            }
        });

        document.getElementById('chatForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const message = document.getElementById('chatInput').value;
            
            if (message.trim()) {
                const chatContainer = document.getElementById('chatContainer');
                
                // Add user message
                chatContainer.innerHTML += `
                    <div class="flex justify-end mb-4">
                        <div class="bg-blue-500 text-white rounded-lg px-4 py-2 max-w-md">
                            ${message}
                        </div>
                    </div>
                `;
                
                // Add loading indicator
                chatContainer.innerHTML += `
                    <div class="flex justify-start mb-4" id="loadingMessage">
                        <div class="bg-gray-100 text-gray-600 rounded-lg px-4 py-2 max-w-md">
                            <div class="flex items-center gap-2">
                                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-500"></div>
                                Procesando consulta...
                            </div>
                        </div>
                    </div>
                `;
                
                chatContainer.scrollTop = chatContainer.scrollHeight;
                
                try {
                    // Send request to consultas.php
                    const formData = new FormData();
                    formData.append('pregunta', message);
                    
                    const response = await fetch('consultas.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    // Remove loading indicator
                    const loadingElement = document.getElementById('loadingMessage');
                    if (loadingElement) {
                        loadingElement.remove();
                    }
                    
                    // Add bot response
                    chatContainer.innerHTML += `
                        <div class="flex justify-start mb-4">
                            <div class="bg-gray-100 text-gray-800 rounded-lg px-4 py-2 max-w-md">
                                <div class="whitespace-pre-wrap">${data.respuesta || 'No se pudo procesar la consulta.'}</div>
                            </div>
                        </div>
                    `;
                    
                } catch (error) {
                    console.error('Error en consulta:', error);
                    
                    // Remove loading indicator
                    const loadingElement = document.getElementById('loadingMessage');
                    if (loadingElement) {
                        loadingElement.remove();
                    }
                    
                    // Add error message
                    chatContainer.innerHTML += `
                        <div class="flex justify-start mb-4">
                            <div class="bg-red-100 text-red-800 rounded-lg px-4 py-2 max-w-md">
                                Lo siento, hubo un error al procesar tu consulta. Por favor, intenta nuevamente.
                            </div>
                        </div>
                    `;
                }
                
                this.reset();
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });

        // Close modals when clicking outside
        document.getElementById('categoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('categoryModal');
        });

        document.getElementById('searchResultsModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('searchResultsModal');
        });

        document.getElementById('addInfoModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('addInfoModal');
        });

        document.getElementById('editCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('editCategoryModal');
        });

        document.getElementById('editTopicModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('editTopicModal');
        });

        document.getElementById('addTopicToCategoryModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal('addTopicToCategoryModal');
        });

        document.getElementById('chatModal').addEventListener('click', function(e) {
            if (e.target === this) closeChat();
        });

        // Search on Enter key and input events
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('searchSuggestions').classList.add('hidden');
                searchKnowledge();
            }
        });

        // Show suggestions on input
        document.getElementById('searchInput').addEventListener('input', function(e) {
            showSearchSuggestions(e.target.value);
        });

        // Hide suggestions when input loses focus
        document.getElementById('searchInput').addEventListener('blur', function(e) {
            hideSuggestions();
        });

        // Show suggestions when input gains focus (if there's text)
        document.getElementById('searchInput').addEventListener('focus', function(e) {
            if (e.target.value.trim().length >= 2) {
                showSearchSuggestions(e.target.value);
            }
        });

        // Función para actualizar la lista de contenido
        function updateContentList() {
            const sortBy = document.getElementById('sortBy').value;
            const filterCategory = document.getElementById('filterCategory').value;
            const container = document.getElementById('contentListContainer');
            const statsContainer = document.getElementById('contentStats');
            
            if (!container) return;
            
            // Obtener todos los temas
            let allTopics = Object.values(dynamicContent);
            let topics = [...allTopics];
            
            // Filtrar por categoría si es necesario
            if (filterCategory !== 'all') {
                topics = topics.filter(topic => topic.categoryId === filterCategory);
            }
            
            // Actualizar estadísticas
            if (statsContainer) {
                const totalFiles = allTopics.reduce((sum, topic) => sum + (topic.files ? topic.files.length : 0), 0);
                const categoriesCount = new Set(allTopics.map(topic => topic.categoryId).filter(Boolean)).size;
                
                statsContainer.innerHTML = `
                    <div class="flex items-center gap-4 text-sm">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            ${allTopics.length} temas
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            ${categoriesCount} categorías
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                            </svg>
                            ${totalFiles} archivos
                        </span>
                    </div>
                `;
            }
            
            // Ordenar según la opción seleccionada
            topics.sort((a, b) => {
                switch (sortBy) {
                    case 'date':
                        return new Date(b.timestamp) - new Date(a.timestamp);
                    case 'category':
                        const catA = a.categoryId && allCategories[a.categoryId] ? allCategories[a.categoryId].title : 'Sin categoría';
                        const catB = b.categoryId && allCategories[b.categoryId] ? allCategories[b.categoryId].title : 'Sin categoría';
                        return catA.localeCompare(catB);
                    case 'title':
                        return a.title.localeCompare(b.title);
                    default:
                        return 0;
                }
            });
            
            // Generar HTML
            if (topics.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <svg class="w-12 h-12 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">No hay contenido disponible</h3>
                        <p class="text-gray-600 text-lg mb-6">El contenido aparecerá aquí cuando se agregue información al sistema.</p>
                        <button onclick="openAddInfoModal()" class="bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition-all shadow-lg">
                            ✨ Agregar Primer Tema
                        </button>
                    </div>
                `;
                return;
            }
            
            let html = '<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">';
            
            topics.forEach((topic, index) => {
                const category = topic.categoryId && allCategories[topic.categoryId] 
                    ? allCategories[topic.categoryId] 
                    : { title: 'Sin categoría', icon: '📄', color: 'gray' };
                
                const date = new Date(topic.timestamp);
                const formattedDate = date.toLocaleDateString('es-ES', {
                    day: 'numeric',
                    month: 'short',
                    year: 'numeric'
                });
                
                const hasFiles = topic.files && topic.files.length > 0;
                const isLast = index === topics.length - 1;
                
                html += `
                    <div class="group hover:bg-blue-50 transition-colors ${!isLast ? 'border-b border-gray-100' : ''}">
                        <div class="p-4 flex items-center justify-between">
                            <!-- Left Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-lg flex-shrink-0">${category.icon}</span>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-base font-semibold text-gray-900 cursor-pointer hover:text-blue-600 transition-colors truncate" onclick="openTopicModal('${topic.id}')">
                                            ${topic.title}
                                        </h3>
                                        <div class="flex items-center gap-3 text-xs text-gray-500 mt-1">
                                            <span class="px-2 py-1 bg-gray-100 rounded-full font-medium">
                                                ${category.title}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 011-1h6a1 1 0 011 1v4m-6 0h6m-6 0a1 1 0 00-1 1v10a1 1 0 001 1h6a1 1 0 001-1V8a1 1 0 00-1-1m-6 0V7"></path>
                                                </svg>
                                                ${formattedDate}
                                            </span>
                                            ${hasFiles ? `
                                                <span class="flex items-center gap-1 text-blue-600">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                                    </svg>
                                                    ${topic.files.length} archivo${topic.files.length > 1 ? 's' : ''}
                                                </span>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600 leading-relaxed cursor-pointer hover:text-gray-800 transition-colors ml-8 line-clamp-2" onclick="openTopicModal('${topic.id}')">
                                    ${topic.content.replace(/<[^>]*>/g, '').substring(0, 200)}...
                                </p>
                            </div>
                            
                            <!-- Right Actions -->
                            <div class="flex items-center gap-2 ml-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="openTopicModal('${topic.id}')" 
                                        class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg transition-colors" title="Ver tema">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                                <button onclick="openEditTopicModal('${topic.id}')" 
                                        class="bg-gray-500 hover:bg-gray-600 text-white p-2 rounded-lg transition-colors" title="Editar tema">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }
        
        // Función para poblar el filtro de categorías
        function populateCategoryFilter() {
            const filterSelect = document.getElementById('filterCategory');
            if (!filterSelect) return;
            
            // Limpiar opciones existentes (excepto "Todas")
            filterSelect.innerHTML = '<option value="all">Todas las Categorías</option>';
            
            // Agregar categorías
            for (const [categoryId, category] of Object.entries(allCategories)) {
                const option = document.createElement('option');
                option.value = categoryId;
                option.textContent = `${category.icon} ${category.title}`;
                filterSelect.appendChild(option);
            }
        }

        // Función para subir archivos
        async function uploadFiles(files) {
            if (!files || files.length === 0) {
                return { files: [], errors: [] };
            }
            
            const formData = new FormData();
            for (let i = 0; i < files.length; i++) {
                formData.append('files[]', files[i]);
            }
            
            try {
                const response = await fetch('upload_files.php', {
                    method: 'POST',
                    body: formData
                });
                
                if (!response.ok) {
                    throw new Error('Error en la subida de archivos');
                }
                
                return await response.json();
            } catch (error) {
                console.error('Error uploading files:', error);
                return { files: [], errors: ['Error al subir archivos: ' + error.message] };
            }
        }
        
        // Función para mostrar archivos existentes
        function displayExistingFiles(files, containerId, topicId = null) {
            const container = document.getElementById(containerId);
            if (!container || !files || files.length === 0) {
                if (container) container.innerHTML = '';
                return;
            }
            
            let html = '<div class="mt-2"><h6 class="text-sm font-medium text-gray-700 mb-2">Archivos actuales:</h6><div class="space-y-2">';
            
            files.forEach((file, index) => {
                const fileIcon = getFileIcon(file.file_type);
                html += `
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border">
                        <div class="flex items-center gap-3">
                            <span class="text-xl">${fileIcon}</span>
                            <div>
                                <p class="font-medium text-gray-900 text-sm">${file.original_name}</p>
                                <p class="text-xs text-gray-500">${formatFileSize(file.file_size)} • ${file.file_type.toUpperCase()}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="${file.file_path}" target="_blank" download="${file.original_name}"
                               class="bg-blue-500 hover:bg-blue-600 text-white p-1.5 rounded transition-colors">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </a>
                            ${topicId ? `
                                <button type="button" onclick="removeFile('${file.file_name}', ${index}, '${topicId}')" 
                                        class="text-red-500 hover:text-red-700 p-1.5">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                `;
            });
            
            html += '</div></div>';
            container.innerHTML = html;
        }
        
        // Función para obtener icono de archivo
        function getFileIcon(fileType) {
            switch (fileType.toLowerCase()) {
                case 'pdf': return '📄';
                case 'doc':
                case 'docx': return '📝';
                case 'jpg':
                case 'jpeg':
                case 'png':
                case 'gif': return '🖼️';
                default: return '📎';
            }
        }
        
        // Función para formatear tamaño de archivo
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Función para eliminar archivo
        function removeFile(fileName, index, topicId) {
            if (confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
                const topic = dynamicContent[topicId];
                if (topic && topic.files) {
                    // Eliminar archivo del array
                    topic.files.splice(index, 1);
                    
                    // Mostrar archivos existentes
                    displayExistingFiles(topic.files || [], 'existingFiles', topicId);
                    
                    // Guardar cambios
                    saveDatabaseChanges();
                }
            }
        }
        
        // Función para guardar cambios en la base de datos
        async function saveDatabaseChanges() {
            const data = getDatabaseJsonData();
            const result = await saveToDatabaseJson(data);
            
            if (!result.success) {
                alert('Error al guardar los cambios: ' + result.error);
            }
        }

        // Función para poblar selectores de categorías en formularios
        function populateCategorySelectors() {
            const selectors = ['editTopicCategory', 'infoCategory'];
            
            selectors.forEach(selectorId => {
                const select = document.getElementById(selectorId);
                if (!select) return;
                
                // Guardar la opción seleccionada actual
                const currentValue = select.value;
                
                // Limpiar opciones existentes
                if (selectorId === 'editTopicCategory') {
                    select.innerHTML = '<option value="">Seleccionar categoría</option>';
                } else {
                    select.innerHTML = '<option value="">Sin categoría específica</option>';
                }
                
                // Agregar categorías
                for (const [categoryId, category] of Object.entries(allCategories)) {
                    const option = document.createElement('option');
                    option.value = categoryId;
                    option.textContent = `${category.icon} ${category.title}`;
                    select.appendChild(option);
                }
                
                // Restaurar valor seleccionado
                if (currentValue) {
                    select.value = currentValue;
                }
            });
        }

        // Load content on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadDynamicContent();
        });
    </script>

<?php endif; ?>

</body>
</html>
