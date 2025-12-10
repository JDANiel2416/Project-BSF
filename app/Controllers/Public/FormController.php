<?php
namespace App\Controllers\Public;

use App\Models\Public\FormPublicModel;

class FormController
{
    public function display($token)
    {
        if (!$token) {
            http_response_code(404);
            echo "Formulario no encontrado.";
            return;
        }

        $model = new FormPublicModel();
        $form = $model->getFormByProjectToken($token);

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
        $projectId = $form['project_id'];

        $rawJson = $form['form_definition'];
        $decoded = json_decode($rawJson);
        $questionsJson = ($decoded && is_array($decoded)) ? $rawJson : '[]';

        $formVersionId = $form['version_id'];

        include PROJECT_ROOT . '/app/Views/public/form/display.php';
    }

    public function submit($token)
    {
        header('Content-Type: application/json');

        // Verificar que vengan datos por POST (FormData)
        if (empty($_POST)) {
            // Intentar fallback a JSON por si acaso (para compatibilidad antigua)
            $jsonInput = file_get_contents('php://input');
            $data = json_decode($jsonInput, true);
        } else {
            $data = $_POST;
        }

        if (!$data || !isset($data['answers']) || !isset($data['version_id'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        $answers = $data['answers'];

        // --- PROCESAMIENTO DE ARCHIVOS ---
        if (!empty($_FILES['answers']['name'])) {
            // Crear subcarpetas por Año/Mes (Ej: uploads/2023/10/)
            $subFolder = date('Y') . '/' . date('m') . '/';
            $uploadDir = PROJECT_ROOT . '/public/uploads/' . $subFolder;

            // Crear carpeta si no existe
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Iterar sobre los archivos enviados en answers[]
            foreach ($_FILES['answers']['name'] as $questionId => $filename) {
                if ($_FILES['answers']['error'][$questionId] === UPLOAD_ERR_OK) {

                    $tmpName = $_FILES['answers']['tmp_name'][$questionId];
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);

                    // Generar nombre único: timestamp_random.ext
                    $newFilename = time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $newFilename;

                    if (move_uploaded_file($tmpName, $targetPath)) {
                        // Guardar la URL relativa en las respuestas
                        // Nota: Guardamos "uploads/nombre_archivo.jpg"
                        $answers[$questionId] = 'uploads/' . $subFolder . $newFilename;
                    }
                }
            }
        }

        // Obtener tiempos
        $start_time = $data['start_time'] ?? date('Y-m-d H:i:s');
        $end_time = $data['end_time'] ?? date('Y-m-d H:i:s');

        $model = new FormPublicModel();
        // Guardamos $answers (que ahora contiene las rutas de los archivos) como JSON
        $success = $model->saveSubmission(
            (int) $data['version_id'],
            json_encode($answers),
            $start_time,
            $end_time
        );

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Enviado correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar']);
        }
    }
}