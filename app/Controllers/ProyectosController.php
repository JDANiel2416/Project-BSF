<?php
namespace App\Controllers;

class ProyectosController {
    public function index() {
        $page_title = 'Nuestros Proyectos | Banderas Sin Fronteras';
        include '../app/Views/proyectos/index.php';
    }
}