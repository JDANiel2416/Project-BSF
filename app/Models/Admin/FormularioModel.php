<?php
namespace App\Models\Admin;

class FormularioModel
{
    /**
     * @param int $projectId
     * @param string $formDefinitionJson
     * @return bool
     */
    public function saveFormVersion(int $projectId, string $formDefinitionJson)
    {
        $db = \conectarDB();

        mysqli_begin_transaction($db);

        try {
            $formId = null;
            $queryForm = "SELECT id FROM forms WHERE project_id = ? LIMIT 1";
            $stmtForm = mysqli_prepare($db, $queryForm);
            mysqli_stmt_bind_param($stmtForm, 'i', $projectId);
            mysqli_stmt_execute($stmtForm);
            $resultForm = mysqli_stmt_get_result($stmtForm);

            if ($row = mysqli_fetch_assoc($resultForm)) {
                $formId = $row['id'];
            } else {
                $queryProjectTitle = "SELECT title FROM projects WHERE id = ? LIMIT 1";
                $stmtProject = mysqli_prepare($db, $queryProjectTitle);
                mysqli_stmt_bind_param($stmtProject, 'i', $projectId);
                mysqli_stmt_execute($stmtProject);
                $project = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtProject));
                $formTitle = $project['title'] ?? 'Formulario sin título';

                $uuid = bin2hex(random_bytes(16));
                $queryInsertForm = "INSERT INTO forms (project_id, uuid, title) VALUES (?, ?, ?)";
                $stmtInsertForm = mysqli_prepare($db, $queryInsertForm);
                mysqli_stmt_bind_param($stmtInsertForm, 'iss', $projectId, $uuid, $formTitle);
                mysqli_stmt_execute($stmtInsertForm);
                $formId = mysqli_insert_id($db);
            }

            if (!$formId) {
                throw new \Exception("No se pudo obtener o crear el ID del formulario.");
            }

            $newVersionNumber = 1;
            $queryVersion = "SELECT MAX(version_number) as latest_version FROM form_versions WHERE form_id = ?";
            $stmtGetVersion = mysqli_prepare($db, $queryVersion);
            mysqli_stmt_bind_param($stmtGetVersion, 'i', $formId);
            mysqli_stmt_execute($stmtGetVersion);
            $resultVersion = mysqli_stmt_get_result($stmtGetVersion);
            if ($rowVersion = mysqli_fetch_assoc($resultVersion)) {
                if ($rowVersion['latest_version'] !== null) {
                    $newVersionNumber = $rowVersion['latest_version'] + 1;
                }
            }

            $queryInsertVersion = "INSERT INTO form_versions (form_id, version_number, form_definition) VALUES (?, ?, ?)";
            $stmtVersion = mysqli_prepare($db, $queryInsertVersion);
            mysqli_stmt_bind_param($stmtVersion, 'iis', $formId, $newVersionNumber, $formDefinitionJson);

            if (!mysqli_stmt_execute($stmtVersion)) {
                throw new \Exception("No se pudo guardar la versión del formulario: " . mysqli_stmt_error($stmtVersion));
            }

