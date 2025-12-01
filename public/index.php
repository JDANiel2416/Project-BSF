<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

// Procesamiento de la URL
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$request_path = str_replace(BASE_PATH, '', $request_uri);
$path = trim($request_path, '/');

$parts = explode('/', $path);

// Si la raíz está vacía, ir a inicio
if (empty($parts[0])) {
    (new App\Controllers\Public\InicioController())->index();
    exit;
}

switch ($parts[0]) {
    // --- RUTAS PÚBLICAS ---
    case 'inicio': (new App\Controllers\Public\InicioController())->index(); break;
    case 'quienes-somos': (new App\Controllers\Public\QuienesSomosController())->index(); break;
    case 'proyectos': (new App\Controllers\Public\ProyectosController())->index(); break;
    case 'voluntariado': (new App\Controllers\Public\VoluntariadoController())->index(); break;
    case 'equipo': (new App\Controllers\Public\EquipoController())->index(); break;
    case 'contacto': (new App\Controllers\Public\ContactoController())->index(); break;
    case 'mapa': (new App\Controllers\Public\MapController())->index(); break;

    // --- NUEVA RUTA: FORMULARIOS PÚBLICOS (/f/19) ---
    case 'f':
        $projectId = $parts[1] ?? null; // El ID (ej: 19)
        $action = $parts[2] ?? 'view';  // view o submit
        
        $controller = new App\Controllers\Public\FormController();
        
        if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->submit($projectId);
        } else {
            $controller->display($projectId);
        }
        break;

    // --- RUTAS ADMIN (Si se acceden via public/admin en URL rewriting) ---
    case 'admin':
        // Normalmente admin tiene su propio index.php, pero si cae aquí:
        header('Location: ' . ADMIN_URL . '/login');
        break;

    default:
        http_response_code(404);
        // Estilo simple para el error 404
        echo "<div style='font-family:sans-serif;text-align:center;padding:50px;'>";
        echo "<h1>404</h1><p>Página no encontrada.</p>";
        echo "<a href='".BASE_URL."/inicio'>Volver al inicio</a>";
        echo "</div>";
        break;
}