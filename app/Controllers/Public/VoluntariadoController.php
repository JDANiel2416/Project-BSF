<?php
namespace App\Controllers\Public;

class VoluntariadoController {
    public function index() {
        $page_title = 'Voluntariado | Banderas Sin Fronteras';
        include '../app/Views/public/voluntariado/index.php';
    }
}