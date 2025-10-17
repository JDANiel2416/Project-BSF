<?php
namespace App\Controllers;

class ContactoController {
    public function index() {
        $page_title = 'Contacto | Banderas Sin Fronteras';
        include '../app/Views/contacto/index.php';
    }
}