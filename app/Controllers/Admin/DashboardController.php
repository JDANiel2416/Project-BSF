<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Proyecto;

class DashboardController {

    public function __construct() {
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public function index() {
        $page_title = 'Admin Dashboard | Proyectos';

        $proyectoModel = new Proyecto();
        
        $proyectos = $proyectoModel->getAll();
        $statusCounts = $proyectoModel->countByStatus();
        
        include PROJECT_ROOT . '/app/Views/admin/dashboard.php';
    }
}