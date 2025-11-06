<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/config/database.php';

$request_uri = explode('?', $_SERVER['REQUEST_URI'], 2)[0];
$request_path = str_replace(BASE_PATH, '', $request_uri);
$path = trim($request_path, '/');

if (empty($path)) {
    $path = 'inicio';
}

switch ($path) {
    case 'inicio':
        (new App\Controllers\Public\InicioController())->index();
        break;
    case 'quienes-somos':
        (new App\Controllers\Public\QuienesSomosController())->index();
        break;
    case 'proyectos':
        (new App\Controllers\Public\ProyectosController())->index();
        break;
    case 'voluntariado':
        (new App\Controllers\Public\VoluntariadoController())->index();
        break;
    case 'equipo':
        (new App\Controllers\Public\EquipoController())->index();
        break;
    case 'contacto':
        (new App\Controllers\Public\ContactoController())->index();
        break;
    case 'mapa':
        (new App\Controllers\Public\MapController())->index();
        break;
    default:
        http_response_code(404);
        echo "Página no encontrada";
        break;
}