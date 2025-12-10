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
    case 'login':
        (new App\Controllers\Admin\AuthController())->showLoginForm();
        break;

    case 'login-handler':
        (new App\Controllers\Admin\AuthController())->login();
        break;

    case 'logout':
        (new App\Controllers\Admin\AuthController())->logout();
        break;

    case 'dashboard':
        (new App\Controllers\Admin\DashboardController())->index();
        break;

    case 'proyectos':
        $controller = new App\Controllers\Admin\ProyectoController();

        switch ($method_name) {
            case 'crear':
                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                    $controller->crear();
                break;
            case 'actualizar':
                if ($_SERVER['REQUEST_METHOD'] === 'POST')
                    $controller->actualizar($params);
                break;
            case 'eliminar':
                $controller->eliminar($params);
                break;
            case 'getProjectDetailAjax':
                $controller->getProjectDetailAjax($params);
                break;
            case 'getFormConstructorAjax':
                $controller->getFormConstructorAjax($params);
                break;
            case 'archivar':
                $controller->archivar($params);
                break;
            case 'restaurar':
                $controller->restaurar($params);
                break;
            case 'listarArchivados':
                $controller->listarArchivados();
                break;
            case 'implementar':
                $controller->implementar($params);
                break;
            case 'getDataTableAjax':
                $controller->getDataTableAjax($params);
                break;
            case 'updateSubmissionStatus':
                $controller->updateSubmissionStatus();
                break;
            case 'updateSubmissionData':
                $controller->updateSubmissionData();
                break;
            case 'deleteSubmissions':
                $controller->deleteSubmissions();
                break;
            case 'getReportsAjax':
                $controller->getReportsAjax($params);
                break;
            case 'saveReport':
                $controller->saveReport();
                break;
            case 'getExportAjax':
                $controller->getExportAjax($params);
                break;
            case 'processExport':
                $controller->processExport();
                break;
            case 'deleteExport':
                $controller->deleteExport($params);
                break;
            case 'getMapAjax':
                $controller->getMapAjax($params);
                break;
            case 'getGalleryAjax':
                $controller->getGalleryAjax($params);
                break;
            default:
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Método no encontrado']);
                break;
        }
        break;

    case 'formularios':
        $controller = new App\Controllers\Admin\ProyectoController();
        if ($method_name === 'guardar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->guardarFormulario($params);
        } elseif ($method_name === 'constructor') {
            header('Location: ' . ADMIN_URL . '/dashboard');
        } else {
            http_response_code(404);
        }
        break;

    default:
        if (empty($controller_name) || $controller_name === 'index.php') {
            if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
                header('Location: ' . ADMIN_URL . '/dashboard');
            } else {
                header('Location: ' . ADMIN_URL . '/login');
            }
            exit;
        }
        http_response_code(404);
        echo "Página no encontrada";
        break;
}