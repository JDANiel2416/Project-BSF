<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$request_path = str_replace(ADMIN_BASE_PATH, '', $request_uri);
$path = trim($request_path, '/');

$parts = explode('/', $path);
$controller_name = $parts[0] ?: 'dashboard';
$method_name = $parts[1] ?? 'index';
$params = array_slice($parts, 2);

switch ($controller_name) {
    case 'dashboard':
        (new App\Controllers\Admin\DashboardController())->index();
        break;

    case 'login':
        (new App\Controllers\Admin\AuthController())->showLoginForm();
        break;

    case 'login-handler':
        (new App\Controllers\Admin\AuthController())->login();
        break;
        
    case 'logout':
        (new App\Controllers\Admin\AuthController())->logout();
        break;
    
    case 'proyectos':
        if ($method_name === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            (new App\Controllers\Admin\ProyectoController())->crear();
        } 
        elseif ($method_name === 'resumen' && !empty($params)) {
            (new App\Controllers\Admin\ProyectoController())->resumen($params);
        } 
        else {
            http_response_code(404);
            echo "Acción de proyecto no encontrada.";
        }
        break;

    case 'formularios':
        if ($method_name === 'constructor' && !empty($params)) {
            (new App\Controllers\Admin\FormularioController())->constructor($params);
        } 
        elseif ($method_name === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST' && !empty($params)) {
            (new App\Controllers\Admin\FormularioController())->guardar($params);
        }
        break;

    default:
        if(empty($controller_name) || $controller_name === 'index.php') {
             if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                header('Location: ' . ADMIN_URL . '/dashboard');
             } else {
                header('Location: ' . ADMIN_URL . '/login');
             }
             exit;
        }
        http_response_code(404);
        echo "Página de administración no encontrada";
        break;
}