<?php
namespace App\Models\Admin;

class FormularioModel {
    /**
     * @param int $projectId
     * @param string $formDefinitionJson
     * @return bool
     */
    public function saveFormVersion(int $projectId, string $formDefinitionJson) {
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

    public function getLatestFormVersion(int $projectId) {
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

    public function countVersions(int $projectId) {
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
}