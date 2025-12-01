<?php
namespace App\Controllers\Public;

use App\Models\Public\FormPublicModel;

class FormController {

    public function display($projectId) {
        if (!$projectId) { http_response_code(404); echo "Formulario no encontrado."; return; }

        $model = new FormPublicModel();
        $form = $model->getFormByProjectId((int)$projectId);

        if (!$form) {
            http_response_code(404);
            echo "<div style='font-family:sans-serif;text-align:center;margin-top:50px;color:#666;'>
                    <h2>Formulario no disponible</h2>
                    <p>El formulario no existe o no ha sido implementado (activado) aún.</p>
                </div>";
            return;
        }

        $page_title = htmlspecialchars($form['project_title']);
        $projectDescription = htmlspecialchars($form['project_desc']);
        
        // Decodificar y volver a codificar para asegurar JSON válido
        $rawJson = $form['form_definition'];
        $decoded = json_decode($rawJson);
        $questionsJson = ($decoded && is_array($decoded)) ? $rawJson : '[]';
        
        $formVersionId = $form['version_id'];

        include PROJECT_ROOT . '/app/Views/public/form/display.php';
    }

    public function submit($projectId) {
        header('Content-Type: application/json');
        
        $jsonInput = file_get_contents('php://input');
        $data = json_decode($jsonInput, true);

        if (!$data || !isset($data['answers']) || !isset($data['version_id'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        $model = new FormPublicModel();
        $success = $model->saveSubmission(
            (int)$data['version_id'], 
            json_encode($data['answers'])
        );

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Enviado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
    }
}