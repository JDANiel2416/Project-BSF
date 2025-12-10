<?php
namespace App\Models\Public;

class FormPublicModel
{
    public function getFormByProjectToken(string $token)
    {
        $db = \conectarDB();

        $query = "
            SELECT 
                p.id as project_id,
                p.title as project_title,
                p.description as project_desc,
                fv.id as version_id,
                fv.form_definition
            FROM projects p
            JOIN forms f ON p.id = f.project_id
            JOIN form_versions fv ON f.id = fv.form_id
            WHERE p.token = ? 
            AND p.status = 'active' 
            AND fv.status = 'published'
            ORDER BY fv.id DESC 
            LIMIT 1
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 's', $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $data = mysqli_fetch_assoc($result);

        mysqli_close($db);
        return $data;
    }

    public function saveSubmission(int $formVersionId, string $jsonData, ?string $start = null, ?string $end = null)
    {
        $db = \conectarDB();

        $uuid = uniqid('sub_', true);

        $query = "INSERT INTO submissions (uuid, form_version_id, submission_data, start_time, end_time, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'sisss', $uuid, $formVersionId, $jsonData, $start, $end);

        $success = mysqli_stmt_execute($stmt);

        mysqli_close($db);
        return $success;
    }
}