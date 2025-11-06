<?php
namespace App\Controllers\Public;

class InicioController {
    public function index() {
        $page_title = 'Banderas Sin Fronteras | Inicio';
        include '../app/Views/public/layout/header.php';
        include '../app/Views/public/inicio/index.php';
        include '../app/Views/public/layout/footer.php';
    }
}