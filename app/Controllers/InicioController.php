<?php
namespace App\Controllers;

class InicioController {
    public function index() {
        $page_title = 'Banderas Sin Fronteras | Inicio';
        // La ruta debe ser relativa al archivo index.php principal
        include '../app/Views/layout/header.php';
        include '../app/Views/inicio/index.php';
        include '../app/Views/layout/footer.php';
    }
}