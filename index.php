<?php
session_start();

// Credenciales de acceso con roles
$valid_users = [
    'admin' => [
        'password' => 'pr@2025',
        'role' => 'administrator',
        'name' => 'Administrador'
    ],
    'operador' => [
        'password' => 'op@2025',
        'role' => 'operator',
        'name' => 'Operador'
    ]
];

// Procesar login
if (isset($_POST['action']) && $_POST['action'] === 'login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (isset($valid_users[$username]) && $valid_users[$username]['password'] === $password) {
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $valid_users[$username]['role'];
        $_SESSION['user_name'] = $valid_users[$username]['name'];
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

// Procesar guardado de configuración (solo administradores)
if (isset($_POST['action']) && $_POST['action'] === 'save_config') {
    // Verificar que el usuario sea administrador
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'administrator') {
        $error_message = 'No tienes permisos para realizar esta acción';
    } else {
        $config = [
            'selected_ai' => $_POST['selected_ai'] ?? 'gemini',
            'gemini_api_key' => $_POST['gemini_api_key'] ?? '',
            'gemini_prompt' => $_POST['gemini_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.',
            'chatgpt_api_key' => $_POST['chatgpt_api_key'] ?? '',
            'chatgpt_prompt' => $_POST['chatgpt_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.',
            'claude_api_key' => $_POST['claude_api_key'] ?? '',
            'claude_prompt' => $_POST['claude_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'
        ];
        
        file_put_contents('config.json', json_encode($config, JSON_PRETTY_PRINT));
        $_SESSION['config_saved'] = true;
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Cargar configuración
$config = [];
if (file_exists('config.json')) {
    $config = json_decode(file_get_contents('config.json'), true) ?? [];
}

// Verificar si está logueado
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

// Funciones auxiliares para permisos
function isAdministrator() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'administrator';
}

function isOperator() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'operator';
}

function getUserRole() {
    return $_SESSION['user_role'] ?? 'guest';
}

function getUserName() {
    return $_SESSION['user_name'] ?? 'Usuario';
}
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
            background-color: #EBF2E8;
        }
        
        /* Mejoras para móviles */
        @media (max-width: 640px) {
            .login-container {
                padding: 1rem;
            }
            
            /* Evitar zoom en inputs en iOS */
            input[type="text"], input[type="password"] {
                font-size: 16px;
            }
            
            /* Mejorar área táctil de botones */
            button {
                min-height: 44px;
            }
        }
        
        /* Animaciones suaves para interacciones */
        .login-form-input {
            transition: all 0.3s ease;
        }
        
        .login-form-input:focus {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        /* Estilos para el menú móvil */
        .safe-area-pb {
            padding-bottom: env(safe-area-inset-bottom);
        }
        
        .mobile-nav-item {
            -webkit-tap-highlight-color: transparent;
        }
        
        .mobile-nav-item:active {
            transform: scale(0.95);
        }
        
        .mobile-drawer-item:active {
            transform: scale(0.98);
        }
        
        /* Animación para el drawer */
        #mobileDrawer {
            backdrop-filter: blur(10px);
        }
        
        /* Mejorar el handle del drawer */
        .drawer-handle {
            background: linear-gradient(90deg, transparent, #d1d5db, transparent);
        }
        
        /* Controlar scroll en móviles */
        @media (max-width: 1023px) {
            body {
                overflow-x: hidden;
            }
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
        
        /* Editor HTML Styles */
        .html-editor {
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            overflow: hidden;
        }
        
        .editor-toolbar {
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.5rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.25rem;
        }
        
        .editor-btn {
            padding: 0.375rem 0.5rem;
            border: 1px solid #d1d5db;
            background: white;
            border-radius: 0.25rem;
            cursor: pointer;
            font-size: 0.875rem;
            transition: all 0.2s;
            min-width: 32px;
            text-align: center;
        }
        
        .editor-btn:hover {
            background: #f3f4f6;
            border-color: #9ca3af;
        }
        
        .editor-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .editor-content {
            min-height: 200px;
            padding: 0.75rem;
            outline: none;
            font-family: inherit;
            font-size: 0.875rem;
            line-height: 1.5;
        }
        
        .editor-content:focus {
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.1);
        }
        
        /* Estilos para el contenido del editor */
        .editor-content h1, .editor-content h2, .editor-content h3 {
            font-weight: bold;
            margin: 1rem 0 0.5rem 0;
        }
        
        .editor-content h1 { font-size: 1.5rem; }
        .editor-content h2 { font-size: 1.25rem; }
        .editor-content h3 { font-size: 1.125rem; }
        
        .editor-content ul, .editor-content ol {
            margin: 0.5rem 0;
            padding-left: 2rem;
        }
        
        .editor-content li {
            margin: 0.25rem 0;
        }
        
        .editor-content p {
            margin: 0.5rem 0;
        }
        
        .editor-content strong {
            font-weight: bold;
        }
        
        .editor-content em {
            font-style: italic;
        }
        
        .editor-content u {
            text-decoration: underline;
        }
        
        /* Placeholder para editor */
        .editor-content:empty:before {
            content: attr(data-placeholder);
            color: #9ca3af;
            font-style: italic;
        }
        
        .editor-content:focus:before {
            display: none;
        }
        
        /* Colores de fondo suaves para categorías */
        .category-bg-blue {
            background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
            border-color: #93c5fd;
        }
        
        .category-bg-green {
            background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
            border-color: #86efac;
        }
        
        .category-bg-yellow {
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%);
            border-color: #fde047;
        }
        
        .category-bg-purple {
            background: linear-gradient(135deg, #f3e8ff 0%, #e9d5ff 100%);
            border-color: #c084fc;
        }
        
        .category-bg-red {
            background: linear-gradient(135deg, #fef2f2 0%, #fecaca 100%);
            border-color: #fca5a5;
        }
        
        .category-bg-indigo {
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            border-color: #a5b4fc;
        }
        
        /* Hover effects para categorías */
        .category-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50 font-inter min-h-screen flex flex-col">

<?php if (!$is_logged_in): ?>
    <!-- Login Page -->
    <div class="login-container min-h-screen flex items-center justify-center px-4 py-8">
        <div class="bg-white rounded-2xl shadow-2xl p-6 sm:p-8 w-full max-w-md mx-auto">
            <div class="text-center mb-6 sm:mb-8">
                <div class="mb-4 sm:mb-6">
                    <img src="https://app.cescoonline.com:7443/image/logo.png?v=<?php echo time(); ?>" alt="CESCO Online" 
                         class="mx-auto w-24 sm:w-32 h-auto" />
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-800 mb-2">Acceso al Sistema</h1>
                <p class="text-sm sm:text-base text-gray-600">Base de Conocimientos CESCO</p>
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

            <form method="POST" class="space-y-4 sm:space-y-6">
                <input type="hidden" name="action" value="login">
                
                <div>
                    <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-gray-500"></i>Usuario
                    </label>
                    <input type="text" id="username" name="username" required
                           class="login-form-input w-full px-4 py-3 sm:py-4 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition-colors text-base"
                           placeholder="Ingrese su usuario"
                           autocomplete="username">
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-gray-500"></i>Contraseña
                    </label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required
                               class="login-form-input w-full px-4 py-3 sm:py-4 pr-12 border-2 border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none transition-colors text-base"
                               placeholder="Ingrese su contraseña"
                               autocomplete="current-password">
                        <button type="button" onclick="togglePasswordVisibility()" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                            <i id="passwordToggleIcon" class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" 
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 sm:py-4 px-4 rounded-lg font-semibold hover:from-blue-600 hover:to-blue-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] text-base">
                    <i class="fas fa-sign-in-alt mr-2"></i>Iniciar Sesión
                </button>
            </form>

            <div class="mt-6 sm:mt-8 text-center">
                <div class="bg-gray-50 rounded-lg p-3 sm:p-4">
                    <h3 class="text-xs sm:text-sm font-semibold text-gray-700 mb-3">
                        <i class="fas fa-info-circle mr-1"></i>Credenciales de Acceso
                    </h3>
                    <div class="text-xs sm:text-sm text-gray-600 space-y-2 sm:space-y-3">
                        <div class="bg-white rounded-md p-2 sm:p-3">
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-user-shield text-blue-500 mr-2"></i>
                                <strong class="text-gray-800">Administrador</strong>
                            </div>
                            <div class="text-xs text-gray-600">
                                <div>Usuario: <span class="font-mono bg-gray-100 px-1 rounded">admin</span></div>
                                <div>Contraseña: <span class="font-mono bg-gray-100 px-1 rounded">pr@2025</span></div>
                            </div>
                        </div>
                        <div class="bg-white rounded-md p-2 sm:p-3">
                            <div class="flex items-center justify-center mb-1">
                                <i class="fas fa-user text-green-500 mr-2"></i>
                                <strong class="text-gray-800">Operador</strong>
                            </div>
                            <div class="text-xs text-gray-600">
                                <div>Usuario: <span class="font-mono bg-gray-100 px-1 rounded">operador</span></div>
                                <div>Contraseña: <span class="font-mono bg-gray-100 px-1 rounded">op@2025</span></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php else: ?>
    <div class="flex-1 flex flex-col">
    <!-- Header tipo help.tawk.to -->
    <header class="border-b border-gray-200 sticky top-0 z-50" style="background-color: #EBF2E8;">
        <div class="w-[98%] lg:w-[90%] max-w-7xl mx-auto px-4 lg:px-6 py-4">
            <!-- Mobile Layout: Solo logo centrado -->
            <div class="lg:hidden flex justify-center">
                <img src="https://app.cescoonline.com:7443/image/logo.png" alt="CESCO Online" class="h-8 w-auto">
            </div>
            
            <!-- Desktop Layout: Grid completo -->
            <div class="hidden lg:grid grid-cols-3 items-center">
                <!-- Left: Logo -->
                <div class="flex items-center space-x-4">
                    <img src="https://app.cescoonline.com:7443/image/logo.png" alt="CESCO Online" class="h-8 w-auto">
                </div>
                
                <!-- Center: Search -->
                <div class="flex justify-center">
                    <div class="relative w-full max-w-md">
                        <input type="text" id="headerSearch" 
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                               placeholder="Buscar en la base de conocimientos...">
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                </div>
                
                <!-- Right: User Menu -->
                <div class="flex justify-end">
                    <div class="flex items-center space-x-2 lg:space-x-3">
                    <div class="text-xs lg:text-sm text-gray-600 hidden sm:block text-right">
                        <div>Hola, <?php echo htmlspecialchars(getUserName()); ?></div>
                        <div class="text-xs text-gray-500"><?php echo getUserRole() === 'administrator' ? 'Administrador' : 'Operador'; ?></div>
                    </div>
                    <div class="relative">
                        <button onclick="toggleUserMenu()" class="flex items-center space-x-1 lg:space-x-2 text-gray-600 hover:text-gray-900">
                            <div class="w-8 h-8 <?php echo getUserRole() === 'administrator' ? 'bg-primary' : 'bg-green-500'; ?> text-white rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-sm"></i>
                            </div>
                            <i class="fas fa-chevron-down text-xs hidden sm:block"></i>
                        </button>
                        <div id="userMenu" class="hidden absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                            <div class="px-4 py-2 border-b border-gray-100">
                                <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars(getUserName()); ?></div>
                                <div class="text-xs text-gray-500"><?php echo getUserRole() === 'administrator' ? 'Administrador' : 'Operador'; ?></div>
                            </div>
                            <?php if (isAdministrator()): ?>
                            <a href="#" onclick="showSection('settings')" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog mr-2"></i>Configuración
                            </a>
                            <hr class="my-1">
                            <?php endif; ?>
                            <a href="?action=logout" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                <i class="fas fa-sign-out-alt mr-2"></i>Cerrar Sesión
                            </a>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile search bar (hidden - using bottom nav search instead) -->
        <div id="mobileSearch" class="hidden border-t border-gray-200" style="background-color: #EBF2E8;">
            <div class="w-[98%] lg:w-[90%] max-w-7xl mx-auto px-4 py-4">
                <div class="relative">
                    <input type="text" id="mobileSearchInput" 
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent text-sm"
                           placeholder="Buscar en la base de conocimientos...">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Layout Principal -->
    <div class="flex min-h-screen lg:h-screen pt-5">
        <!-- Container centrado: 98% móvil, 90% desktop -->
        <div class="w-[98%] lg:w-[90%] max-w-7xl mx-auto flex min-h-full lg:h-full">
        
        <!-- Sidebar -->
        <aside id="sidebar" class="w-64 bg-sidebar border-r border-gray-200 overflow-y-auto transition-transform duration-300 ease-in-out lg:translate-x-0 -translate-x-full fixed lg:relative z-40 h-full">
            <nav class="p-4">
                <div class="space-y-2">
                    <button onclick="showSection('dashboard')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-home mr-3 text-gray-400"></i>
                        <span>Inicio</span>
                    </button>
                    
                    <button onclick="showSection('categories')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-folder mr-3 text-gray-400"></i>
                        <span>Categorías</span>
                    </button>
                    
                    <button onclick="showSection('content')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-file-alt mr-3 text-gray-400"></i>
                        <span>Contenido</span>
                    </button>
                    
                    <button onclick="showSection('search')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-search mr-3 text-gray-400"></i>
                        <span>Búsqueda Avanzada</span>
                    </button>
                    
                    <button onclick="showSection('chat')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-robot mr-3 text-gray-400"></i>
                        <span>Asistente IA</span>
                    </button>
                    
                    <?php if (isAdministrator()): ?>
                    <hr class="my-4">
                    
                    <button onclick="showSection('settings')" class="sidebar-item w-full flex items-center px-3 py-2 text-left text-gray-700 rounded-lg transition-colors hover:bg-sidebar-hover">
                        <i class="fas fa-cog mr-3 text-gray-400"></i>
                        <span>Configuración</span>
                    </button>
                    <?php endif; ?>
                </div>
            </nav>
        </aside>
        
        <!-- Mobile sidebar overlay -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 lg:hidden hidden" onclick="toggleMobileMenu()"></div>

        <!-- Mobile Bottom Navigation -->
        <nav class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 z-50 safe-area-pb">
            <div class="grid grid-cols-4 h-16">
                <!-- Inicio -->
                <button onclick="showSection('dashboard'); closeMobileMenu();" 
                        class="mobile-nav-item flex flex-col items-center justify-center space-y-1 text-gray-600 hover:text-blue-600 transition-colors"
                        data-section="dashboard">
                    <i class="fas fa-home text-lg"></i>
                    <span class="text-xs font-medium">Inicio</span>
                </button>
                
                <!-- Categorías -->
                <button onclick="showSection('categories'); closeMobileMenu();" 
                        class="mobile-nav-item flex flex-col items-center justify-center space-y-1 text-gray-600 hover:text-blue-600 transition-colors"
                        data-section="categories">
                    <i class="fas fa-folder text-lg"></i>
                    <span class="text-xs font-medium">Categorías</span>
                </button>
                
                <!-- Búsqueda -->
                <button onclick="showSection('search'); closeMobileMenu();" 
                        class="mobile-nav-item flex flex-col items-center justify-center space-y-1 text-gray-600 hover:text-blue-600 transition-colors"
                        data-section="search">
                    <i class="fas fa-search text-lg"></i>
                    <span class="text-xs font-medium">Buscar</span>
                </button>
                
                <!-- Menú -->
                <button onclick="toggleMobileDrawer()" 
                        class="mobile-nav-item flex flex-col items-center justify-center space-y-1 text-gray-600 hover:text-blue-600 transition-colors">
                    <i class="fas fa-bars text-lg"></i>
                    <span class="text-xs font-medium">Menú</span>
                </button>
            </div>
        </nav>

        <!-- Mobile Drawer Menu -->
        <div id="mobileDrawer" class="lg:hidden fixed inset-0 z-50 transform translate-y-full transition-transform duration-300 ease-out">
            <!-- Overlay -->
            <div class="absolute inset-0 bg-black bg-opacity-50" onclick="closeMobileDrawer()"></div>
            
            <!-- Drawer Content -->
            <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-2xl">
                <!-- Handle -->
                <div class="flex justify-center py-3">
                    <div class="w-12 h-1 bg-gray-300 rounded-full drawer-handle"></div>
                </div>
                
                <!-- Header -->
                <div class="px-6 pb-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($_SESSION['user_name']); ?></h3>
                                <p class="text-sm text-gray-500"><?php echo ucfirst($_SESSION['user_role']); ?></p>
                            </div>
                        </div>
                        <button onclick="closeMobileDrawer()" class="p-2 text-gray-400 hover:text-gray-600">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>
                
                <!-- Menu Items -->
                <div class="px-6 py-4 space-y-1 max-h-96 overflow-y-auto">
                    <button onclick="showSection('content'); closeMobileDrawer();" 
                            class="mobile-drawer-item w-full flex items-center space-x-4 px-4 py-3 text-left text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">Contenido</div>
                            <div class="text-sm text-gray-500">Ver todos los artículos</div>
                        </div>
                    </button>
                    
                    <?php if (isAdministrator()): ?>
                    <button onclick="showSection('settings'); closeMobileDrawer();" 
                            class="mobile-drawer-item w-full flex items-center space-x-4 px-4 py-3 text-left text-gray-700 rounded-xl hover:bg-gray-50 transition-colors">
                        <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-cog text-purple-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">Configuración</div>
                            <div class="text-sm text-gray-500">Ajustes del sistema</div>
                        </div>
                    </button>
                    <?php endif; ?>
                    
                    <div class="border-t border-gray-200 my-4"></div>
                    
                    <a href="?action=logout" 
                       class="mobile-drawer-item w-full flex items-center space-x-4 px-4 py-3 text-left text-red-600 rounded-xl hover:bg-red-50 transition-colors">
                        <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-sign-out-alt text-red-600"></i>
                        </div>
                        <div>
                            <div class="font-medium">Cerrar Sesión</div>
                            <div class="text-sm text-red-500">Salir del sistema</div>
                        </div>
                    </a>
                </div>
                
                <!-- Safe Area Bottom -->
                <div class="h-8 bg-white"></div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <main class="flex-1 lg:overflow-y-auto lg:ml-0 transition-all duration-300 pb-20 lg:pb-0">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="content-section pt-2 px-3 pb-3 sm:p-4 lg:p-6">
                <div class="max-w-6xl mx-auto overflow-hidden">
                    <!-- Mobile IA Chat Priority - PRIMERO -->
                    <div class="lg:hidden mb-4">
                        <!-- Chat IA Prominente -->
                        <div class="bg-gradient-to-br from-blue-500 via-purple-600 to-indigo-700 rounded-3xl p-4 mb-4 text-white shadow-2xl">
                            <div class="text-center mb-4">
                                <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mx-auto mb-3 animate-pulse">
                                    <i class="fas fa-robot text-white text-2xl"></i>
                                </div>
                                <h2 class="text-xl font-bold mb-1">¡Hola! Soy ANA</h2>
                                <p class="text-sm opacity-90">Tu asistente virtual para trámites CESCO</p>
                            </div>
                            
                            <!-- Quick Chat Input -->
                            <div class="bg-white bg-opacity-10 rounded-2xl p-4 backdrop-blur-sm">
                                <!-- Campo de texto arriba -->
                                <div class="mb-4">
                                    <textarea id="quickChatInput" rows="8"
                                              class="w-full bg-white bg-opacity-20 border border-white border-opacity-30 rounded-xl px-4 py-4 text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 text-base resize-none"
                                              placeholder="Escribe tu pregunta aquí...&#10;&#10;Puedes hacer preguntas sobre:&#10;• Renovación de licencias&#10;• Marbetes vehiculares&#10;• Documentos requeridos&#10;• Ubicaciones y horarios"></textarea>
                                </div>
                                
                                <!-- Botón abajo -->
                                <div class="text-center">
                                    <button onclick="sendQuickChatDirect()" 
                                            class="bg-white text-purple-600 px-8 py-3 rounded-xl hover:bg-opacity-90 transition-all shadow-lg font-semibold w-full">
                                        <i class="fas fa-paper-plane mr-2"></i>
                                        Consultar con ANA
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Compactas -->
                        <div class="grid grid-cols-2 gap-3 mb-6">
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-folder text-blue-600"></i>
                                    </div>
                                    <div>
                                        <p id="categoriesCountMobile" class="text-xl font-bold text-gray-900">0</p>
                                        <p class="text-xs text-gray-500">Categorías</p>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-white rounded-2xl p-4 shadow-sm border border-gray-200">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                        <i class="fas fa-file-alt text-green-600"></i>
                                    </div>
                                    <div>
                                        <p id="articlesCountMobile" class="text-xl font-bold text-gray-900">0</p>
                                        <p class="text-xs text-gray-500">Artículos</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Título de Bienvenida - Solo Móvil -->
                        <div class="text-center mb-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-2">¡Bienvenido al Centro de Ayuda CESCO!</h2>
                            <p class="text-sm text-gray-600 px-4">Encuentra toda la información que necesitas sobre trámites vehiculares en Puerto Rico.</p>
                        </div>
                    </div>

                    <!-- Título de Bienvenida - Solo Desktop -->
                    <div class="hidden lg:block mb-4 sm:mb-6">
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-2">¡Bienvenido al Centro de Ayuda CESCO!</h2>
                        <p class="text-sm sm:text-base text-gray-600">Encuentra toda la información que necesitas sobre trámites vehiculares en Puerto Rico.</p>
                    </div>

                    <!-- Stats Cards - Desktop -->
                    <div class="hidden lg:grid grid-cols-3 gap-6 mb-8">
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

                    <!-- Quick Actions - Desktop -->
                    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php if (isAdministrator()): ?>
                            <button onclick="openAddCategoryModal()" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-plus-circle text-blue-600 mr-3"></i>
                                <span class="text-sm font-medium">Nueva Categoría</span>
                            </button>
                            
                            <button onclick="openAddTopicModal()" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-file-plus text-green-600 mr-3"></i>
                                <span class="text-sm font-medium">Nuevo Artículo</span>
                            </button>
                            <?php endif; ?>
                            
                            <button onclick="showSection('chat')" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-robot text-purple-600 mr-3"></i>
                                <span class="text-sm font-medium">Abrir Chat IA</span>
                            </button>
                            
                            <?php if (isAdministrator()): ?>
                            <button onclick="showSection('settings')" class="flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cog text-gray-600 mr-3"></i>
                                <span class="text-sm font-medium">Configuración</span>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Quick Actions - Mobile -->
                    <div class="lg:hidden mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h3>
                        <div class="grid grid-cols-2 gap-3">
                            <button onclick="showSection('categories')" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <div class="text-center">
                                    <i class="fas fa-folder text-2xl mb-2"></i>
                                    <p class="text-sm font-semibold">Ver Categorías</p>
                                </div>
                            </button>
                            
                            <button onclick="showSection('content')" class="bg-gradient-to-r from-green-500 to-green-600 text-white p-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <div class="text-center">
                                    <i class="fas fa-file-alt text-2xl mb-2"></i>
                                    <p class="text-sm font-semibold">Ver Contenido</p>
                                </div>
                            </button>
                            
                            <button onclick="showSection('search')" class="bg-gradient-to-r from-purple-500 to-purple-600 text-white p-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <div class="text-center">
                                    <i class="fas fa-search text-2xl mb-2"></i>
                                    <p class="text-sm font-semibold">Buscar</p>
                                </div>
                            </button>
                            
                            <?php if (isAdministrator()): ?>
                            <button onclick="showSection('settings')" class="bg-gradient-to-r from-gray-500 to-gray-600 text-white p-4 rounded-2xl shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-300">
                                <div class="text-center">
                                    <i class="fas fa-cog text-2xl mb-2"></i>
                                    <p class="text-sm font-semibold">Configuración</p>
                                </div>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Content - Desktop -->
                    <div class="hidden lg:block bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contenido Reciente</h3>
                        <div id="recentContent" class="space-y-4">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>

                    <!-- Recent Content - Mobile -->
                    <div class="lg:hidden">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contenido Reciente</h3>
                        <div id="recentContentMobile" class="space-y-3">
                            <!-- Se llenará dinámicamente -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Section -->
            <div id="categories-section" class="content-section hidden p-4 lg:p-6">
                <div class="max-w-6xl mx-auto">
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Categorías</h2>
                                <p class="text-sm sm:text-base text-gray-600">Organiza tu contenido por categorías</p>
                            </div>
                            <?php if (isAdministrator()): ?>
                            <button onclick="openAddCategoryModal()" 
                                    class="bg-gradient-to-r from-green-500 to-blue-500 text-white px-4 py-2.5 rounded-xl hover:from-green-600 hover:to-blue-600 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Nueva Categoría
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div id="categoriesGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <!-- Se llenará dinámicamente -->
                    </div>
                </div>
            </div>

            <!-- Content Section -->
            <div id="content-section" class="content-section hidden p-4 lg:p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Mobile Header -->
                    <div class="mb-6">
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Gestión de Contenido</h2>
                                <p class="text-sm sm:text-base text-gray-600">Administra todos tus artículos</p>
                            </div>
                            <?php if (isAdministrator()): ?>
                            <button onclick="openAddTopicModal()" 
                                    class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 py-2.5 rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105 text-sm font-medium">
                                <i class="fas fa-plus mr-2"></i>Nuevo Artículo
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Mobile-Optimized Filters -->
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
                        <div class="space-y-3 sm:space-y-0 sm:flex sm:items-center sm:gap-6">
                            <!-- Sort Filter -->
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-sort text-blue-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ordenar</label>
                                    <select id="sortBy" onchange="updateContentList()" 
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="date">Por Fecha</option>
                                        <option value="category">Por Categoría</option>
                                        <option value="title">Por Título</option>
                                    </select>
                                </div>
                            </div>
                            
                            <!-- Category Filter -->
                            <div class="flex items-center space-x-3">
                                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-filter text-green-600 text-sm"></i>
                                </div>
                                <div class="flex-1">
                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Filtrar</label>
                                    <select id="filterCategory" onchange="updateContentList()" 
                                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                        <option value="all">Todas las Categorías</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Content List Container -->
                    <div id="contentListContainer" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Loading State -->
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-spinner fa-spin text-2xl mb-3"></i>
                            <p>Cargando contenido...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Section -->
            <div id="search-section" class="content-section hidden p-4 lg:p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Búsqueda Avanzada</h2>
                        <p class="text-sm sm:text-base text-gray-600">Encuentra información específica en toda la base de conocimientos</p>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 sm:p-6 mb-6">
                        <div class="space-y-4 sm:space-y-0 sm:flex sm:items-center sm:space-x-3">
                            <div class="relative flex-1">
                                <input type="text" id="searchInput" 
                                       class="w-full pl-12 pr-4 py-3 sm:py-4 text-base sm:text-lg border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                       placeholder="Buscar información sobre trámites CESCO...">
                                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            </div>
                            <button onclick="searchKnowledge()" 
                                    class="w-full sm:w-auto bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 sm:py-4 rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl font-medium">
                                <i class="fas fa-search mr-2"></i>Buscar
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
            <div id="chat-section" class="content-section hidden p-3 sm:p-6">
                <div class="max-w-6xl mx-auto">
                    <!-- Hero Section - Desktop -->
                    <div class="hidden lg:block text-center mb-8">
                        <div class="relative inline-block mb-4">
                            <div class="w-24 h-24 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center shadow-lg">
                                <i class="fas fa-robot text-3xl text-white"></i>
                            </div>
                            <div class="absolute -top-2 -right-2 w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-check text-white text-sm"></i>
                            </div>
                        </div>
                        <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent mb-2">
                            ANA - Asistente Virtual
                        </h1>
                        <p class="text-xl text-gray-600 mb-2">Especializada en Trámites CESCO de Puerto Rico</p>
                        <div class="flex items-center justify-center space-x-4 text-sm text-gray-500">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                                <span>
                                    <?php 
                                    $selectedAI = $config['selected_ai'] ?? 'gemini';
                                    $aiNames = [
                                        'gemini' => 'Google Gemini',
                                        'chatgpt' => 'ChatGPT',
                                        'claude' => 'Claude'
                                    ];
                                    echo 'Powered by ' . ($aiNames[$selectedAI] ?? 'Gemini');
                                    ?>
                                </span>
                            </div>
                            <span>•</span>
                            <span>Respuestas en tiempo real</span>
                            <span>•</span>
                            <span>Información oficial CESCO</span>
                        </div>
                    </div>

                    <!-- Mobile Header Compacto -->
                    <div class="lg:hidden text-center mb-3">
                        <div class="flex items-center justify-center space-x-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                <i class="fas fa-robot text-white text-lg"></i>
                            </div>
                            <div class="text-left">
                                <h1 class="text-lg font-bold text-gray-900">Chat con ANA</h1>
                                <p class="text-sm text-gray-600">Asistente Virtual CESCO</p>
                            </div>
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                <span class="text-xs text-gray-500">En línea</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions - Solo Desktop -->
                    <div class="hidden lg:grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                        <button onclick="document.getElementById('chatInput').value='¿Cómo renuevo mi licencia de conducir?'; document.getElementById('chatInput').focus();" 
                                class="p-4 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-300 text-left group">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-id-card text-white"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Renovación de Licencia</h3>
                            </div>
                            <p class="text-sm text-gray-600">Información sobre renovación de licencias de conducir</p>
                        </button>

                        <button onclick="document.getElementById('chatInput').value='¿Cuánto cuesta el marbete vehicular?'; document.getElementById('chatInput').focus();" 
                                class="p-4 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-300 text-left group">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-car text-white"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Marbetes y Registro</h3>
                            </div>
                            <p class="text-sm text-gray-600">Costos y requisitos para marbetes vehiculares</p>
                        </button>

                        <button onclick="document.getElementById('chatInput').value='¿Dónde están ubicadas las oficinas de CESCO?'; document.getElementById('chatInput').focus();" 
                                class="p-4 bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200 rounded-xl hover:from-purple-100 hover:to-purple-200 transition-all duration-300 text-left group">
                            <div class="flex items-center mb-2">
                                <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                                    <i class="fas fa-map-marker-alt text-white"></i>
                                </div>
                                <h3 class="font-semibold text-gray-900">Ubicaciones y Horarios</h3>
                            </div>
                            <p class="text-sm text-gray-600">Encuentra oficinas CESCO cerca de ti</p>
                        </button>
                    </div>
                    
                    <!-- Chat Container -->
                    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">
                        <!-- Chat Header - Solo Desktop -->
                        <div class="hidden lg:block bg-gradient-to-r from-blue-500 to-purple-600 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-3">
                                        <i class="fas fa-comments text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-white font-semibold">Chat con ANA</h3>
                                        <p class="text-blue-100 text-sm">Asistente Virtual CESCO</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                                    <span class="text-white text-sm">En línea</span>
                                </div>
                            </div>
                        </div>

                        <!-- Chat Messages -->
                        <div class="h-[calc(100vh-200px)] lg:h-[500px] overflow-y-auto p-3 lg:p-6 bg-gradient-to-b from-gray-50 to-white" id="chatContainer">
                            <div class="flex items-start mb-6">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-gray-200 max-w-md">
                                    <div class="flex items-center mb-2">
                                        <span class="font-semibold text-gray-900 mr-2">ANA</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Asistente Virtual</span>
                                    </div>
                                    <p class="text-gray-700 leading-relaxed">¡Hola! 👋 Soy ANA, tu asistente virtual especializada en trámites de CESCO. Estoy aquí para ayudarte con:</p>
                                    <ul class="mt-3 space-y-1 text-sm text-gray-600">
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Renovaciones de licencia</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Marbetes vehiculares</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Documentos requeridos</li>
                                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Ubicaciones y horarios</li>
                                    </ul>
                                    <p class="mt-3 text-gray-700">¿En qué puedo ayudarte hoy? 😊</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Chat Input -->
                        <div class="border-t border-gray-200 p-3 lg:p-4 bg-white">
                            <form id="chatForm" class="flex space-x-2 lg:space-x-3">
                                <div class="flex-1 relative">
                                    <input type="text" id="chatInput" 
                                           class="w-full border border-gray-300 rounded-xl px-3 lg:px-4 py-2.5 lg:py-3 pr-10 lg:pr-12 text-sm lg:text-base focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                           placeholder="Pregunta sobre trámites CESCO..." required>
                                    <div class="absolute right-2 lg:right-3 top-1/2 transform -translate-y-1/2">
                                        <i class="fas fa-keyboard text-gray-400 text-sm lg:text-base"></i>
                                    </div>
                                </div>
                                <button type="submit" 
                                        class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-4 lg:px-6 py-2.5 lg:py-3 rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-105">
                                    <i class="fas fa-paper-plane text-sm lg:text-base"></i>
                                </button>
                            </form>
                            <div class="mt-2 text-center">
                                <p class="text-xs text-gray-500">Presiona Enter para enviar • ANA responde en segundos</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Settings Section -->
            <div id="settings-section" class="content-section hidden p-4 lg:p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="mb-6">
                        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 mb-2">Configuración</h2>
                        <p class="text-sm sm:text-base text-gray-600">Configura tu asistente IA y otras preferencias del sistema</p>
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

                    <!-- Multi-AI Configuration -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-robot mr-2 text-primary"></i>
                            Configuración de Asistentes IA
                        </h3>
                        
                        <form method="POST" class="space-y-6">
                            <input type="hidden" name="action" value="save_config">
                            
                            <!-- AI Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    <i class="fas fa-brain mr-1"></i>
                                    Seleccionar Asistente IA Activo
                                </label>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="selected_ai" value="gemini" 
                                               <?php echo ($config['selected_ai'] ?? 'gemini') === 'gemini' ? 'checked' : ''; ?>
                                               class="sr-only peer">
                                        <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-blue-500 peer-checked:bg-blue-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fab fa-google text-blue-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">Google Gemini</div>
                                                    <div class="text-sm text-gray-500">Gemini 2.0 Flash</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="selected_ai" value="chatgpt" 
                                               <?php echo ($config['selected_ai'] ?? 'gemini') === 'chatgpt' ? 'checked' : ''; ?>
                                               class="sr-only peer">
                                        <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-green-500 peer-checked:bg-green-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-comments text-green-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">ChatGPT</div>
                                                    <div class="text-sm text-gray-500">GPT-4o</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                    
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="selected_ai" value="claude" 
                                               <?php echo ($config['selected_ai'] ?? 'gemini') === 'claude' ? 'checked' : ''; ?>
                                               class="sr-only peer">
                                        <div class="p-4 border-2 border-gray-200 rounded-lg peer-checked:border-purple-500 peer-checked:bg-purple-50 hover:bg-gray-50 transition-colors">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-brain text-purple-600"></i>
                                                </div>
                                                <div>
                                                    <div class="font-medium text-gray-900">Claude</div>
                                                    <div class="text-sm text-gray-500">Claude 3.5 Sonnet</div>
                                                </div>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- AI Configurations Tabs -->
                            <div class="border-t border-gray-200 pt-6">
                                <div class="mb-4">
                                    <nav class="flex space-x-8">
                                        <button type="button" onclick="showAIConfig('gemini')" 
                                                class="ai-tab-btn py-2 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors"
                                                data-ai="gemini">
                                            <i class="fab fa-google mr-1"></i>Google Gemini
                                        </button>
                                        <button type="button" onclick="showAIConfig('chatgpt')" 
                                                class="ai-tab-btn py-2 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors"
                                                data-ai="chatgpt">
                                            <i class="fas fa-comments mr-1"></i>ChatGPT
                                        </button>
                                        <button type="button" onclick="showAIConfig('claude')" 
                                                class="ai-tab-btn py-2 px-1 border-b-2 font-medium text-sm focus:outline-none transition-colors"
                                                data-ai="claude">
                                            <i class="fas fa-brain mr-1"></i>Claude
                                        </button>
                                    </nav>
                                </div>
                                
                                <!-- Gemini Config -->
                                <div id="gemini-config" class="ai-config-panel space-y-4">
                                    <div>
                                        <label for="gemini_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-key mr-1"></i>
                                            API Key de Google Gemini
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="gemini_api_key" name="gemini_api_key" 
                                                   value="<?php echo htmlspecialchars($config['gemini_api_key'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                   placeholder="Ingresa tu API Key de Google Gemini">
                                            <button type="button" onclick="toggleApiKeyVisibility('gemini_api_key', 'gemini_toggle')" 
                                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                <i id="gemini_toggle" class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Obtén tu API Key en <a href="https://makersuite.google.com/app/apikey" target="_blank" class="text-blue-600 hover:underline">Google AI Studio</a>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="gemini_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-comment-dots mr-1"></i>
                                            Prompt Personalizado
                                        </label>
                                        <textarea id="gemini_prompt" name="gemini_prompt" rows="6"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                                  placeholder="Define cómo debe comportarse Gemini..."><?php echo htmlspecialchars($config['gemini_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'); ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- ChatGPT Config -->
                                <div id="chatgpt-config" class="ai-config-panel space-y-4 hidden">
                                    <div>
                                        <label for="chatgpt_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-key mr-1"></i>
                                            API Key de OpenAI (ChatGPT)
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="chatgpt_api_key" name="chatgpt_api_key" 
                                                   value="<?php echo htmlspecialchars($config['chatgpt_api_key'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                   placeholder="Ingresa tu API Key de OpenAI">
                                            <button type="button" onclick="toggleApiKeyVisibility('chatgpt_api_key', 'chatgpt_toggle')" 
                                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                <i id="chatgpt_toggle" class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Obtén tu API Key en <a href="https://platform.openai.com/api-keys" target="_blank" class="text-green-600 hover:underline">OpenAI Platform</a>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="chatgpt_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-comment-dots mr-1"></i>
                                            Prompt Personalizado
                                        </label>
                                        <textarea id="chatgpt_prompt" name="chatgpt_prompt" rows="6"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                                  placeholder="Define cómo debe comportarse ChatGPT..."><?php echo htmlspecialchars($config['chatgpt_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'); ?></textarea>
                                    </div>
                                </div>
                                
                                <!-- Claude Config -->
                                <div id="claude-config" class="ai-config-panel space-y-4 hidden">
                                    <div>
                                        <label for="claude_api_key" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-key mr-1"></i>
                                            API Key de Anthropic (Claude)
                                        </label>
                                        <div class="relative">
                                            <input type="password" id="claude_api_key" name="claude_api_key" 
                                                   value="<?php echo htmlspecialchars($config['claude_api_key'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                   placeholder="Ingresa tu API Key de Anthropic">
                                            <button type="button" onclick="toggleApiKeyVisibility('claude_api_key', 'claude_toggle')" 
                                                    class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700 focus:outline-none">
                                                <i id="claude_toggle" class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500">
                                            Obtén tu API Key en <a href="https://console.anthropic.com/" target="_blank" class="text-purple-600 hover:underline">Anthropic Console</a>
                                        </p>
                                    </div>
                                    
                                    <div>
                                        <label for="claude_prompt" class="block text-sm font-medium text-gray-700 mb-2">
                                            <i class="fas fa-comment-dots mr-1"></i>
                                            Prompt Personalizado
                                        </label>
                                        <textarea id="claude_prompt" name="claude_prompt" rows="6"
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                  placeholder="Define cómo debe comportarse Claude..."><?php echo htmlspecialchars($config['claude_prompt'] ?? 'Eres ANA, un asistente virtual especializado en trámites de CESCO (Centro de Servicios al Conductor) en Puerto Rico. Ayuda a los usuarios con información sobre renovaciones de licencia, registros vehiculares, marbetes, documentos requeridos, costos, horarios y ubicaciones. Responde de manera amigable y profesional.'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                                <div class="flex items-center space-x-2">
                                    <?php 
                                    $selectedAI = $config['selected_ai'] ?? 'gemini';
                                    $apiKeyField = $selectedAI . '_api_key';
                                    $isConfigured = !empty($config[$apiKeyField]);
                                    $aiNames = [
                                        'gemini' => 'Gemini',
                                        'chatgpt' => 'ChatGPT',
                                        'claude' => 'Claude'
                                    ];
                                    $currentAIName = $aiNames[$selectedAI] ?? 'Gemini';
                                    ?>
                                    <div class="w-3 h-3 rounded-full <?php echo $isConfigured ? 'bg-green-500' : 'bg-red-500'; ?>"></div>
                                    <span class="text-sm text-gray-600">
                                        <?php echo $currentAIName; ?>: <?php echo $isConfigured ? 'Configurado' : 'No configurado'; ?>
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
                                <div class="text-sm font-medium text-gray-700">IA Activa</div>
                                <div class="text-lg font-semibold text-gray-900">
                                    <?php 
                                    $selectedAI = $config['selected_ai'] ?? 'gemini';
                                    $aiNames = [
                                        'gemini' => 'Google Gemini',
                                        'chatgpt' => 'ChatGPT',
                                        'claude' => 'Claude'
                                    ];
                                    
                                    $currentAI = $aiNames[$selectedAI] ?? 'Gemini';
                                    $apiKeyField = $selectedAI . '_api_key';
                                    $isConfigured = !empty($config[$apiKeyField]);
                                    
                                    echo $currentAI . ($isConfigured ? ' ✓' : ' ❌');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
        </div>
    </div>

    <!-- Modals y JavaScript -->
    <script>
        // Variables globales
        let allCategories = {};
        let dynamicContent = [];
        let currentEditingTopic = null;
        
        // Variables de permisos
        const userRole = '<?php echo getUserRole(); ?>';
        const isAdmin = userRole === 'administrator';

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
            const activeButton = document.querySelector(`[onclick="showSection(\`${sectionName}\`)"]`);
            if (activeButton) {
                activeButton.classList.add('active');
            }
            
            // Actualizar estados del menú móvil
            updateMobileNavStates(sectionName);

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
        
        // Funciones para móvil
        function toggleMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        // Funciones para el nuevo menú móvil
        function toggleMobileDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            drawer.classList.toggle('translate-y-full');
        }
        
        function closeMobileDrawer() {
            const drawer = document.getElementById('mobileDrawer');
            drawer.classList.add('translate-y-full');
        }
        
        function closeMobileMenu() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        }
        
        // Actualizar estados activos del menú móvil
        function updateMobileNavStates(activeSection) {
            // Actualizar bottom navigation
            document.querySelectorAll('.mobile-nav-item').forEach(item => {
                const section = item.getAttribute('data-section');
                if (section === activeSection) {
                    item.classList.add('text-blue-600');
                    item.classList.remove('text-gray-600');
                } else if (section) {
                    item.classList.add('text-gray-600');
                    item.classList.remove('text-blue-600');
                }
            });
        }
        
        // Funciones para chat rápido
        function setQuickQuestion(question) {
            const input = document.getElementById('quickChatInput');
            if (input) {
                input.value = question;
                input.focus();
            }
        }
        
        function sendQuickChat() {
            const input = document.getElementById('quickChatInput');
            if (input && input.value.trim()) {
                // Cambiar a la sección de chat
                showSection('chat');
                
                // Esperar un momento para que se cargue la sección
                setTimeout(() => {
                    const chatInput = document.getElementById('chatInput');
                    if (chatInput) {
                        chatInput.value = input.value;
                        // Simular envío del mensaje
                        sendMessage();
                        // Limpiar el input rápido
                        input.value = '';
                    }
                }, 100);
            }
        }
        
        function sendQuickChatDirect() {
            const input = document.getElementById('quickChatInput');
            if (input && input.value.trim()) {
                const question = input.value.trim();
                
                // Cambiar a la sección de chat
                showSection('chat');
                
                // Esperar un momento para que se cargue la sección y enviar directamente
                setTimeout(() => {
                    const chatInput = document.getElementById('chatInput');
                    const chatContainer = document.getElementById('chatContainer');
                    
                    if (chatInput && chatContainer) {
                        // Limpiar el input del dashboard
                        input.value = '';
                        
                        // Agregar mensaje del usuario al chat
                        const userMessage = document.createElement('div');
                        userMessage.className = 'flex items-start mb-4 justify-end';
                        userMessage.innerHTML = `
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl rounded-tr-sm p-4 shadow-sm max-w-md">
                                <div class="flex items-center mb-2">
                                    <span class="font-semibold mr-2">Tú</span>
                                    <span class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full">Usuario</span>
                                </div>
                                <p class="leading-relaxed">${question}</p>
                            </div>
                            <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center ml-3 flex-shrink-0">
                                <i class="fas fa-user text-gray-600 text-sm"></i>
                            </div>
                        `;
                        chatContainer.appendChild(userMessage);
                        
                        // Scroll al final
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        
                        // Mostrar indicador de carga
                        const loadingMessage = document.createElement('div');
                        loadingMessage.className = 'flex items-start mb-4';
                        loadingMessage.id = 'loadingMessage';
                        loadingMessage.innerHTML = `
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-robot text-white text-sm"></i>
                            </div>
                            <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-gray-200 max-w-md">
                                <div class="flex items-center mb-2">
                                    <span class="font-semibold text-gray-900 mr-2">ANA</span>
                                    <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Escribiendo...</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                            </div>
                        `;
                        chatContainer.appendChild(loadingMessage);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                        
                        // Enviar pregunta a la IA
                        const formData = new FormData();
                        formData.append('pregunta', question);
                        
                        fetch('consultas_multi.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            // Remover indicador de carga
                            const loading = document.getElementById('loadingMessage');
                            if (loading) loading.remove();
                            
                            // Agregar respuesta de ANA
                            const anaMessage = document.createElement('div');
                            anaMessage.className = 'flex items-start mb-4';
                            anaMessage.innerHTML = `
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-gray-200 max-w-md">
                                    <div class="flex items-center mb-2">
                                        <span class="font-semibold text-gray-900 mr-2">ANA</span>
                                        <span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">Asistente Virtual</span>
                                    </div>
                                    <div class="text-gray-700 leading-relaxed whitespace-pre-wrap">${data.respuesta || 'Lo siento, no pude procesar tu pregunta en este momento.'}</div>
                                </div>
                            `;
                            chatContainer.appendChild(anaMessage);
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            // Remover indicador de carga
                            const loading = document.getElementById('loadingMessage');
                            if (loading) loading.remove();
                            
                            // Mostrar mensaje de error
                            const errorMessage = document.createElement('div');
                            errorMessage.className = 'flex items-start mb-4';
                            errorMessage.innerHTML = `
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                    <i class="fas fa-robot text-white text-sm"></i>
                                </div>
                                <div class="bg-red-50 rounded-2xl rounded-tl-sm p-4 shadow-sm border border-red-200 max-w-md">
                                    <div class="flex items-center mb-2">
                                        <span class="font-semibold text-red-900 mr-2">ANA</span>
                                        <span class="text-xs text-red-500 bg-red-100 px-2 py-1 rounded-full">Error</span>
                                    </div>
                                    <p class="text-red-700">Lo siento, hubo un problema al procesar tu pregunta. Por favor, intenta nuevamente.</p>
                                </div>
                            `;
                            chatContainer.appendChild(errorMessage);
                            chatContainer.scrollTop = chatContainer.scrollHeight;
                        });
                    }
                }, 100);
            }
        }
        
        // Permitir envío con Enter en el chat rápido
        document.addEventListener('DOMContentLoaded', function() {
            const quickInput = document.getElementById('quickChatInput');
            if (quickInput) {
                quickInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        sendQuickChatDirect();
                    }
                });
            }
        });
        
        // Función para mostrar/ocultar API Key
        function toggleApiKeyVisibility(inputId, iconId) {
            const apiKeyInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (apiKeyInput.type === 'password') {
                apiKeyInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                apiKeyInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Función para mostrar/ocultar contraseña del login
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('passwordToggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
        
        // Función para mostrar configuración de IA específica
        function showAIConfig(aiType) {
            // Ocultar todos los paneles
            document.querySelectorAll('.ai-config-panel').forEach(panel => {
                panel.classList.add('hidden');
            });
            
            // Mostrar el panel seleccionado
            document.getElementById(aiType + '-config').classList.remove('hidden');
            
            // Actualizar estilos de pestañas
            document.querySelectorAll('.ai-tab-btn').forEach(btn => {
                if (btn.dataset.ai === aiType) {
                    btn.classList.add('border-primary', 'text-primary');
                    btn.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    btn.classList.add('border-transparent', 'text-gray-500');
                    btn.classList.remove('border-primary', 'text-primary');
                }
            });
        }
        
        // Inicializar pestañas al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            showAIConfig('gemini'); // Mostrar Gemini por defecto
        });
        
        function toggleMobileSearch() {
            const mobileSearch = document.getElementById('mobileSearch');
            mobileSearch.classList.toggle('hidden');
            
            if (!mobileSearch.classList.contains('hidden')) {
                document.getElementById('mobileSearchInput').focus();
            }
        }

        // Funciones del dashboard
        function updateDashboardStats() {
            const categoriesCount = Object.keys(allCategories).length;
            const articlesCount = dynamicContent.length;
            const aiStatus = geminiConfig.apiKey ? 'Configurado' : 'Configurar';
            
            // Actualizar contadores desktop
            document.getElementById('categoriesCount').textContent = categoriesCount;
            document.getElementById('articlesCount').textContent = articlesCount;
            document.getElementById('aiStatus').textContent = aiStatus;
            
            // Actualizar contadores móviles
            const categoriesCountMobile = document.getElementById('categoriesCountMobile');
            const articlesCountMobile = document.getElementById('articlesCountMobile');
            const aiStatusMobile = document.getElementById('aiStatusMobile');
            
            if (categoriesCountMobile) categoriesCountMobile.textContent = categoriesCount;
            if (articlesCountMobile) articlesCountMobile.textContent = articlesCount;
            if (aiStatusMobile) aiStatusMobile.textContent = aiStatus;
        }

        function loadRecentContent() {
            // Ordenar por fecha más reciente y tomar los primeros 5
            const sortedContent = [...dynamicContent].sort((a, b) => 
                new Date(b.timestamp) - new Date(a.timestamp)
            );
            const recentItems = sortedContent.slice(0, 5);
            
            // Contenedor desktop
            const recentContainer = document.getElementById('recentContent');
            // Contenedor móvil
            const recentContainerMobile = document.getElementById('recentContentMobile');
            
            if (recentItems.length === 0) {
                const emptyMessage = '<p class="text-gray-500 text-center py-4">No hay contenido reciente</p>';
                if (recentContainer) recentContainer.innerHTML = emptyMessage;
                if (recentContainerMobile) recentContainerMobile.innerHTML = emptyMessage;
                return;
            }
            
            // HTML para desktop
            const desktopHTML = recentItems.map(item => {
                const categoryName = item.categoryId && allCategories[item.categoryId] 
                    ? `${allCategories[item.categoryId].icon} ${allCategories[item.categoryId].title}` 
                    : 'Sin categoría';
                
                return `
                    <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex-1">
                            <h4 class="font-medium text-gray-900">${item.title}</h4>
                            <p class="text-sm text-gray-500">${categoryName} • ${new Date(item.timestamp).toLocaleDateString('es-ES')}</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="openTopicModal(\`${item.id}\`)" class="text-primary hover:text-primary-dark p-2 rounded-lg hover:bg-blue-50">
                                <i class="fas fa-eye"></i>
                            </button>
                            ${isAdmin ? `<button onclick="editTopic(\`${item.id}\`)" class="text-gray-600 hover:text-gray-800 p-2 rounded-lg hover:bg-gray-100">
                                <i class="fas fa-edit"></i>
                            </button>` : ''}
                        </div>
                    </div>
                `;
            }).join('');
            
            // HTML para móvil (siguiendo el patrón de gestión de contenido)
            const mobileHTML = recentItems.map(item => {
                const categoryInfo = item.categoryId && allCategories[item.categoryId] 
                    ? allCategories[item.categoryId] 
                    : null;
                
                return `
                    <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md transition-all duration-300">
                        <!-- Título -->
                        <h4 class="font-semibold text-gray-900 mb-3 text-base leading-tight">${item.title}</h4>
                        
                        <!-- Categoría -->
                        ${categoryInfo ? `
                            <div class="mb-3">
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-blue-50 to-blue-100 text-blue-800 border border-blue-200">
                                    <span class="mr-2">${categoryInfo.icon}</span>
                                    ${categoryInfo.title}
                                </span>
                            </div>
                        ` : ''}
                        
                        <!-- Contenido Preview -->
                        <p class="text-sm text-gray-600 mb-4 leading-relaxed">${item.content ? item.content.replace(/<[^>]*>/g, '').substring(0, 120) + '...' : 'Sin contenido disponible'}</p>
                        
                        <!-- Fecha y Botones -->
                        <div class="flex items-center justify-between">
                            <span class="text-xs text-gray-500">${new Date(item.timestamp).toLocaleDateString('es-ES', { 
                                year: 'numeric', 
                                month: 'short', 
                                day: 'numeric' 
                            })}</span>
                            <div class="flex items-center space-x-3">
                                <button onclick="openTopicModal('${item.id}')" 
                                        class="flex items-center space-x-2 bg-blue-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                                    <i class="fas fa-eye text-xs"></i>
                                    <span>Ver</span>
                                </button>
                                ${isAdmin ? `
                                    <button onclick="editTopic('${item.id}')" 
                                            class="flex items-center space-x-2 bg-gray-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors">
                                        <i class="fas fa-edit text-xs"></i>
                                        <span>Editar</span>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            // Actualizar contenedores
            if (recentContainer) recentContainer.innerHTML = desktopHTML;
            if (recentContainerMobile) recentContainerMobile.innerHTML = mobileHTML;
        }

        // Funciones de categorías
        function loadCategoriesGrid() {
            const grid = document.getElementById('categoriesGrid');
            
            if (Object.keys(allCategories).length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <i class="fas fa-folder-open text-4xl text-gray-300 mb-4"></i>
                        <p class="text-gray-500 mb-4">No hay categorías creadas</p>
                        ${isAdmin ? `<button onclick="openAddCategoryModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark">
                            Crear Primera Categoría
                        </button>` : `<p class="text-gray-500">No hay categorías disponibles</p>`}
                    </div>
                `;
                return;
            }
            
            grid.innerHTML = Object.entries(allCategories).map(([id, category]) => `
                <div class="category-card rounded-xl border-2 p-6 transition-all duration-300 cursor-pointer category-bg-${category.color}" onclick="openCategoryModal(\`${id}\`)">
                    <div class="flex items-center justify-between mb-4">
                        <div class="text-3xl">${category.icon}</div>
                        ${isAdmin ? `<button onclick="event.stopPropagation(); editCategory(\`${id}\`)" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-white hover:bg-opacity-50 transition-colors">
                            <i class="fas fa-edit"></i>
                        </button>` : ''}
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">${category.title}</h3>
                    <p class="text-gray-700 text-sm mb-4 leading-relaxed">${category.description}</p>
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-white bg-opacity-60 text-gray-800 border border-white border-opacity-40">
                            ${getTopicsInCategory(id).length} artículo${getTopicsInCategory(id).length !== 1 ? 's' : ''}
                        </span>
                        <span class="text-xs font-medium text-gray-600 bg-white bg-opacity-40 px-2 py-1 rounded-full">
                            ${getCategoryColorName(category.color)}
                        </span>
                    </div>
                </div>
            `).join('');
        }

        function getTopicsInCategory(categoryId) {
            return dynamicContent.filter(topic => topic.categoryId === categoryId);
        }
        
        // Función auxiliar para obtener nombres de colores en español
        function getCategoryColorName(color) {
            const colorNames = {
                'blue': 'Azul',
                'green': 'Verde', 
                'yellow': 'Amarillo',
                'purple': 'Morado',
                'red': 'Rojo',
                'indigo': 'Índigo'
            };
            return colorNames[color] || color;
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
                        <!-- Desktop Layout -->
                        <div class="hidden lg:flex p-4 hover:bg-gray-50 items-center justify-between">
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
                                <button onclick="openTopicModal(\`${item.id}\`)" class="text-primary hover:text-primary-dark">
                                    <i class="fas fa-eye"></i>
                                </button>
                                ${isAdmin ? `<button onclick="editTopic(\`${item.id}\`)" class="text-gray-600 hover:text-gray-800">
                                    <i class="fas fa-edit"></i>
                                </button>` : ''}
                            </div>
                        </div>
                        
                        <!-- Mobile Layout -->
                        <div class="lg:hidden p-4 hover:bg-gray-50">
                            <!-- Título -->
                            <h4 class="font-semibold text-gray-900 mb-3 text-base leading-tight">${item.title}</h4>
                            
                            <!-- Categoría -->
                            ${item.categoryId && allCategories[item.categoryId] ? `
                                <div class="mb-3">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gradient-to-r from-blue-50 to-blue-100 text-blue-800 border border-blue-200">
                                        <span class="mr-2">${allCategories[item.categoryId].icon}</span>
                                        ${allCategories[item.categoryId].title}
                                    </span>
                                </div>
                            ` : ''}
                            
                            <!-- Contenido Preview -->
                            <p class="text-sm text-gray-600 mb-4 leading-relaxed">${item.content.replace(/<[^>]*>/g, '').substring(0, 120)}...</p>
                            
                            <!-- Fecha y Botones -->
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-500">${new Date(item.timestamp).toLocaleDateString('es-ES', { 
                                    year: 'numeric', 
                                    month: 'short', 
                                    day: 'numeric' 
                                })}</span>
                                <div class="flex items-center space-x-3">
                                    <button onclick="openTopicModal(\`${item.id}\`)" 
                                            class="flex items-center space-x-2 bg-blue-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                                        <i class="fas fa-eye text-xs"></i>
                                        <span>Ver</span>
                                    </button>
                                    ${isAdmin ? `
                                        <button onclick="editTopic(\`${item.id}\`)" 
                                                class="flex items-center space-x-2 bg-gray-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium hover:bg-gray-600 transition-colors">
                                            <i class="fas fa-edit text-xs"></i>
                                            <span>Editar</span>
                                        </button>
                                    ` : ''}
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // Funciones de búsqueda
        function searchKnowledge() {
            const searchInput = document.getElementById('searchInput');
            const headerSearch = document.getElementById('headerSearch');
            
            // Obtener query del input activo
            let query = '';
            if (searchInput && searchInput.value.trim()) {
                query = searchInput.value.trim();
            } else if (headerSearch && headerSearch.value.trim()) {
                query = headerSearch.value.trim();
                // Sincronizar con el input de la sección de búsqueda
                if (searchInput) searchInput.value = query;
            }
            
            if (!query) return;
            
            // Cambiar a la sección de búsqueda si no estamos ahí
            showSection('search');
            
            // Algoritmo de búsqueda mejorado
            const results = dynamicContent.filter(item => {
                const titleMatch = item.title.toLowerCase().includes(query.toLowerCase());
                const contentMatch = item.content.toLowerCase().replace(/<[^>]*>/g, '').includes(query.toLowerCase());
                return titleMatch || contentMatch;
            }).map(item => {
                // Calcular relevancia
                let relevance = 0;
                const queryLower = query.toLowerCase();
                const titleLower = item.title.toLowerCase();
                const contentLower = item.content.toLowerCase().replace(/<[^>]*>/g, '');
                
                // Coincidencia exacta en título (mayor peso)
                if (titleLower.includes(queryLower)) {
                    relevance += titleLower === queryLower ? 10 : 5;
                }
                
                // Coincidencia en contenido
                if (contentLower.includes(queryLower)) {
                    relevance += 2;
                }
                
                // Palabras individuales
                const queryWords = queryLower.split(' ');
                queryWords.forEach(word => {
                    if (word.length > 2) {
                        if (titleLower.includes(word)) relevance += 3;
                        if (contentLower.includes(word)) relevance += 1;
                    }
                });
                
                return { ...item, relevance };
            }).sort((a, b) => b.relevance - a.relevance);
            
            displaySearchResults(results, query);
        }
        
        // Event listeners para búsqueda
        document.addEventListener('DOMContentLoaded', function() {
            const headerSearch = document.getElementById('headerSearch');
            const mobileSearchInput = document.getElementById('mobileSearchInput');
            
            if (headerSearch) {
                headerSearch.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        searchKnowledge();
                    }
                });
            }
            
            if (mobileSearchInput) {
                mobileSearchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        // Sincronizar con el input principal
                        if (headerSearch) {
                            headerSearch.value = e.target.value;
                        }
                        searchKnowledge();
                        toggleMobileSearch(); // Cerrar búsqueda móvil
                    }
                });
            }
        });

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
                        <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer" onclick="openTopicModal(\`${item.id}\`)">
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
            try {
                addChatMessage('Usuario', message, 'user');
                addTypingIndicator();

                const response = await fetch('consultas_multi.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `pregunta=${encodeURIComponent(message)}`
                });

                const data = await response.json();
                removeTypingIndicator();

                if (data.respuesta) {
                    addChatMessage('ANA', data.respuesta, 'ai');
                } else {
                    addChatMessage('Sistema', 'No se pudo obtener respuesta del asistente IA.', 'system');
                }
            } catch (error) {
                removeTypingIndicator();
                addChatMessage('Sistema', 'Error al conectar con el asistente IA: ' + error.message, 'system');
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

        // Función para cargar contenido dinámico desde database.json
        async function loadDynamicContent() {
            try {
                const response = await fetch('database.json');
                if (!response.ok) {
                    throw new Error('No se pudo cargar database.json');
                }
                
                const data = await response.json();
                
                // Cargar categorías
                if (data.categories) {
                    allCategories = {};
                    data.categories.forEach(category => {
                        allCategories[category.id] = category;
                    });
                }
                
                // Cargar temas
                if (data.topics) {
                    dynamicContent = data.topics;
                }
                
                // Actualizar selectores de filtros
                updateCategoryFilters();
                
                // Actualizar estadísticas del dashboard
                updateDashboardStats();
                
                // Actualizar contenido reciente
                loadRecentContent();
                
                console.log('Contenido dinámico cargado:', {
                    categorias: Object.keys(allCategories).length,
                    temas: dynamicContent.length
                });
                
            } catch (error) {
                console.error('Error cargando contenido dinámico:', error);
                
                // Fallback a localStorage si existe
                const localCategories = localStorage.getItem('cescoCategories');
                const localTopics = localStorage.getItem('cescoTopics');
                
                if (localCategories && localTopics) {
                    allCategories = JSON.parse(localCategories);
                    dynamicContent = JSON.parse(localTopics);
                    updateCategoryFilters();
                    updateDashboardStats();
                    loadRecentContent(); // Actualizar contenido reciente
                    console.log('Contenido cargado desde localStorage');
                } else {
                    console.warn('No se pudo cargar contenido dinámico');
                }
            }
        }
        
        // Función para actualizar filtros de categorías
        function updateCategoryFilters() {
            const filterSelect = document.getElementById('filterCategory');
            if (filterSelect) {
                // Limpiar opciones existentes excepto "Todas las categorías"
                filterSelect.innerHTML = '<option value="all">🌟 Todas las Categorías</option>';
                
                // Agregar categorías dinámicamente
                Object.entries(allCategories).forEach(([id, category]) => {
                    const option = document.createElement('option');
                    option.value = id;
                    option.textContent = `${category.icon} ${category.title}`;
                    filterSelect.appendChild(option);
                });
            }
        }

        function openAddCategoryModal() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl">
                    <div class="bg-white border-b border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900">Nueva Categoría</h2>
                        <p class="text-sm text-gray-500">Crear una nueva categoría para organizar el contenido</p>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form id="addCategoryForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Categoría</label>
                                <input type="text" id="categoryName" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea id="categoryDescription" required rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent"></textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Icono</label>
                                <div class="grid grid-cols-6 gap-2 p-3 border border-gray-300 rounded-lg">
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="🚗" onclick="selectIcon(this, '🚗')">🚗</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="📋" onclick="selectIcon(this, '📋')">📋</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="💰" onclick="selectIcon(this, '💰')">💰</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="⏰" onclick="selectIcon(this, '⏰')">⏰</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="🎯" onclick="selectIcon(this, '🎯')">🎯</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="❓" onclick="selectIcon(this, '❓')">❓</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="📁" onclick="selectIcon(this, '📁')">📁</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="📄" onclick="selectIcon(this, '📄')">📄</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="🔧" onclick="selectIcon(this, '🔧')">🔧</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="📞" onclick="selectIcon(this, '📞')">📞</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="🏢" onclick="selectIcon(this, '🏢')">🏢</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors" data-icon="📍" onclick="selectIcon(this, '📍')">📍</button>
                                </div>
                                <input type="hidden" id="categoryIcon" required>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <div class="grid grid-cols-6 gap-2 p-3 border border-gray-300 rounded-lg">
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-blue-500" data-color="blue" onclick="selectColor(this, 'blue')" title="Azul"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-green-500" data-color="green" onclick="selectColor(this, 'green')" title="Verde"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-yellow-500" data-color="yellow" onclick="selectColor(this, 'yellow')" title="Amarillo"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-purple-500" data-color="purple" onclick="selectColor(this, 'purple')" title="Morado"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-red-500" data-color="red" onclick="selectColor(this, 'red')" title="Rojo"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 border-gray-200 hover:border-gray-400 transition-colors bg-indigo-500" data-color="indigo" onclick="selectColor(this, 'indigo')" title="Índigo"></button>
                                </div>
                                <input type="hidden" id="categoryColor" required>
                            </div>
                            
                            <div class="flex justify-end space-x-3 pt-4 border-t">
                                <button type="button" onclick="this.closest('.fixed').remove()" 
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                                <button type="submit" 
                                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark">
                                    Crear Categoría
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Event listener para el formulario
            document.getElementById('addCategoryForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validar que se haya seleccionado icono y color
                const iconValue = document.getElementById('categoryIcon').value;
                const colorValue = document.getElementById('categoryColor').value;
                
                if (!iconValue) {
                    alert('Por favor selecciona un icono para la categoría');
                    return;
                }
                
                if (!colorValue) {
                    alert('Por favor selecciona un color para la categoría');
                    return;
                }
                
                const newCategory = {
                    id: generateId(document.getElementById('categoryName').value),
                    title: document.getElementById('categoryName').value,
                    description: document.getElementById('categoryDescription').value,
                    icon: iconValue,
                    color: colorValue,
                    topics: []
                };
                
                // Agregar a allCategories
                allCategories[newCategory.id] = newCategory;
                
                // Preparar datos para guardar
                const dataToSave = {
                    categories: Object.values(allCategories),
                    topics: dynamicContent
                };
                
                const result = await saveToDatabaseJson(dataToSave);
                
                if (result.success) {
                    modal.remove();
                    updateCategoryFilters();
                    loadCategoriesGrid();
                    updateDashboardStats();
                    alert('Categoría creada exitosamente');
                } else {
                    alert('Error al crear categoría: ' + result.error);
                }
            });
        }

        function openAddTopicToCategoryModal(categoryId) {
            const category = allCategories[categoryId];
            if (!category) {
                console.error('Categoría no encontrada:', categoryId);
                return;
            }
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                    <div class="bg-white border-b border-gray-100 p-6">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="text-2xl">${category.icon}</div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">Nuevo Tema para "${category.title}"</h2>
                                <p class="text-sm text-gray-500">Agregar un nuevo tema a la categoría ${category.title}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form id="addTopicToCategoryForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título del Tema</label>
                                <input type="text" id="categoryTopicTitle" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contenido</label>
                                <div class="html-editor">
                                    <div class="editor-toolbar">
                                        <button type="button" class="editor-btn" onclick="formatText('bold', 'categoryTopicContent')" title="Negrita">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('italic', 'categoryTopicContent')" title="Cursiva">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('underline', 'categoryTopicContent')" title="Subrayado">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatText('insertUnorderedList', 'categoryTopicContent')" title="Lista con viñetas">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('insertOrderedList', 'categoryTopicContent')" title="Lista numerada">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h2', 'categoryTopicContent')" title="Título 2">
                                            H2
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h3', 'categoryTopicContent')" title="Título 3">
                                            H3
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="clearFormatting('categoryTopicContent')" title="Limpiar formato">
                                            <i class="fas fa-remove-format"></i>
                                        </button>
                                    </div>
                                    <div id="categoryTopicContent" class="editor-content" contenteditable="true" 
                                         data-placeholder="Escribe el contenido del tema para ${category.title}..."></div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    Archivos Adjuntos (Opcional)
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                                    <input type="file" id="categoryTopicFiles" multiple 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                           class="hidden">
                                    <div class="cursor-pointer" onclick="document.getElementById('categoryTopicFiles').click()">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600">Haz clic para seleccionar archivos</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, JPG, PNG, GIF (máx. 5MB cada uno)</p>
                                    </div>
                                </div>
                                <div id="categorySelectedFiles" class="mt-3 space-y-2"></div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                                <button type="button" onclick="this.closest('.fixed').remove()" 
                                        class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                                <button type="submit" 
                                        class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark">
                                    Crear Tema
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Event listener para selección de archivos
            document.getElementById('categoryTopicFiles').addEventListener('change', function(e) {
                displaySelectedFiles(e.target.files, 'categorySelectedFiles');
            });
            
            // Event listeners para el editor
            const editor = document.getElementById('categoryTopicContent');
            editor.addEventListener('keyup', () => updateToolbarState('categoryTopicContent'));
            editor.addEventListener('mouseup', () => updateToolbarState('categoryTopicContent'));
            editor.addEventListener('focus', () => updateToolbarState('categoryTopicContent'));
            
            // Event listener para el formulario
            document.getElementById('addTopicToCategoryForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Creando...';
                submitButton.disabled = true;
                
                try {
                    // Subir archivos primero si hay alguno
                    let uploadedFiles = [];
                    const fileInput = document.getElementById('categoryTopicFiles');
                    
                    if (fileInput.files.length > 0) {
                        const formData = new FormData();
                        Array.from(fileInput.files).forEach(file => {
                            formData.append('files[]', file);
                        });
                        
                        const uploadResponse = await fetch('upload_files.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const uploadResult = await uploadResponse.json();
                        
                        if (uploadResult.errors && uploadResult.errors.length > 0) {
                            alert('Errores al subir archivos: ' + uploadResult.errors.join(', '));
                        }
                        
                        uploadedFiles = uploadResult.files || [];
                    }
                    
                    const newTopic = {
                        id: generateId(document.getElementById('categoryTopicTitle').value),
                        title: document.getElementById('categoryTopicTitle').value,
                        content: getEditorContent('categoryTopicContent'),
                        categoryId: categoryId, // Asignar automáticamente la categoría
                        timestamp: new Date().toISOString(),
                        files: uploadedFiles
                    };
                    
                    // Agregar a dynamicContent
                    dynamicContent.push(newTopic);
                    
                    // Preparar datos para guardar
                    const dataToSave = {
                        categories: Object.values(allCategories),
                        topics: dynamicContent
                    };
                    
                    const result = await saveToDatabaseJson(dataToSave);
                    
                    if (result.success) {
                        modal.remove();
                        updateContentList();
                        updateDashboardStats();
                        loadRecentContent();
                        loadCategoriesGrid(); // Actualizar el grid para mostrar el nuevo conteo
                        alert('Tema creado exitosamente en la categoría ' + category.title);
                    } else {
                        alert('Error al crear tema: ' + result.error);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }

        function openAddTopicModal() {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                    <div class="bg-white border-b border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900">Nuevo Artículo</h2>
                        <p class="text-sm text-gray-500">Crear un nuevo artículo de información</p>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form id="addTopicForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título del Artículo</label>
                                <input type="text" id="topicTitle" required
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                                <select id="topicCategory" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Sin categoría específica</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contenido</label>
                                <div class="html-editor">
                                    <div class="editor-toolbar">
                                        <button type="button" class="editor-btn" onclick="formatText('bold')" title="Negrita">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('italic')" title="Cursiva">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('underline')" title="Subrayado">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatText('insertUnorderedList')" title="Lista con viñetas">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('insertOrderedList')" title="Lista numerada">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h2')" title="Título 2">
                                            H2
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h3')" title="Título 3">
                                            H3
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="clearFormatting()" title="Limpiar formato">
                                            <i class="fas fa-remove-format"></i>
                                        </button>
                                    </div>
                                    <div id="topicContent" class="editor-content" contenteditable="true" 
                                         data-placeholder="Escribe el contenido del artículo..."></div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    Archivos Adjuntos (Opcional)
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary transition-colors">
                                    <input type="file" id="topicFiles" multiple 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                           class="hidden">
                                    <div class="cursor-pointer" onclick="document.getElementById('topicFiles').click()">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600">Haz clic para seleccionar archivos</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, JPG, PNG, GIF (máx. 5MB cada uno)</p>
                                    </div>
                                </div>
                                <div id="selectedFiles" class="mt-3 space-y-2"></div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row justify-end space-y-2 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                                <button type="button" onclick="this.closest('.fixed').remove()" 
                                        class="flex items-center justify-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm text-gray-600 hover:text-white hover:bg-gray-500 border border-gray-300 rounded-lg transition-colors">
                                    <i class="fas fa-times text-xs"></i>
                                    <span class="hidden sm:inline">Cancelar</span>
                                </button>
                                <button type="submit" 
                                        class="bg-primary text-white px-4 sm:px-6 py-1.5 sm:py-2 text-sm rounded-lg hover:bg-primary-dark">
                                    Crear Artículo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Poblar categorías
            const categorySelect = document.getElementById('topicCategory');
            Object.entries(allCategories).forEach(([id, category]) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = `${category.icon} ${category.title}`;
                categorySelect.appendChild(option);
            });
            
            // Event listener para selección de archivos
            document.getElementById('topicFiles').addEventListener('change', function(e) {
                displaySelectedFiles(e.target.files, 'selectedFiles');
            });
            
            // Event listeners para el editor
            const editor = document.getElementById('topicContent');
            editor.addEventListener('keyup', () => updateToolbarState('topicContent'));
            editor.addEventListener('mouseup', () => updateToolbarState('topicContent'));
            editor.addEventListener('focus', () => updateToolbarState('topicContent'));
            
            // Event listener para el formulario
            document.getElementById('addTopicForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Creando...';
                submitButton.disabled = true;
                
                try {
                    // Subir archivos primero si hay alguno
                    let uploadedFiles = [];
                    const fileInput = document.getElementById('topicFiles');
                    
                    if (fileInput.files.length > 0) {
                        const formData = new FormData();
                        Array.from(fileInput.files).forEach(file => {
                            formData.append('files[]', file);
                        });
                        
                        const uploadResponse = await fetch('upload_files.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const uploadResult = await uploadResponse.json();
                        
                        if (uploadResult.errors && uploadResult.errors.length > 0) {
                            alert('Errores al subir archivos: ' + uploadResult.errors.join(', '));
                        }
                        
                        uploadedFiles = uploadResult.files || [];
                    }
                    
                    const newTopic = {
                        id: generateId(document.getElementById('topicTitle').value),
                        title: document.getElementById('topicTitle').value,
                        content: getEditorContent('topicContent'),
                        categoryId: document.getElementById('topicCategory').value || null,
                        timestamp: new Date().toISOString(),
                        files: uploadedFiles
                    };
                    
                    // Agregar a dynamicContent
                    dynamicContent.push(newTopic);
                    
                    // Preparar datos para guardar
                    const dataToSave = {
                        categories: Object.values(allCategories),
                        topics: dynamicContent
                    };
                    
                    const result = await saveToDatabaseJson(dataToSave);
                    
                    if (result.success) {
                        modal.remove();
                        updateContentList();
                        updateDashboardStats();
                        loadRecentContent();
                        alert('Artículo creado exitosamente');
                    } else {
                        alert('Error al crear artículo: ' + result.error);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }

        function openTopicModal(topicId) {
            const topic = dynamicContent.find(t => t.id === topicId);
            if (!topic) {
                console.error('Tema no encontrado:', topicId);
                return;
            }
            
            // Crear modal dinámico
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl border-0 flex flex-col">
                    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                                <i class="fas fa-file-alt text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">${topic.title}</h2>
                                <p class="text-sm text-gray-600">
                                    ${allCategories[topic.categoryId] ? `${allCategories[topic.categoryId].icon} ${allCategories[topic.categoryId].title}` : 'Sin categoría'} • 
                                    ${new Date(topic.timestamp).toLocaleDateString('es-ES')}
                                </p>
                            </div>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" 
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6">
                        <div class="prose max-w-none">
                            ${topic.content}
                        </div>
                        
                        ${topic.files && topic.files.length > 0 ? `
                            <div class="mt-6 pt-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                    <i class="fas fa-paperclip mr-2"></i>
                                    Archivos Adjuntos
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    ${topic.files.map(file => `
                                        <div class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                                            <div class="text-2xl mr-3">
                                                ${file.file_type === 'pdf' ? '📄' : file.file_type.includes('image') ? '🖼️' : '📝'}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900 truncate">${file.original_name}</p>
                                                <p class="text-xs text-gray-500">${formatFileSize(file.file_size)}</p>
                                            </div>
                                            <a href="${file.file_path}" target="_blank" 
                                               class="ml-2 text-primary hover:text-primary-dark">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }

        function openCategoryModal(categoryId) {
            const category = allCategories[categoryId];
            if (!category) {
                console.error('Categoría no encontrada:', categoryId);
                return;
            }
            
            const categoryTopics = dynamicContent.filter(topic => topic.categoryId === categoryId);
            
            // Crear modal dinámico
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-5xl max-h-[90vh] overflow-hidden shadow-2xl border-0 flex flex-col">
                    <div class="flex items-center justify-between p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 flex-shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="text-3xl">${category.icon}</div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900">${category.title}</h2>
                                <p class="text-sm text-gray-600">${category.description}</p>
                            </div>
                        </div>
                        <button onclick="this.closest('.fixed').remove()" 
                                class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-full p-2 transition-colors">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <div class="flex-1 overflow-y-auto p-6">
                        ${categoryTopics.length > 0 ? `
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Temas en esta categoría (${categoryTopics.length})</h3>
                                ${isAdmin ? `<button onclick="this.closest('.fixed').remove(); openAddTopicToCategoryModal(\`${categoryId}\`)" 
                                        class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-primary-dark transition-colors text-sm">
                                    <i class="fas fa-plus mr-2"></i>Agregar Tema
                                </button>` : ''}
                            </div>
                            <div class="grid gap-4">
                                ${categoryTopics.map(topic => `
                                    <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 cursor-pointer transition-colors"
                                         onclick="this.closest('.fixed').remove(); openTopicModal(\`${topic.id}\`)">
                                        <div class="flex items-center justify-between mb-2">
                                            <h3 class="font-semibold text-gray-900">${topic.title}</h3>
                                            <div class="flex items-center space-x-2">
                                                <span class="text-xs text-gray-500">${new Date(topic.timestamp).toLocaleDateString('es-ES')}</span>
                                                ${isAdmin ? `<button onclick="event.stopPropagation(); this.closest('.fixed').remove(); editTopic(\`${topic.id}\`)" 
                                                        class="text-gray-400 hover:text-gray-600 p-1 rounded">
                                                    <i class="fas fa-edit text-xs"></i>
                                                </button>` : ''}
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-600 line-clamp-2">${topic.content.replace(/<[^>]*>/g, '').substring(0, 150)}...</p>
                                        ${topic.files && topic.files.length > 0 ? `
                                            <div class="mt-2 flex items-center text-xs text-blue-600">
                                                <i class="fas fa-paperclip mr-1"></i>
                                                ${topic.files.length} archivo${topic.files.length > 1 ? 's' : ''}
                                            </div>
                                        ` : ''}
                                    </div>
                                `).join('')}
                            </div>
                        ` : `
                            <div class="text-center py-12">
                                <div class="text-4xl mb-4">${category.icon}</div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">No hay temas en esta categoría</h3>
                                <p class="text-gray-600 mb-6">Aún no se han agregado temas a "${category.title}"</p>
                                ${isAdmin ? `<button onclick="this.closest('.fixed').remove(); openAddTopicToCategoryModal(\`${categoryId}\`)" 
                                        class="bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary-dark transition-colors">
                                    <i class="fas fa-plus mr-2"></i>Agregar Primer Tema
                                </button>` : `<p class="text-gray-500">No hay temas disponibles en esta categoría</p>`}
                            </div>
                        `}
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
        }
        
        // Función auxiliar para formatear tamaño de archivos
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        function editCategory(categoryId) {
            const category = allCategories[categoryId];
            if (!category) return;
            
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden shadow-2xl">
                    <div class="bg-white border-b border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900">Editar Categoría</h2>
                        <p class="text-sm text-gray-500">Modificar información de la categoría</p>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form id="editCategoryForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la Categoría</label>
                                <input type="text" id="editCategoryName" required value="${category.title}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Descripción</label>
                                <textarea id="editCategoryDescription" required rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">${category.description}</textarea>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Icono</label>
                                <div class="grid grid-cols-6 gap-2 p-3 border border-gray-300 rounded-lg">
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '🚗' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="🚗" onclick="selectIcon(this, '🚗', 'edit')">🚗</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '📋' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="📋" onclick="selectIcon(this, '📋', 'edit')">📋</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '💰' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="💰" onclick="selectIcon(this, '💰', 'edit')">💰</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '⏰' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="⏰" onclick="selectIcon(this, '⏰', 'edit')">⏰</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '🎯' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="🎯" onclick="selectIcon(this, '🎯', 'edit')">🎯</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '❓' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="❓" onclick="selectIcon(this, '❓', 'edit')">❓</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '📁' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="📁" onclick="selectIcon(this, '📁', 'edit')">📁</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '📄' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="📄" onclick="selectIcon(this, '📄', 'edit')">📄</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '🔧' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="🔧" onclick="selectIcon(this, '🔧', 'edit')">🔧</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '📞' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="📞" onclick="selectIcon(this, '📞', 'edit')">📞</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '🏢' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="🏢" onclick="selectIcon(this, '🏢', 'edit')">🏢</button>
                                    <button type="button" class="icon-option w-10 h-10 flex items-center justify-center text-2xl border border-gray-200 rounded-lg hover:bg-blue-50 hover:border-blue-300 transition-colors ${category.icon === '📍' ? 'bg-blue-100 border-blue-400' : ''}" data-icon="📍" onclick="selectIcon(this, '📍', 'edit')">📍</button>
                                </div>
                                <input type="hidden" id="editCategoryIcon" required value="${category.icon}">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Color</label>
                                <div class="grid grid-cols-6 gap-2 p-3 border border-gray-300 rounded-lg">
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-blue-500 ${category.color === 'blue' ? 'border-gray-800 ring-2 ring-blue-300' : 'border-gray-200'}" data-color="blue" onclick="selectColor(this, 'blue', 'edit')" title="Azul"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-green-500 ${category.color === 'green' ? 'border-gray-800 ring-2 ring-green-300' : 'border-gray-200'}" data-color="green" onclick="selectColor(this, 'green', 'edit')" title="Verde"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-yellow-500 ${category.color === 'yellow' ? 'border-gray-800 ring-2 ring-yellow-300' : 'border-gray-200'}" data-color="yellow" onclick="selectColor(this, 'yellow', 'edit')" title="Amarillo"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-purple-500 ${category.color === 'purple' ? 'border-gray-800 ring-2 ring-purple-300' : 'border-gray-200'}" data-color="purple" onclick="selectColor(this, 'purple', 'edit')" title="Morado"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-red-500 ${category.color === 'red' ? 'border-gray-800 ring-2 ring-red-300' : 'border-gray-200'}" data-color="red" onclick="selectColor(this, 'red', 'edit')" title="Rojo"></button>
                                    <button type="button" class="color-option w-10 h-10 rounded-lg border-2 hover:border-gray-400 transition-colors bg-indigo-500 ${category.color === 'indigo' ? 'border-gray-800 ring-2 ring-indigo-300' : 'border-gray-200'}" data-color="indigo" onclick="selectColor(this, 'indigo', 'edit')" title="Índigo"></button>
                                </div>
                                <input type="hidden" id="editCategoryColor" required value="${category.color}">
                            </div>
                            
                            <div class="flex justify-between pt-4 border-t">
                                <button type="button" onclick="deleteCategory(\`${categoryId}\`, this)" 
                                        class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                                    Eliminar
                                </button>
                                <div class="space-x-3">
                                    <button type="button" onclick="this.closest('.fixed').remove()" 
                                            class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                                    <button type="submit" 
                                            class="bg-primary text-white px-6 py-2 rounded-lg hover:bg-primary-dark">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Event listener para el formulario
            document.getElementById('editCategoryForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // Validar que se haya seleccionado icono y color
                const iconValue = document.getElementById('editCategoryIcon').value;
                const colorValue = document.getElementById('editCategoryColor').value;
                
                if (!iconValue) {
                    alert('Por favor selecciona un icono para la categoría');
                    return;
                }
                
                if (!colorValue) {
                    alert('Por favor selecciona un color para la categoría');
                    return;
                }
                
                // Actualizar categoría
                allCategories[categoryId] = {
                    ...allCategories[categoryId],
                    title: document.getElementById('editCategoryName').value,
                    description: document.getElementById('editCategoryDescription').value,
                    icon: iconValue,
                    color: colorValue
                };
                
                // Preparar datos para guardar
                const dataToSave = {
                    categories: Object.values(allCategories),
                    topics: dynamicContent
                };
                
                const result = await saveToDatabaseJson(dataToSave);
                
                if (result.success) {
                    modal.remove();
                    updateCategoryFilters();
                    loadCategoriesGrid();
                    alert('Categoría actualizada exitosamente');
                } else {
                    alert('Error al actualizar categoría: ' + result.error);
                }
            });
        }
        
        function openEditTopicModal(topic) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden shadow-2xl">
                    <div class="bg-white border-b border-gray-100 p-6">
                        <h2 class="text-xl font-bold text-gray-900">Editar Artículo</h2>
                        <p class="text-sm text-gray-500">Modificar información del artículo</p>
                    </div>
                    
                    <div class="p-6 overflow-y-auto max-h-[calc(90vh-140px)]">
                        <form id="editTopicForm" class="space-y-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Título del Artículo</label>
                                <input type="text" id="editTopicTitle" required value="${topic.title}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Categoría</label>
                                <select id="editTopicCategory" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent">
                                    <option value="">Sin categoría específica</option>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contenido</label>
                                <div class="html-editor">
                                    <div class="editor-toolbar">
                                        <button type="button" class="editor-btn" onclick="formatText('bold', 'editTopicContent')" title="Negrita">
                                            <i class="fas fa-bold"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('italic', 'editTopicContent')" title="Cursiva">
                                            <i class="fas fa-italic"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('underline', 'editTopicContent')" title="Subrayado">
                                            <i class="fas fa-underline"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatText('insertUnorderedList', 'editTopicContent')" title="Lista con viñetas">
                                            <i class="fas fa-list-ul"></i>
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatText('insertOrderedList', 'editTopicContent')" title="Lista numerada">
                                            <i class="fas fa-list-ol"></i>
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h2', 'editTopicContent')" title="Título 2">
                                            H2
                                        </button>
                                        <button type="button" class="editor-btn" onclick="formatHeading('h3', 'editTopicContent')" title="Título 3">
                                            H3
                                        </button>
                                        <div class="w-px h-6 bg-gray-300 mx-1"></div>
                                        <button type="button" class="editor-btn" onclick="clearFormatting('editTopicContent')" title="Limpiar formato">
                                            <i class="fas fa-remove-format"></i>
                                        </button>
                                    </div>
                                    <div id="editTopicContent" class="editor-content" contenteditable="true">${topic.content}</div>
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    Archivos Adjuntos
                                </label>
                                
                                <!-- Archivos existentes -->
                                <div id="existingFiles" class="mb-4">
                                    ${topic.files && topic.files.length > 0 ? `
                                        <div class="space-y-2">
                                            <p class="text-sm text-gray-600 mb-2">Archivos actuales:</p>
                                            ${topic.files.map((file, index) => `
                                                <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg border border-blue-200">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="text-lg">${getFileIcon(file.original_name)}</div>
                                                        <div class="flex-1 min-w-0">
                                                            <p class="text-sm font-medium text-gray-900 truncate">${file.original_name}</p>
                                                            <p class="text-xs text-gray-500">${formatFileSize(file.file_size)}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <a href="${file.file_path}" target="_blank" 
                                                           class="text-blue-600 hover:text-blue-800 p-1">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <button type="button" onclick="removeExistingFile(\`${topic.id}\`, ${index})" 
                                                                class="text-red-500 hover:text-red-700 p-1">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    ` : '<p class="text-sm text-gray-500 mb-2">No hay archivos adjuntos</p>'}
                                </div>
                                
                                <!-- Subir nuevos archivos -->
                                <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-primary transition-colors">
                                    <input type="file" id="editTopicFiles" multiple 
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif"
                                           class="hidden">
                                    <div class="cursor-pointer" onclick="document.getElementById('editTopicFiles').click()">
                                        <i class="fas fa-plus text-2xl text-gray-400 mb-2"></i>
                                        <p class="text-gray-600 text-sm">Agregar más archivos</p>
                                        <p class="text-xs text-gray-500 mt-1">PDF, DOC, DOCX, JPG, PNG, GIF (máx. 5MB)</p>
                                    </div>
                                </div>
                                <div id="editSelectedFiles" class="mt-3 space-y-2"></div>
                            </div>
                            
                            <div class="flex flex-col sm:flex-row justify-between pt-4 border-t space-y-2 sm:space-y-0">
                                <button type="button" onclick="deleteTopic(\`${topic.id}\`, this)" 
                                        class="flex items-center justify-center space-x-1 sm:space-x-2 bg-red-500 text-white px-2 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm rounded-lg hover:bg-red-600 transition-colors">
                                    <i class="fas fa-trash text-xs"></i>
                                    <span class="hidden sm:inline">Eliminar</span>
                                </button>
                                <div class="flex items-center space-x-2 sm:space-x-3">
                                    <button type="button" onclick="this.closest('.fixed').remove()" 
                                            class="flex items-center justify-center space-x-1 sm:space-x-2 px-2 sm:px-3 py-1 sm:py-1.5 text-xs sm:text-sm text-gray-600 hover:text-white hover:bg-gray-500 border border-gray-300 rounded-lg transition-colors">
                                        <i class="fas fa-times text-xs"></i>
                                        <span class="hidden sm:inline">Cancelar</span>
                                    </button>
                                    <button type="submit" 
                                            class="bg-primary text-white px-4 sm:px-6 py-1.5 sm:py-2 text-sm rounded-lg hover:bg-primary-dark">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            
            // Poblar categorías
            const categorySelect = document.getElementById('editTopicCategory');
            Object.entries(allCategories).forEach(([id, category]) => {
                const option = document.createElement('option');
                option.value = id;
                option.textContent = `${category.icon} ${category.title}`;
                option.selected = topic.categoryId === id;
                categorySelect.appendChild(option);
            });
            
            // Event listener para selección de archivos
            document.getElementById('editTopicFiles').addEventListener('change', function(e) {
                displaySelectedFiles(e.target.files, 'editSelectedFiles');
            });
            
            // Event listeners para el editor de edición
            const editEditor = document.getElementById('editTopicContent');
            editEditor.addEventListener('keyup', () => updateToolbarState('editTopicContent'));
            editEditor.addEventListener('mouseup', () => updateToolbarState('editTopicContent'));
            editEditor.addEventListener('focus', () => updateToolbarState('editTopicContent'));
            
            // Event listener para el formulario
            document.getElementById('editTopicForm').addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                submitButton.textContent = 'Guardando...';
                submitButton.disabled = true;
                
                try {
                    // Subir nuevos archivos si hay alguno
                    let newUploadedFiles = [];
                    const fileInput = document.getElementById('editTopicFiles');
                    
                    if (fileInput.files.length > 0) {
                        const formData = new FormData();
                        Array.from(fileInput.files).forEach(file => {
                            formData.append('files[]', file);
                        });
                        
                        const uploadResponse = await fetch('upload_files.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const uploadResult = await uploadResponse.json();
                        
                        if (uploadResult.errors && uploadResult.errors.length > 0) {
                            alert('Errores al subir archivos: ' + uploadResult.errors.join(', '));
                        }
                        
                        newUploadedFiles = uploadResult.files || [];
                    }
                    
                    // Encontrar y actualizar el tema
                    const topicIndex = dynamicContent.findIndex(t => t.id === topic.id);
                    if (topicIndex !== -1) {
                        // Combinar archivos existentes con nuevos
                        const existingFiles = dynamicContent[topicIndex].files || [];
                        const allFiles = [...existingFiles, ...newUploadedFiles];
                        
                        dynamicContent[topicIndex] = {
                            ...dynamicContent[topicIndex],
                            title: document.getElementById('editTopicTitle').value,
                            content: getEditorContent('editTopicContent'),
                            categoryId: document.getElementById('editTopicCategory').value || null,
                            files: allFiles
                        };
                    }
                    
                    // Preparar datos para guardar
                    const dataToSave = {
                        categories: Object.values(allCategories),
                        topics: dynamicContent
                    };
                    
                    const result = await saveToDatabaseJson(dataToSave);
                    
                    if (result.success) {
                        modal.remove();
                        updateContentList();
                        loadRecentContent();
                        alert('Artículo actualizado exitosamente');
                    } else {
                        alert('Error al actualizar artículo: ' + result.error);
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                } finally {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }
            });
        }
        
        // Funciones de eliminación
        async function deleteCategory(categoryId, button) {
            if (!confirm('¿Estás seguro de que quieres eliminar esta categoría? Esta acción no se puede deshacer.')) {
                return;
            }
            
            delete allCategories[categoryId];
            
            // Preparar datos para guardar
            const dataToSave = {
                categories: Object.values(allCategories),
                topics: dynamicContent
            };
            
            const result = await saveToDatabaseJson(dataToSave);
            
            if (result.success) {
                button.closest('.fixed').remove();
                updateCategoryFilters();
                loadCategoriesGrid();
                updateDashboardStats();
                alert('Categoría eliminada exitosamente');
            } else {
                alert('Error al eliminar categoría: ' + result.error);
            }
        }
        
        async function deleteTopic(topicId, button) {
            if (!confirm('¿Estás seguro de que quieres eliminar este artículo? Esta acción no se puede deshacer.')) {
                return;
            }
            
            const topicIndex = dynamicContent.findIndex(t => t.id === topicId);
            if (topicIndex !== -1) {
                dynamicContent.splice(topicIndex, 1);
            }
            
            // Preparar datos para guardar
            const dataToSave = {
                categories: Object.values(allCategories),
                topics: dynamicContent
            };
            
            const result = await saveToDatabaseJson(dataToSave);
            
            if (result.success) {
                button.closest('.fixed').remove();
                updateContentList();
                updateDashboardStats();
                loadRecentContent(); // Actualizar contenido reciente
                alert('Artículo eliminado exitosamente');
            } else {
                alert('Error al eliminar artículo: ' + result.error);
            }
        }
        
        // Función para mostrar archivos seleccionados
        function displaySelectedFiles(files, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            container.innerHTML = '';
            
            if (files.length === 0) return;
            
            Array.from(files).forEach((file, index) => {
                const fileDiv = document.createElement('div');
                fileDiv.className = 'flex items-center justify-between p-3 bg-gray-50 rounded-lg border';
                fileDiv.innerHTML = `
                    <div class="flex items-center space-x-3">
                        <div class="text-lg">
                            ${getFileIcon(file.name)}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${file.name}</p>
                            <p class="text-xs text-gray-500">${formatFileSize(file.size)}</p>
                        </div>
                    </div>
                    <button type="button" onclick="removeSelectedFile(${index}, \`${containerId}\`)" 
                            class="text-red-500 hover:text-red-700 p-1">
                        <i class="fas fa-times"></i>
                    </button>
                `;
                container.appendChild(fileDiv);
            });
        }
        
        // Función para remover archivo seleccionado
        function removeSelectedFile(index, containerId) {
            const fileInput = document.querySelector('input[type="file"]');
            if (!fileInput) return;
            
            const dt = new DataTransfer();
            const files = Array.from(fileInput.files);
            
            files.forEach((file, i) => {
                if (i !== index) {
                    dt.items.add(file);
                }
            });
            
            fileInput.files = dt.files;
            displaySelectedFiles(fileInput.files, containerId);
        }
        
        // Función para obtener icono de archivo
        function getFileIcon(filename) {
            const extension = filename.split('.').pop().toLowerCase();
            switch (extension) {
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
        
        // Función para eliminar archivo existente
        async function removeExistingFile(topicId, fileIndex) {
            if (!confirm('¿Estás seguro de que quieres eliminar este archivo?')) {
                return;
            }
            
            const topicIndex = dynamicContent.findIndex(t => t.id === topicId);
            if (topicIndex === -1) return;
            
            const topic = dynamicContent[topicIndex];
            if (!topic.files || !topic.files[fileIndex]) return;
            
            // Eliminar archivo del array
            topic.files.splice(fileIndex, 1);
            
            // Guardar cambios
            const dataToSave = {
                categories: Object.values(allCategories),
                topics: dynamicContent
            };
            
            const result = await saveToDatabaseJson(dataToSave);
            
            if (result.success) {
                // Recargar el modal de edición
                const modal = document.querySelector('.fixed');
                if (modal) {
                    modal.remove();
                    openEditTopicModal(topic);
                }
                alert('Archivo eliminado exitosamente');
            } else {
                alert('Error al eliminar archivo: ' + result.error);
            }
        }
        
        // Funciones para selección de iconos y colores
        function selectIcon(button, icon, mode = 'create') {
            const container = button.parentElement;
            const hiddenInput = mode === 'edit' ? 
                document.getElementById('editCategoryIcon') : 
                document.getElementById('categoryIcon');
            
            // Remover selección anterior
            container.querySelectorAll('.icon-option').forEach(btn => {
                btn.classList.remove('bg-blue-100', 'border-blue-400');
                btn.classList.add('border-gray-200');
            });
            
            // Seleccionar nuevo icono
            button.classList.remove('border-gray-200');
            button.classList.add('bg-blue-100', 'border-blue-400');
            
            // Actualizar input hidden
            hiddenInput.value = icon;
        }
        
        function selectColor(button, color, mode = 'create') {
            const container = button.parentElement;
            const hiddenInput = mode === 'edit' ? 
                document.getElementById('editCategoryColor') : 
                document.getElementById('categoryColor');
            
            // Remover selección anterior
            container.querySelectorAll('.color-option').forEach(btn => {
                btn.classList.remove('border-gray-800', 'ring-2');
                btn.classList.add('border-gray-200');
                // Remover todas las clases de ring
                btn.classList.remove('ring-blue-300', 'ring-green-300', 'ring-yellow-300', 'ring-purple-300', 'ring-red-300', 'ring-indigo-300');
            });
            
            // Seleccionar nuevo color
            button.classList.remove('border-gray-200');
            button.classList.add('border-gray-800', 'ring-2', `ring-${color}-300`);
            
            // Actualizar input hidden
            hiddenInput.value = color;
        }

        // Funciones del Editor HTML
        function formatText(command, editorId = 'topicContent') {
            const editor = document.getElementById(editorId);
            editor.focus();
            document.execCommand(command, false, null);
            updateToolbarState(editorId);
        }
        
        function formatHeading(tag, editorId = 'topicContent') {
            const editor = document.getElementById(editorId);
            editor.focus();
            document.execCommand('formatBlock', false, tag);
            updateToolbarState(editorId);
        }
        
        function clearFormatting(editorId = 'topicContent') {
            const editor = document.getElementById(editorId);
            editor.focus();
            document.execCommand('removeFormat', false, null);
            updateToolbarState(editorId);
        }
        
        function updateToolbarState(editorId) {
            const toolbar = document.getElementById(editorId).parentElement.querySelector('.editor-toolbar');
            const buttons = toolbar.querySelectorAll('.editor-btn');
            
            buttons.forEach(button => {
                button.classList.remove('active');
                const command = button.getAttribute('onclick');
                
                if (command && command.includes('bold') && document.queryCommandState('bold')) {
                    button.classList.add('active');
                } else if (command && command.includes('italic') && document.queryCommandState('italic')) {
                    button.classList.add('active');
                } else if (command && command.includes('underline') && document.queryCommandState('underline')) {
                    button.classList.add('active');
                }
            });
        }
        
        function getEditorContent(editorId) {
            const editor = document.getElementById(editorId);
            return editor.innerHTML;
        }
        
        function setEditorContent(editorId, content) {
            const editor = document.getElementById(editorId);
            editor.innerHTML = content;
        }
        
        // Función para generar IDs únicos
        function generateId(title) {
            return title.toLowerCase()
                .replace(/[áàäâ]/g, 'a')
                .replace(/[éèëê]/g, 'e')
                .replace(/[íìïî]/g, 'i')
                .replace(/[óòöô]/g, 'o')
                .replace(/[úùüû]/g, 'u')
                .replace(/ñ/g, 'n')
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
        }

        function editTopic(topicId) {
            const topic = dynamicContent.find(t => t.id === topicId);
            if (!topic) return;
            
            openEditTopicModal(topic);
        }
        
        // Función para guardar en database.json
        async function saveToDatabaseJson(data) {
            try {
                const response = await fetch('save_database.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                return result;
            } catch (error) {
                console.error('Error guardando:', error);
                return { success: false, error: error.message };
            }
        }
    </script>
    </div>

    <!-- Footer -->
    <footer class="hidden lg:block border-t border-gray-200 mt-auto" style="background-color: #397843;">
        <div class="w-[98%] lg:w-[90%] max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                <div class="flex items-center space-x-3">
                    
                    <div class="text-center md:text-left">
                        <h3 class="text-sm font-semibold text-white">Centro de Ayuda CESCO</h3>
                        <p class="text-xs text-gray-200">Base de conocimientos y asistencia</p>
                    </div>
                </div>
                
                <div class="flex items-center space-x-6 text-xs text-gray-200">
                    <span>© <?php echo date('Y'); ?> CESCO Online</span>
                    <span>•</span>
                    <span>Sistema de Gestión de Conocimientos</span>
                </div>
            </div>
        </div>
    </footer>

<?php endif; ?>

</body>
</html>
