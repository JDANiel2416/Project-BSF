<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Proyecto;
use App\Models\Admin\FormularioModel;
// PHPOffice
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class ProyectoController
{
    public function __construct()
    {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesión expirada', 'redirect' => ADMIN_URL . '/login']);
                exit;
            }
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public function getProjectDetailAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id) {
            http_response_code(400);
            return;
        }

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->getProjectSummaryById((int) $id);

        if (!$proyecto) {
            http_response_code(404);
            echo "Proyecto no encontrado.";
            return;
        }

        $formModel = new FormularioModel();
        $formDefinitionJson = $formModel->getLatestFormVersion((int) $id);
        $questionsJson = $formDefinitionJson ?: '[]';

        $versionCount = $formModel->countVersions((int) $id);

        // --- CAMBIO 1: URL con Token ---
        $token = $proyecto['token'];
        $publicFormUrl = PUBLIC_URL . "/f/" . $token;

        // --- CAMBIO 2: Lógica de estado de implementación ---
        $latestVersionStatus = $formModel->getLatestVersionStatus((int) $id);

        // El proyecto está activo PERO la última versión es un borrador
        $hasPendingChanges = ($proyecto['status'] === 'active' && $latestVersionStatus === 'draft');

        $proyecto['sector'] = $proyecto['sector'] ?? '-';
        $proyecto['country'] = $proyecto['country'] ?? '-';
        $proyecto['description'] = $proyecto['description'] ?? '';

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_summary_ajax.php';
    }

    public function implementar($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $proyectoModel = new Proyecto();
        $formModel = new FormularioModel();

        // Validación: No permitir implementar si no hay preguntas
        $projectData = $proyectoModel->getProjectSummaryById((int) $id);
        if ($projectData['question_count'] == 0) {
            echo json_encode(['success' => false, 'message' => 'No puedes implementar un formulario vacío.']);
            return;
        }

        // 1. Activar Proyecto
        $successProj = $proyectoModel->updateStatus((int) $id, 'active');

        // 2. Publicar última versión del formulario
        $successForm = $formModel->publishLatestVersion((int) $id);

        if ($successProj && $successForm) {
            echo json_encode([
                'success' => true,
                'message' => 'Proyecto implementado exitosamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al implementar.'
            ]);
        }
    }

    public function getFormConstructorAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id)
            return;

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->findById((int) $id);

        $formModel = new FormularioModel();
        $formDefinitionJson = $formModel->getLatestFormVersion((int) $id);
        $questionsJson = $formDefinitionJson ?: '[]';

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_form_constructor_ajax.php';
    }

    public function crear()
    {
        header('Content-Type: application/json');

        $nombre = $_POST['nombre'] ?? '';
        $sector = $_POST['sector'] ?? '';
        $pais = $_POST['pais'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if (empty($nombre) || empty($sector) || empty($pais)) {
            echo json_encode(['success' => false, 'message' => 'Faltan campos obligatorios.']);
            return;
        }

        $proyectoModel = new Proyecto();
        $owner_id = $_SESSION['admin_user_id'] ?? 0;

        $newId = $proyectoModel->create($nombre, $descripcion, $sector, $pais, $owner_id);

        if ($newId) {
            echo json_encode([
                'success' => true,
                'projectId' => $newId,
                'projectName' => $nombre,
                'message' => 'Proyecto creado correctamente.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar en base de datos.']);
        }
    }

    // --- Actualizar datos completos ---
    public function actualizar($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;

        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        $sector = $_POST['sector'] ?? '';
        $pais = $_POST['pais'] ?? '';

        if (!$id || empty($nombre)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos. El nombre es obligatorio.']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->update((int) $id, $nombre, $descripcion, $sector, $pais);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Proyecto actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar en base de datos.']);
        }
    }

    public function eliminar($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->delete((int) $id);

        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Proyecto eliminado permanentemente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }

    public function archivar($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->updateStatus((int) $id, 'archived');

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Proyecto archivado.' : 'Error al archivar.'
        ]);
    }

    public function restaurar($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->updateStatus((int) $id, 'active');

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Proyecto restaurado.' : 'Error al restaurar.'
        ]);
    }

    public function guardarFormulario($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        $definition = $_POST['form_definition'] ?? '[]';

        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $formModel = new FormularioModel();
        $result = $formModel->saveFormVersion((int) $id, $definition);

        echo json_encode(['success' => $result, 'message' => $result ? 'Guardado' : 'Error al guardar']);
    }

    public function listarArchivados()
    {
        $proyectoModel = new Proyecto();
        $proyectos = $proyectoModel->getArchived();

        if (empty($proyectos)) {
            echo '<tr><td colspan="7" class="text-center" style="padding:20px;">No hay proyectos archivados.</td></tr>';
            return;
        }

        foreach ($proyectos as $p) {
            $nombre = htmlspecialchars($p['nombre']);
            $sector = htmlspecialchars($p['sector'] ?? '-');
            $pais = htmlspecialchars($p['pais'] ?? '-');
            $fecha = date('d/m/Y', strtotime($p['fecha_modificacion']));
            $id = $p['id'];

            echo "<tr data-id='{$id}' class='project-row archived-row'>
                    <td><input type='checkbox'></td>
                    <td style='opacity:0.6;'>{$nombre}</td>
                    <td><span class='status status-archived'>Archivado</span></td>
                    <td>{$sector}</td>
                    <td>{$pais}</td>
                    <td>{$fecha}</td>
                    <td class='actions-cell'>
                        <button class='btn-icon-action btn-restore' data-id='{$id}' title='Restaurar'><i class='fas fa-undo'></i></button>
                    </td>
                </tr>";
        }
    }

    public function getDataTableAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id)
            return;

        $formModel = new FormularioModel();

        // 1. Obtener definición del formulario para las Cabeceras de la tabla
        $formDefinitionJson = $formModel->getLatestFormVersion((int) $id);
        $questions = json_decode($formDefinitionJson ?: '[]', true);

        // 2. Obtener los envíos (Filas)
        $submissions = $formModel->getSubmissionsByProjectId((int) $id);

        // 3. Cargar la vista parcial
        include PROJECT_ROOT . '/app/Views/admin/proyecto/_data_table_ajax.php';
    }

    public function updateSubmissionStatus()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];
        $status = $input['status'] ?? '';

        if (empty($ids) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            return;
        }

        $formModel = new FormularioModel();
        if ($formModel->updateSubmissionStatus($ids, $status)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
    }

    public function deleteSubmissions()
    {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);
        $ids = $input['ids'] ?? [];

        if (empty($ids)) {
            echo json_encode(['success' => false, 'message' => 'No se seleccionaron registros']);
            return;
        }

        $formModel = new FormularioModel();
        if ($formModel->deleteSubmissions($ids)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
    }

    public function updateSubmissionData()
    {
        header('Content-Type: application/json');

        // Verificar ID de sumisión
        $submissionId = $_POST['submission_id'] ?? null;
        if (!$submissionId) {
            echo json_encode(['success' => false, 'message' => 'ID de registro no encontrado']);
            return;
        }
        // Preparar datos para el modelo
        // Los inputs vienen como "q_123", necesitamos limpiarlos a "123" para la BD
        $newData = [];

        // 1. Procesar Textos/Selects
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'q_') === 0) {
                $questionId = substr($key, 2); // Quitar 'q_'
                $newData[$questionId] = $value;
            }
        }

        // 2. Procesar Archivos (Fotos)
        $files = $_FILES ?? [];

        $formModel = new FormularioModel();
        $result = $formModel->updateSubmissionData((int) $submissionId, $newData, $files);

        if ($result['success']) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => $result['message']]);
        }
    }

    public function getReportsAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id)
            return;

        // 1. Obtener datos del proyecto para el reporte
        $proyectoModel = new Proyecto();
        $projectData = $proyectoModel->findById((int) $id);

        $formModel = new FormularioModel();

        // 2. Obtener lista de informes personalizados
        $customReports = $formModel->getCustomReports((int) $id);

        // 3. Obtener definición para el modal
        $formDef = $formModel->getLatestFormVersion((int) $id);
        $allQuestions = json_decode($formDef ?: '[]', true);

        // 4. Lógica del reporte seleccionado
        $currentReportId = $_GET['report_id'] ?? '';
        $filterQuestions = [];
        $currentReportName = 'Informe predeterminado';

        if (!empty($currentReportId)) {
            $reportData = $formModel->getCustomReportById((int) $currentReportId);
            if ($reportData) {
                $filterQuestions = json_decode($reportData['questions_json'], true);
                $currentReportName = $reportData['name'];
            }
        }

        // 5. Estadísticas
        $stats = $formModel->getProjectStatistics((int) $id, $filterQuestions);

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_reports_ajax.php';
    }

    public function saveReport()
    {
        header('Content-Type: application/json');

        $projectId = $_POST['project_id'] ?? null;
        $name = $_POST['report_name'] ?? '';
        $questions = $_POST['questions'] ?? [];
        $reportId = !empty($_POST['report_id']) ? (int) $_POST['report_id'] : null;

        if (!$projectId || empty($name) || empty($questions)) {
            echo json_encode(['success' => false, 'message' => 'Faltan datos obligatorios.']);
            return;
        }

        $formModel = new FormularioModel();
        // Capturamos el ID del informe guardado
        $savedId = $formModel->saveCustomReport((int) $projectId, $name, $questions, $reportId);

        if ($savedId > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Informe guardado.',
                'report_id' => $savedId
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar.']);
        }
    }

    // --- VISTA AJAX DE DESCARGA ---
    public function getExportAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id)
            return;

        $formModel = new FormularioModel();

        // Obtener preguntas y historial
        $formDef = $formModel->getLatestFormVersion((int) $id);
        $allQuestions = json_decode($formDef ?: '[]', true);
        $history = $formModel->getExportHistory((int) $id);

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_export_ajax.php';
    }

    public function processExport()
    {
        header('Content-Type: application/json');

        $projectId = $_POST['project_id'] ?? null;
        $fileFormat = $_POST['format'] ?? 'xlsx'; // xlsx o csv
        $columns = $_POST['columns'] ?? []; // Array de IDs seleccionados
        $startDate = $_POST['start_date'] ?? null;
        $endDate = $_POST['end_date'] ?? null;

        if (!$projectId || empty($columns)) {
            echo json_encode(['success' => false, 'message' => 'Selecciona al menos una columna.']);
            return;
        }

        // --- 1. OBTENER DATOS DEL PROYECTO ---
        $proyectoModel = new Proyecto();
        $projectData = $proyectoModel->findById((int) $projectId);

        // Sanitizar nombre: Reemplaza espacios por guion bajo y elimina caracteres especiales
        $rawName = $projectData['nombre'] ?? 'Proyecto';
        $cleanProjectName = preg_replace('/[^A-Za-z0-9_\-]/', '', str_replace(' ', '_', $rawName));

        $dateStr = date('Ymd');

        $formModel = new FormularioModel();
        $submissions = $formModel->getSubmissionsByProjectId((int) $projectId);

        $formDef = $formModel->getLatestFormVersion((int) $projectId);
        $questions = json_decode($formDef ?: '[]', true);
        $qMap = [];
        foreach ($questions as $q) {
            $qMap[$q['id']] = $q['text'];
        }

        $systemLabels = [
            '_id' => 'ID Registro',
            '_submission_time' => 'Fecha Envío',
            '_submitted_by' => 'Usuario',
            '_validation_status' => 'Estado',
            'start' => 'Start',
            'end' => 'End'
        ];

        // --- 2. CREAR EL OBJETO SPREADSHEET ---
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Datos Exportados');

        // --- 3. ESCRIBIR ENCABEZADOS (Fila 1) ---
        $colIndex = 1;
        foreach ($columns as $colId) {
            $headerText = $colId;
            if (isset($systemLabels[$colId])) {
                $headerText = $systemLabels[$colId];
            } elseif (isset($qMap[$colId])) {
                $headerText = $qMap[$colId];
            }

            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $cellAddress = $columnLetter . '1';

            $sheet->setCellValue($cellAddress, $headerText);
            $sheet->getStyle($cellAddress)->getFont()->setBold(true);
            $colIndex++;
        }

        // --- 4. ESCRIBIR DATOS ---
        $rowIndex = 2;
        $exportCount = 0;
        $dbColumns = ['_id', '_submission_time', '_submitted_by', '_validation_status'];

        foreach ($submissions as $sub) {
            // Filtro de fecha
            $subDate = date('Y-m-d', strtotime($sub['_submission_time']));
            if ($startDate && $subDate < $startDate)
                continue;
            if ($endDate && $subDate > $endDate)
                continue;

            $answers = json_decode($sub['submission_data'], true);
            $colIndex = 1;

            foreach ($columns as $colId) {
                $value = '';

                // A) Datos Directos de la Tabla SQL
                if (in_array($colId, $dbColumns)) {
                    $value = $sub[$colId] ?? '';
                    if ($colId === '_validation_status') {
                        $value = ($value === 'approved') ? 'Aprobado' : (($value === 'rejected') ? 'Rechazado' : 'En espera');
                    }
                }
                // B) Datos del JSON (start, end, preguntas)
                else {
                    $rawVal = $answers[$colId] ?? '';

                    if (is_array($rawVal)) {
                        $value = implode(', ', $rawVal);
                    } elseif (is_string($rawVal) && strpos($rawVal, 'uploads/') === 0) {
                        $value = PUBLIC_URL . '/' . $rawVal;
                    } else {
                        $value = $rawVal;
                    }
                }

                $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
                $cellAddress = $columnLetter . $rowIndex;

                $sheet->setCellValue($cellAddress, $value);
                $colIndex++;
            }
            $rowIndex++;
            $exportCount++;
        }

        foreach (range(1, count($columns)) as $col) {
            $columnLetter = Coordinate::stringFromColumnIndex($col);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        $fileName = $cleanProjectName . '_' . $dateStr . '.' . $fileFormat;

        $subDir = 'exports/';
        $dir = PROJECT_ROOT . '/public/' . $subDir;
        if (!is_dir($dir))
            mkdir($dir, 0755, true);

        $filePath = $dir . $fileName;
        $publicPath = $subDir . $fileName;

        if ($fileFormat === 'csv') {
            $writer = new Csv($spreadsheet);
            $writer->setDelimiter(',');
            $writer->setEnclosure('"');
            $writer->setSheetIndex(0);
        } else {
            $writer = new Xlsx($spreadsheet);
        }

        $writer->save($filePath);

        // --- 6. REGISTRAR HISTORIAL ---
        $userId = $_SESSION['admin_user_id'] ?? 1;
        $formModel->logExport((int) $projectId, $userId, strtoupper($fileFormat), $publicPath, $exportCount);

        echo json_encode(['success' => true]);
    }

    public function deleteExport($params)
    {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id)
            return;
        $model = new FormularioModel();
        echo json_encode(['success' => $model->deleteExport((int) $id)]);
    }

    public function getMapAjax($params)
    {
        $id = $params[0] ?? null;
        if (!$id)
            return;

        $formModel = new FormularioModel();

        // 1. Obtener definición del formulario (para saber las preguntas)
        $formDef = $formModel->getLatestFormVersion((int) $id);
        $questions = json_decode($formDef ?: '[]', true);

        // Buscar pregunta GPS
        $gpsQuestion = null;
        foreach ($questions as $q) {
            if ($q['type'] === 'gps') {
                $gpsQuestion = $q;
                break;
            }
        }

        $markers = [];
        // Se permite mostrar mapa si hay pregunta GPS o si el sistema detecta lat/lon en metadatos
        $hasGpsField = ($gpsQuestion !== null);

        // 2. Extraer datos
        $submissions = $formModel->getSubmissionsByProjectId((int) $id);

        foreach ($submissions as $sub) {
            $lat = null;
            $lon = null;
            $answers = json_decode($sub['submission_data'], true);

            // A) Intentar desde metadatos del sistema
            if (!empty($sub['latitude']) && !empty($sub['longitude'])) {
                $lat = $sub['latitude'];
                $lon = $sub['longitude'];
            }
            // B) Intentar desde la respuesta de la pregunta GPS
            elseif ($gpsQuestion && isset($answers[$gpsQuestion['id']])) {
                $val = $answers[$gpsQuestion['id']];
                if (is_string($val)) {
                    // Regex potente: busca números (positivos/negativos/decimales)
                    preg_match_all('/[-+]?[0-9]*\.?[0-9]+/', $val, $matches);
                    if (isset($matches[0]) && count($matches[0]) >= 2) {
                        $lat = $matches[0][0];
                        $lon = $matches[0][1];
                    }
                }
            }

            // Si encontramos coordenadas válidas
            if ($lat && $lon && is_numeric($lat) && is_numeric($lon)) {
                $markers[] = [
                    'id' => $sub['_id'],
                    'lat' => (float) $lat,
                    'lng' => (float) $lon,
                    'user' => $sub['_submitted_by'] ?? 'Anónimo',
                    'date' => date('d/m/Y H:i', strtotime($sub['_submission_time'])),
                    'status' => $sub['_validation_status'],
                    'answers' => $answers 
                ];
            }
        }
        if (!empty($markers))
            $hasGpsField = true;

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_map_ajax.php';
    }
}