            mysqli_commit($db);
            mysqli_close($db);
            return true;

        } catch (\Exception $e) {
            mysqli_rollback($db);
            mysqli_close($db);
            error_log($e->getMessage());
            return false;
        }
    }

    public function getLatestVersionStatus(int $projectId)
    {
        $db = \conectarDB();
        $query = "
            SELECT fv.status
            FROM form_versions fv
            JOIN forms f ON f.id = fv.form_id
            WHERE f.project_id = ?
            ORDER BY fv.id DESC
            LIMIT 1
        ";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_close($db);

        // Si no existe versión, asumimos 'draft'
        return $row ? $row['status'] : 'draft';
    }

    public function publishLatestVersion(int $projectId)
    {
        $db = \conectarDB();
        // 1. Obtener ID de la última versión
        $queryIds = "SELECT fv.id FROM form_versions fv JOIN forms f ON f.id = fv.form_id WHERE f.project_id = ? ORDER BY fv.id DESC LIMIT 1";
        $stmt = mysqli_prepare($db, $queryIds);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);

        if (!$row) {
            mysqli_close($db);
            return false;
        }

        $versionId = $row['id'];

        // 2. Actualizar esa versión a 'published'
        $update = "UPDATE form_versions SET status = 'published', published_at = NOW() WHERE id = ?";
        $stmtUp = mysqli_prepare($db, $update);
        mysqli_stmt_bind_param($stmtUp, 'i', $versionId);
        $success = mysqli_stmt_execute($stmtUp);

        mysqli_close($db);
        return $success;
    }

    public function getLatestFormVersion(int $projectId)
    {
        $db = \conectarDB();

        $query = "
            SELECT fv.form_definition
            FROM form_versions fv
            JOIN forms f ON f.id = fv.form_id
            WHERE f.project_id = ?
            ORDER BY fv.id DESC
            LIMIT 1
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_close($db);

        return $row ? $row['form_definition'] : null;
    }

    public function countVersions(int $projectId)
    {
        $db = \conectarDB();
        $query = "SELECT COUNT(*) as total FROM form_versions fv JOIN forms f ON f.id = fv.form_id WHERE f.project_id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $row['total'] ?? 0;
    }
    public function getSubmissionsByProjectId(int $projectId)
    {
        $db = \conectarDB();

        $query = "
            SELECT 
                s.id AS _id,
                s.uuid AS _uuid,
                s.submitted_at AS _submission_time,
                s.validation_status AS _validation_status,
                s.start_time AS start,
                s.end_time AS end,
                s.submission_data,
                fv.version_number AS __version__,
                COALESCE(u.username, 'Anónimo') AS _submitted_by,
                s.latitude,
                s.longitude
            FROM submissions s
            JOIN form_versions fv ON s.form_version_id = fv.id
            JOIN forms f ON fv.form_id = f.id
            LEFT JOIN users u ON s.submitter_id = u.id
            WHERE f.project_id = ?
            ORDER BY s.submitted_at DESC
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $submissions = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $submissions[] = $row;
        }

        mysqli_close($db);
        return $submissions;
    }

    public function updateSubmissionStatus(array $ids, string $status) {
        $db = \conectarDB();
        
        $allowed = ['approved', 'rejected', 'pending_review'];
        if (!in_array($status, $allowed)) return false;

        $idsStr = implode(',', array_map('intval', $ids));
        if (empty($idsStr)) return false;

        $query = "UPDATE submissions SET validation_status = '$status' WHERE id IN ($idsStr)";
        $result = mysqli_query($db, $query);
        
        mysqli_close($db);
        return $result;
    }

    public function deleteSubmissions(array $ids) {
        $db = \conectarDB();
        
        $idsStr = implode(',', array_map('intval', $ids));
        if (empty($idsStr)) return false;
        $querySelect = "SELECT submission_data FROM submissions WHERE id IN ($idsStr)";
        $resultSelect = mysqli_query($db, $querySelect);

        if ($resultSelect) {
            while ($row = mysqli_fetch_assoc($resultSelect)) {
                $data = json_decode($row['submission_data'], true);
                
                if (is_array($data)) {
                    foreach ($data as $respuesta) {
                        if (is_string($respuesta) && strpos($respuesta, 'uploads/') === 0) {
                            
                            $filePath = PROJECT_ROOT . '/public/' . $respuesta;

                            if (file_exists($filePath)) {
                                unlink($filePath);
                            }
                        }
                    }
                }
            }
        }
        $query = "DELETE FROM submissions WHERE id IN ($idsStr)";
        $result = mysqli_query($db, $query);
        
        mysqli_close($db);
        return $result;
    }

    public function updateSubmissionData(int $submissionId, array $newData, array $files) {
        $db = \conectarDB();

        // 1. Obtener datos actuales para mantener lo que no se envió y borrar fotos viejas
        $query = "SELECT submission_data FROM submissions WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $submissionId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);

        if (!$row) {
            return ['success' => false, 'message' => 'Registro no encontrado'];
        }

        $currentData = json_decode($row['submission_data'], true) ?? [];

        // 2. Actualizar datos de texto (mezclar)
        foreach ($newData as $qId => $val) {
            $currentData[$qId] = $val;
        }

        // 3. Procesar Archivos Nuevos
        if (!empty($files)) {
            // Estructura de carpetas igual al FormController público
            $subFolder = date('Y') . '/' . date('m') . '/';
            $uploadDir = PROJECT_ROOT . '/public/uploads/' . $subFolder;

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            foreach ($files as $key => $fileInfo) {
                if (strpos($key, 'q_') === 0 && $fileInfo['error'] === UPLOAD_ERR_OK) {
                    $qId = substr($key, 2);
                    
                    // Borrar archivo anterior si existe
                    if (isset($currentData[$qId]) && strpos($currentData[$qId], 'uploads/') === 0) {
                        $oldPath = PROJECT_ROOT . '/public/' . $currentData[$qId];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }

                    // Subir nuevo
                    $extension = pathinfo($fileInfo['name'], PATHINFO_EXTENSION);
                    $newFilename = time() . '_' . uniqid() . '.' . $extension;
                    $targetPath = $uploadDir . $newFilename;

                    if (move_uploaded_file($fileInfo['tmp_name'], $targetPath)) {
                        $currentData[$qId] = 'uploads/' . $subFolder . $newFilename;
                    }
                }
            }
        }

        // 4. Guardar en Base de Datos
        $newJson = json_encode($currentData);
        $updateQuery = "UPDATE submissions SET submission_data = ? WHERE id = ?";
        $stmtUp = mysqli_prepare($db, $updateQuery);
        mysqli_stmt_bind_param($stmtUp, 'si', $newJson, $submissionId);
        
        $success = mysqli_stmt_execute($stmtUp);
        mysqli_stmt_close($stmtUp);
        mysqli_close($db);

        return ['success' => $success, 'message' => $success ? 'OK' : 'Error SQL'];
    }

    // --- ESTADÍSTICAS CON FILTRO ---
    public function getProjectStatistics(int $projectId, array $filterQuestionIds = []) {
        $db = \conectarDB();

        // 1. Obtener definición de preguntas
        $formDef = $this->getLatestFormVersion($projectId);
        $allQuestions = json_decode($formDef ?: '[]', true);
        // FILTRADO: Si hay IDs específicos, filtramos el array de preguntas
        $questions = [];
        if (!empty($filterQuestionIds)) {
            foreach ($allQuestions as $q) {
                if (in_array($q['id'], $filterQuestionIds)) {
                    $questions[] = $q;
                }
            }
        } else {
            $questions = $allQuestions;
        }

        // 2. Obtener todas las respuestas
        $submissions = $this->getSubmissionsByProjectId($projectId);
        $totalSubmissions = count($submissions);

        $stats = [];

        foreach ($questions as $q) {
            $qId = $q['id'];
            $type = $q['type'];
            $values = [];
            $emptyCount = 0;

            foreach ($submissions as $sub) {
                $data = json_decode($sub['submission_data'], true);
                $val = $data[$qId] ?? null;

                if ($val === null || $val === '') {
                    $emptyCount++;
                } else {
                    $values[] = $val;
                }
            }

            $respondedCount = count($values);
            $qStat = [
                'id' => $qId,
                'text' => $q['text'],
                'type' => $type,
                'total' => $totalSubmissions,
                'responded' => $respondedCount,
                'empty' => $emptyCount,
                'data' => []
            ];

            // A) Numérico
            if ($type === 'number') {
                if ($respondedCount > 0) {
                    $floatValues = array_map('floatval', $values);
                    sort($floatValues);

                    $sum = array_sum($floatValues);
                    $mean = $sum / $respondedCount;

                    $middle = floor(($respondedCount - 1) / 2);
                    if ($respondedCount % 2) {
                        $median = $floatValues[$middle];
                    } else {
                        $median = ($floatValues[$middle] + $floatValues[$middle + 1]) / 2.0;
                    }

                    $vCounts = array_count_values(array_map('strval', $floatValues));
                    arsort($vCounts);
                    $mode = array_key_first($vCounts);
                    if (count($vCounts) > 1 && current($vCounts) === next($vCounts)) {
                        $mode = "* (Múltiple)";
                    }

                    $variance = 0.0;
                    foreach ($floatValues as $v) {
                        $variance += pow($v - $mean, 2);
                    }
                    $stdDev = sqrt($variance / $respondedCount);

                    $qStat['stats'] = [
                        'media' => number_format($mean, 2),
                        'mediana' => number_format($median, 2),
                        'moda' => $mode,
                        'desviacion' => number_format($stdDev, 2)
                    ];
                }
            }
            // B) Texto, Select, Foto, Fecha
            else {
                $cleanValues = array_map(function($v) use ($type) {
                    if ($type === 'photo' && strpos($v, '/') !== false) {
                        return basename($v);
                    }
                    return $v;
                }, $values);

                $counts = array_count_values(array_map('strval', $cleanValues));
                arsort($counts);

                $tableData = [];
                foreach ($counts as $val => $freq) {
                    $tableData[] = [
                        'valor' => $val,
                        'frecuencia' => $freq,
                        'porcentaje' => number_format(($freq / $respondedCount) * 100, 2)
                    ];
                }
                $qStat['data'] = $tableData;
            }

            $stats[] = $qStat;
        }

        return $stats;
    }

    // --- GESTIÓN DE INFORMES PERSONALIZADOS ---
    public function saveCustomReport(int $projectId, string $name, array $questionIds, ?int $reportId = null) {
        $db = \conectarDB();
        $json = json_encode($questionIds);
        $finalId = 0;

        if ($reportId) {
            // Actualizar existente
            $query = "UPDATE custom_reports SET name = ?, questions_json = ? WHERE id = ? AND project_id = ?";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, 'ssii', $name, $json, $reportId, $projectId);
            if (mysqli_stmt_execute($stmt)) {
                $finalId = $reportId;
            }
        } else {
            $query = "INSERT INTO custom_reports (project_id, name, questions_json) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($db, $query);
            mysqli_stmt_bind_param($stmt, 'iss', $projectId, $name, $json);
            if (mysqli_stmt_execute($stmt)) {
                $finalId = mysqli_insert_id($db);
            }
        }

        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $finalId;
    }

    public function getCustomReports(int $projectId) {
        $db = \conectarDB();
        $query = "SELECT id, name, questions_json FROM custom_reports WHERE project_id = ? ORDER BY id DESC";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        $reports = [];
        while ($row = mysqli_fetch_assoc($res)) {
            $reports[] = $row;
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $reports;
    }

    public function getCustomReportById(int $reportId) {
        $db = \conectarDB();
        $query = "SELECT * FROM custom_reports WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $reportId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $row;
    }

    // --- GESTIÓN DE EXPORTACIONES DE DATOS --- //
    public function logExport(int $projectId, int $userId, string $type, string $path, int $count) {
        $db = \conectarDB();
        $query = "INSERT INTO project_exports (project_id, user_id, file_type, file_path, row_count) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'iissi', $projectId, $userId, $type, $path, $count);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }

    public function getExportHistory(int $projectId) {
        $db = \conectarDB();
        $query = "SELECT * FROM project_exports WHERE project_id = ? ORDER BY created_at DESC LIMIT 10";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $data = [];
        while($row = mysqli_fetch_assoc($res)) {
            $data[] = $row;
        }
        mysqli_close($db);
        return $data;
    }

    public function deleteExport(int $exportId) {
        $db = \conectarDB();
        // 1. Obtener ruta para borrar archivo físico
        $qPath = "SELECT file_path FROM project_exports WHERE id = ?";
        $stmt = mysqli_prepare($db, $qPath);
        mysqli_stmt_bind_param($stmt, 'i', $exportId);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        
        if($row && file_exists(PROJECT_ROOT . '/public/' . $row['file_path'])) {
            unlink(PROJECT_ROOT . '/public/' . $row['file_path']);
        }
        mysqli_stmt_close($stmt);

        // 2. Borrar registro de BD
        $query = "DELETE FROM project_exports WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $exportId);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }
}