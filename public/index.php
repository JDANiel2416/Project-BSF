<?php
// Activa la visualización de errores durante el desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../vendor/autoload.php';
require_once '../config/config.php';

// Simple Router
$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$request_path = str_replace(BASE_PATH, '', $request_uri);
$path = trim($request_path, '/');

// Default route
if (empty($path)) {
    $path = 'inicio';
}

switch ($path) {
    case 'inicio':
        (new App\Controllers\InicioController())->index();
        break;
    case 'quienes-somos':
        (new App\Controllers\QuienesSomosController())->index();
        break;
    case 'proyectos':
        (new App\Controllers\ProyectosController())->index();
        break;
    case 'voluntariado':
        (new App\Controllers\VoluntariadoController())->index();
        break;
    case 'equipo':
        (new App\Controllers\EquipoController())->index();
        break;
    case 'contacto':
        (new App\Controllers\ContactoController())->index();
        break;
    default:
        http_response_code(404);
        // Para más adelante, podrías tener un controlador de errores aquí
        echo "Página no encontrada";
        break;
}