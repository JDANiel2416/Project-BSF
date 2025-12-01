<?php
namespace App\Models\Admin;

class Proyecto {
    
    public function getAll() {
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

    public function getArchived() {
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

    public function countByStatus() {
        $db = \conectarDB();
        $query = "SELECT status, COUNT(*) as total FROM projects GROUP BY status";
        $resultado = mysqli_query($db, $query);
        $counts = ['active' => 0, 'archived' => 0, 'draft' => 0];

        if ($resultado) {
            while($fila = mysqli_fetch_assoc($resultado)) {
                if (isset($counts[$fila['status']])) {
                    $counts[$fila['status']] = $fila['total'];
                }
            }
        }
        mysqli_close($db);
        return $counts;
    }

    public function create(string $nombre, string $descripcion, string $sector, string $pais, int $owner_id) {
        $db = \conectarDB();
        $query = "INSERT INTO projects (title, description, sector, country, owner_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'draft', NOW())";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $descripcion, $sector, $pais, $owner_id);
        
        $newId = false;
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($db);
        }
        
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $newId;
    }

    public function findById(int $id) {
        $db = \conectarDB();
        $query = "SELECT id, title AS nombre, description, sector, country AS pais, status FROM projects WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $proyecto = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $proyecto;
    }

    public function getProjectSummaryById(int $id) {
        $db = \conectarDB();
        $query = "
            SELECT 
                p.id,
                p.title,
                p.description,
                p.status,
                p.sector,
                p.country,
                p.created_at,
                u.full_name AS propietario,
                (SELECT COUNT(*) FROM forms WHERE project_id = p.id) as form_count
            FROM 
                projects AS p
            LEFT JOIN 
                users AS u ON p.owner_id = u.id
            WHERE 
                p.id = ?
            LIMIT 1
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
    public function updateStatus(int $id, string $status) {
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
    public function update(int $id, string $nombre, string $descripcion, string $sector, string $pais) {
        $db = \conectarDB();
        $query = "UPDATE projects SET title = ?, description = ?, sector = ?, country = ? WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $descripcion, $sector, $pais, $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }

    public function delete(int $id) {
        $db = \conectarDB();
        $query = "DELETE FROM projects WHERE id = ?";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        $success = mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $success;
    }
}