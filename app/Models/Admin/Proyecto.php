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
                u.full_name AS propietario, 
                p.created_at AS fecha_modificacion
            FROM 
                projects AS p
            LEFT JOIN 
                users AS u ON p.owner_id = u.id
            ORDER BY 
                p.created_at DESC";
        
        $resultado = mysqli_query($db, $query);
        
        if (!$resultado) {
            die("Error en la consulta: " . mysqli_error($db));
        }
        
        $proyectos = [];
        while ($fila = mysqli_fetch_assoc($resultado)) {
            $proyectos[] = $fila;
        }
        
        mysqli_close($db);
        return $proyectos;
    }

    public function countByStatus() {
        $db = \conectarDB();
        
        $query = "SELECT status, COUNT(*) as total FROM projects GROUP BY status";

        $resultado = mysqli_query($db, $query);

        $counts = [
            'active' => 0,
            'archived' => 0
        ];

        while($fila = mysqli_fetch_assoc($resultado)) {
            if (isset($counts[$fila['status']])) {
                $counts[$fila['status']] = $fila['total'];
            }
        }

        mysqli_close($db);
        return $counts;
    }

    /**
     * @param string $nombre
     * @param string $descripcion
     * @param string $sector
     * @param string $pais
     * @param int $owner_id
     * @return int|false
     */
    public function create(string $nombre, string $descripcion, string $sector, string $pais, int $owner_id) {
        $db = \conectarDB();
        
        $query = "INSERT INTO projects (title, description, sector, country, owner_id, status, created_at) VALUES (?, ?, ?, ?, ?, 'draft', NOW())";
        
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'ssssi', $nombre, $descripcion, $sector, $pais, $owner_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $newId = mysqli_insert_id($db);
            mysqli_stmt_close($stmt);
            mysqli_close($db);
            return $newId;
        } else {
            mysqli_stmt_close($stmt);
            mysqli_close($db);
            return false;
        }
    }

    /**
     * @param int $id
     * @return array|null
     */
    public function findById(int $id) {
        $db = \conectarDB();
        $query = "SELECT title as nombre FROM projects WHERE id = ? LIMIT 1";
        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $proyecto = mysqli_fetch_assoc($resultado);
        mysqli_stmt_close($stmt);
        mysqli_close($db);
        return $proyecto;
    }

    /**
     * @param int $id
     * @return array|null
     */
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
                -- Usamos JSON_LENGTH para contar las preguntas en el JSON guardado
                JSON_LENGTH(latest_fv.form_definition) as question_count
            FROM 
                projects AS p
            JOIN 
                users AS u ON p.owner_id = u.id
            -- Hacemos un LEFT JOIN para que funcione incluso si el proyecto aún no tiene formulario
            LEFT JOIN 
                forms AS f ON p.id = f.project_id
            LEFT JOIN
                -- Subconsulta para obtener SOLO la ID de la versión más reciente del formulario
                (SELECT form_id, MAX(id) as max_id FROM form_versions GROUP BY form_id) AS max_fv
                ON f.id = max_fv.form_id
            LEFT JOIN
                form_versions AS latest_fv ON max_fv.max_id = latest_fv.id
            WHERE 
                p.id = ?
            LIMIT 1
        ";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $proyecto = mysqli_fetch_assoc($resultado);
        
        mysqli_close($db);
        return $proyecto;
    }
}