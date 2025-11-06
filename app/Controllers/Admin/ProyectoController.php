<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Proyecto;

class ProyectoController
{
    public function crear()
    {
        header('Content-Type: application/json');

        if (empty($_POST['nombre']) || empty($_POST['sector']) || empty($_POST['pais'])) {
            // ...
        }

        $proyectoModel = new Proyecto();
        $owner_id = $_SESSION['admin_user_id'] ?? 1;
        $proyectoId = $proyectoModel->create($_POST['nombre'], $_POST['descripcion'] ?? '', $_POST['sector'], $_POST['pais'], $owner_id);

        if ($proyectoId) {
            echo json_encode([
                'success' => true,
                'projectId' => $proyectoId,
                'message' => 'Proyecto creado exitosamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar el proyecto en la base de datos.'
            ]);
        }
    }

    public function resumen($params)
    {
        $proyectoId = $params[0] ?? null;
        if (!$proyectoId) {
            http_response_code(404);
            echo "ID de proyecto no especificado.";
            exit;
        }

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->getProjectSummaryById((int) $proyectoId);

        if (!$proyecto) {
            http_response_code(404);
            echo "Proyecto no encontrado.";
            exit;
        }

        $page_title = 'Resumen | ' . htmlspecialchars($proyecto['title']);

        include PROJECT_ROOT . '/app/Views/admin/proyecto/resumen.php';
    }
}