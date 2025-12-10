<?php
namespace App\Models\Admin;

class Proyecto
{
    public function getAll()
    {
        $db = \conectarDB();
        $query = "
            SELECT 
                p.id, 
                p.title AS nombre, 
                p.status AS estado, 
                p.sector,
                p.country AS pais,
                u.full_name AS propietario, 
                p.created_at AS fecha_modificacion
            FROM 
                projects AS p
            LEFT JOIN 
                users AS u ON p.owner_id = u.id
            WHERE p.status != 'archived'
            ORDER BY 
                p.created_at DESC";

        $resultado = mysqli_query($db, $query);

        $proyectos = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $proyectos[] = $fila;
            }
        }

        mysqli_close($db);
        return $proyectos;
    }

    public function getArchived()
    {
        $db = \conectarDB();
        $query = "
            SELECT 
                p.id, 
                p.title AS nombre, 
                p.status AS estado, 
                p.sector,
                p.country AS pais,
                u.full_name AS propietario, 
                p.created_at AS fecha_modificacion
            FROM 
                projects AS p
            LEFT JOIN 
                users AS u ON p.owner_id = u.id
            WHERE p.status = 'archived'
            ORDER BY 
                p.created_at DESC";

        $resultado = mysqli_query($db, $query);

        $proyectos = [];
        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                $proyectos[] = $fila;
            }
        }

        mysqli_close($db);
        return $proyectos;
    }

    public function countByStatus()
    {
        $db = \conectarDB();
        $query = "SELECT status, COUNT(*) as total FROM projects GROUP BY status";
        $resultado = mysqli_query($db, $query);
        $counts = ['active' => 0, 'archived' => 0, 'draft' => 0];

        if ($resultado) {
            while ($fila = mysqli_fetch_assoc($resultado)) {
                if (isset($counts[$fila['status']])) {
                    $counts[$fila['status']] = $fila['total'];
                }
            }
        }
        mysqli_close($db);
        return $counts;
    }

    public function create(string $nombre, string $descripcion, string $sector, string $pais, int $owner_id)
    {
        $db = \conectarDB();

        // Generar token aleatorio de 10 caracteres
        $token = $this->generateUniqueToken($db);

        $query = "INSERT INTO projects (token, title, description, sector, country, owner_id, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'draft', NOW())";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'sssssi', $token, $nombre, $descripcion, $sector, $pais, $owner_id);

        $newId = false;
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($db);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $newId;
    }

    private function generateUniqueToken($db)
    {
        do {
            $token = substr(bin2hex(random_bytes(10)), 0, 10); // Genera string tipo 'a1b2c3d4e5'
            $check = mysqli_query($db, "SELECT id FROM projects WHERE token = '$token'");
        } while (mysqli_num_rows($check) > 0);
        return $token;
    }

    public function findById(int $id)
    {
        $db = \conectarDB();
        $query = "SELECT id, token, title AS nombre, description, sector, country AS pais, status FROM projects WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $proyecto = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $proyecto;
    }

    public function getProjectSummaryById(int $id)
    {
        $db = \conectarDB();

        $query = "
            SELECT 
                p.id, p.token, p.title, p.description, p.status, p.sector, p.country, p.created_at,
                u.full_name AS propietario,
                (SELECT COUNT(*) FROM forms WHERE project_id = p.id) as form_count,
                
                (SELECT MAX(fv.created_at) 
                FROM form_versions fv 
                JOIN forms f ON f.id = fv.form_id 
                WHERE f.project_id = p.id) as last_modified_at,

                (SELECT MAX(published_at) 
                FROM form_versions fv 
                JOIN forms f ON f.id = fv.form_id 
                WHERE f.project_id = p.id AND fv.status = 'published') as last_deployed_at,

                (SELECT MAX(s.submitted_at) 
                FROM submissions s 
                JOIN form_versions fv ON s.form_version_id = fv.id 
                JOIN forms f ON fv.form_id = f.id 
                WHERE f.project_id = p.id) as last_submission_at

            FROM projects AS p
            LEFT JOIN users AS u ON p.owner_id = u.id
            WHERE p.id = ? LIMIT 1
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $proyecto = mysqli_fetch_assoc($resultado);

        // Obtener conteo de preguntas de la última versión del formulario
        if ($proyecto) {
            $queryQuestions = "
                SELECT JSON_LENGTH(fv.form_definition) as q_count 
                FROM form_versions fv 
                JOIN forms f ON f.id = fv.form_id 
                WHERE f.project_id = ? 
                ORDER BY fv.id DESC LIMIT 1";

            $stmtQ = mysqli_prepare($db, $queryQuestions);
            mysqli_stmt_bind_param($stmtQ, 'i', $id);
            mysqli_stmt_execute($stmtQ);
            $resQ = mysqli_stmt_get_result($stmtQ);
            $rowQ = mysqli_fetch_assoc($resQ);
            $proyecto['question_count'] = $rowQ['q_count'] ?? 0;
            mysqli_stmt_close($stmtQ);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $proyecto;
    }

    // Actualizar estado (Archivar/Restaurar)
    public function updateStatus(int $id, string $status)
    {
        $db = \conectarDB();
        $query = "UPDATE projects SET status = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'si', $status, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }

    // --- ACTUALIZADO: Ahora recibe sector y país ---
    public function update(int $id, string $nombre, string $descripcion, string $sector, string $pais)
    {
        $db = \conectarDB();
        $query = "UPDATE projects SET title = ?, description = ?, sector = ?, country = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $descripcion, $sector, $pais, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }

    public function delete(int $id)
    {
        $db = \conectarDB();

        // --- PASO 1: Borrar imágenes de los envíos ---
        $queryFiles = "
            SELECT s.submission_data 
            FROM submissions s
            INNER JOIN form_versions fv ON s.form_version_id = fv.id
            INNER JOIN forms f ON fv.form_id = f.id
            WHERE f.project_id = ?
        ";

        $stmtFiles = mysqli_prepare($db, $queryFiles);
        mysqli_stmt_bind_param($stmtFiles, 'i', $id);
        mysqli_stmt_execute($stmtFiles);
        $resultFiles = mysqli_stmt_get_result($stmtFiles);

        while ($row = mysqli_fetch_assoc($resultFiles)) {
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
        mysqli_stmt_close($stmtFiles);

        // --- PASO 2: Borrar archivos de EXPORTACIONES ---
        $queryExports = "SELECT file_path FROM project_exports WHERE project_id = ?";
        $stmtExp = mysqli_prepare($db, $queryExports);
        mysqli_stmt_bind_param($stmtExp, 'i', $id);
        mysqli_stmt_execute($stmtExp);
        $resExp = mysqli_stmt_get_result($stmtExp);

        while ($row = mysqli_fetch_assoc($resExp)) {
            $exportFilePath = PROJECT_ROOT . '/public/' . $row['file_path'];

            if (file_exists($exportFilePath)) {
                unlink($exportFilePath);
            }
        }
        mysqli_stmt_close($stmtExp);

        // --- PASO 3: Eliminar el proyecto de la BD ---
        $query = "DELETE FROM projects WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $success = mysqli_stmt_execute($stmt);

        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }
}