<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Proyecto;
use App\Models\Admin\FormularioModel;

class FormularioController {

    public function __construct() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public function constructor($params) {
        $proyectoId = $params[0] ?? null;
        if (!$proyectoId) {
            header('Location: ' . ADMIN_URL . '/dashboard');
            exit;
        }

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->findById($proyectoId);

        if (!$proyecto) {
             http_response_code(404);
             echo "Proyecto no encontrado.";
             exit;
        }

        $page_title = 'Constructor de Formulario | ' . htmlspecialchars($proyecto['nombre']);
        
        $formModel = new FormularioModel();
        $formDefinitionJson = $formModel->getLatestFormVersion((int)$proyectoId);
        $questionsJson = $formDefinitionJson ?: '[]';

        include PROJECT_ROOT . '/app/Views/admin/formulario/constructor.php';
    }
    
    public function guardar($params) {
        header('Content-Type: application/json');
        
        $projectId = $params[0] ?? null;
        $formDefinitionJson = $_POST['form_definition'] ?? null;

        if (!$projectId || !$formDefinitionJson) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos del proyecto o del formulario.']);
            return;
        }

        $formModel = new FormularioModel();
        $success = $formModel->saveFormVersion((int)$projectId, $formDefinitionJson);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Formulario guardado con éxito.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el formulario en la base de datos.']);
        }
    }
}