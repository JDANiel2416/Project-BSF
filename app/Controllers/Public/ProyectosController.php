<?php
namespace App\Controllers\Public;

class ProyectosController {
    public function index() {
        $page_title = 'Nuestros Proyectos | Banderas Sin Fronteras';
        include '../app/Views/public/proyectos/index.php';
    }
}