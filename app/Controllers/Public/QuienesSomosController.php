<?php
namespace App\Controllers\Public;

class QuienesSomosController {
    public function index() {
        $page_title = 'Quiénes Somos | Banderas Sin Fronteras';
        include '../app/Views/public/quienes_somos/index.php';
    }
}