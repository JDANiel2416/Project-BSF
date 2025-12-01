<?php
namespace App\Models\Public;

class FormPublicModel {

    public function getFormByProjectId(int $projectId) {
        $db = \conectarDB();
        
        // Buscar la última versión del formulario asociado al proyecto
        // SOLO si el proyecto está 'active'
        $query = "
            SELECT 
                p.title as project_title,
                p.description as project_desc,
                fv.id as version_id,
                fv.form_definition
            FROM projects p
            JOIN forms f ON p.id = f.project_id
            JOIN form_versions fv ON f.id = fv.form_id
            WHERE p.id = ? 
            AND p.status = 'active' 
            ORDER BY fv.id DESC 
            LIMIT 1
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $projectId);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);
        
        mysqli_close($db);
        return $data;
    }

    public function saveSubmission(int $formVersionId, string $jsonData) {
        $db = \conectarDB();
        
        $uuid = uniqid('sub_', true);
        
        $query = "INSERT INTO submissions (uuid, form_version_id, submission_data, submitted_at) VALUES (?, ?, ?, NOW())";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'sis', $uuid, $formVersionId, $jsonData);
        
        $success = mysqli_stmt_execute($stmt);
        
        mysqli_close($db);
        return $success;
    }
}