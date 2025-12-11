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
            // Crear subcarpetas uploads/Año/Mes
            $subFolder = date('Y') . '/' . date('m') . '/';
            $uploadDir = PROJECT_ROOT . '/public/uploads/' . $subFolder;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($_FILES['answers']['name'] as $questionId => $filename) {
                if ($_FILES['answers']['error'][$questionId] === UPLOAD_ERR_OK) {

                    $tmpName = $_FILES['answers']['tmp_name'][$questionId];
                    
                    // Detectar tipo de archivo real
                    $fileType = mime_content_type($tmpName);
                    $isImage = strpos($fileType, 'image/') === 0;

                    // Nombre base único sin extensión
                    $baseName = time() . '_' . uniqid();
                    
                    if ($isImage) {
                        // Definir ruta destino SIN extensión
                        $destinationNoExt = $uploadDir . $baseName;
                        // Usar el Helper (Namespace completo por seguridad)
                        // Calidad 75, Ancho máximo 1280px (ajustable)
                        $finalFullPath = \App\Helpers\ImageHelper::uploadAndCompress($tmpName, $destinationNoExt, 75, 1280);
                        
                        if ($finalFullPath) {
                            // Guardamos la ruta relativa con la extensión .webp que generó el helper
                            $answers[$questionId] = 'uploads/' . $subFolder . basename($finalFullPath);
                        } else {
                            // Fallback si falla la compresión (raro), guardamos original
                            $ext = pathinfo($filename, PATHINFO_EXTENSION);
                            $finalName = $baseName . '.' . $ext;
                            move_uploaded_file($tmpName, $uploadDir . $finalName);
                            $answers[$questionId] = 'uploads/' . $subFolder . $finalName;
                        }

                    } else {
                        // === RUTA NORMAL (PDFs, DOCs, etc) ===
                        $extension = pathinfo($filename, PATHINFO_EXTENSION);
                        $finalName = $baseName . '.' . $extension;
                        $targetPath = $uploadDir . $finalName;

                        if (move_uploaded_file($tmpName, $targetPath)) {
                            $answers[$questionId] = 'uploads/' . $subFolder . $finalName;
                        }
                    }
                }
            }
        }

        // Obtener tiempos
        $start_time = $data['start_time'] ?? date('Y-m-d H:i:s');
        $end_time = $data['end_time'] ?? date('Y-m-d H:i:s');

        $model = new FormPublicModel();
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