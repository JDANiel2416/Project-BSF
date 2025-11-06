<?php
namespace App\Controllers\Public;

class ContactoController {
    public function index() {
        $page_title = 'Contacto | Banderas Sin Fronteras';
        include '../app/Views/public/contacto/index.php';
    }
}