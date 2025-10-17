<?php
namespace App\Controllers;

class VoluntariadoController {
    public function index() {
        $page_title = 'Voluntariado | Banderas Sin Fronteras';
        include '../app/Views/voluntariado/index.php';
    }
}