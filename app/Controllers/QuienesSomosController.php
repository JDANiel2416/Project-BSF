<?php
namespace App\Controllers;

class QuienesSomosController {
    public function index() {
        $page_title = 'Quiénes Somos | Banderas Sin Fronteras';
        include '../app/Views/quienes_somos/index.php';
    }
}