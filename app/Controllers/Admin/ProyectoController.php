<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Proyecto;
use App\Models\Admin\FormularioModel;

class ProyectoController
{
    public function __construct() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Sesión expirada', 'redirect' => ADMIN_URL . '/login']);
                exit;
            }
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public function getProjectDetailAjax($params) {
        $id = $params[0] ?? null;
        if (!$id) { http_response_code(400); return; }

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->getProjectSummaryById((int)$id);

        if (!$proyecto) { http_response_code(404); echo "Proyecto no encontrado."; return; }

        $formModel = new FormularioModel();
        $formDefinitionJson = $formModel->getLatestFormVersion((int)$id);
        $questionsJson = $formDefinitionJson ?: '[]'; 

        // --- NUEVO: Datos para la pestaña Formulario ---
        $versionCount = $formModel->countVersions((int)$id);
        // Generamos enlace público (asumiendo que tienes una ruta pública configurada)
        $publicFormUrl = PUBLIC_URL . "/f/" . $id; 

        $proyecto['sector'] = $proyecto['sector'] ?? '-';
        $proyecto['country'] = $proyecto['country'] ?? '-';
        $proyecto['description'] = $proyecto['description'] ?? '';
        
        include PROJECT_ROOT . '/app/Views/admin/proyecto/_summary_ajax.php';
    }

    public function implementar($params) {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) { 
            echo json_encode(['success' => false, 'message' => 'ID inválido']); 
            return; 
        }

        $proyectoModel = new Proyecto();
        
        // Validación: No permitir implementar si no hay preguntas (opcional, según lógica de negocio)
        $projectData = $proyectoModel->getProjectSummaryById((int)$id);
        if ($projectData['question_count'] == 0) {
            echo json_encode(['success' => false, 'message' => 'No puedes implementar un formulario vacío.']);
            return;
        }

        // Cambiar estado a 'active'
        $success = $proyectoModel->updateStatus((int)$id, 'active');

        echo json_encode([
            'success' => $success,
            'message' => $success ? 'Proyecto implementado exitosamente.' : 'Error al implementar.'
        ]);
    }
    

    public function getFormConstructorAjax($params) {
        $id = $params[0] ?? null;
        if (!$id) return;

        $proyectoModel = new Proyecto();
        $proyecto = $proyectoModel->findById((int)$id);
        
        $formModel = new FormularioModel();
        $formDefinitionJson = $formModel->getLatestFormVersion((int)$id);
        $questionsJson = $formDefinitionJson ?: '[]';

        include PROJECT_ROOT . '/app/Views/admin/proyecto/_form_constructor_ajax.php';
    }

    public function crear() {
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

    // --- ACTUALIZADO: Actualizar datos completos ---
    public function actualizar($params) {
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
        $success = $proyectoModel->update((int)$id, $nombre, $descripcion, $sector, $pais);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Proyecto actualizado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar en base de datos.']);
        }
    }

    public function eliminar($params) {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) { 
            echo json_encode(['success' => false, 'message' => 'ID no proporcionado']); 
            return; 
        }
        
        $proyectoModel = new Proyecto();
        $success = $proyectoModel->delete((int)$id);
        
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Proyecto eliminado permanentemente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar.']);
        }
    }

    public function archivar($params) {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->updateStatus((int)$id, 'archived');

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Proyecto archivado.' : 'Error al archivar.'
        ]);
    }

    public function restaurar($params) {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        $proyectoModel = new Proyecto();
        $success = $proyectoModel->updateStatus((int)$id, 'active');

        echo json_encode([
            'success' => $success, 
            'message' => $success ? 'Proyecto restaurado.' : 'Error al restaurar.'
        ]);
    }

    public function guardarFormulario($params) {
        header('Content-Type: application/json');
        $id = $params[0] ?? null;
        $definition = $_POST['form_definition'] ?? '[]';

        if (!$id) { echo json_encode(['success' => false, 'message' => 'ID inválido']); return; }

        $formModel = new FormularioModel();
        $result = $formModel->saveFormVersion((int)$id, $definition);

        echo json_encode(['success' => $result, 'message' => $result ? 'Guardado' : 'Error al guardar']);
    }
    
    public function listarArchivados() {
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
}