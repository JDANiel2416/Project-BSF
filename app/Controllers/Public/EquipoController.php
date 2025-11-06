<?php
namespace App\Controllers\Public;

class EquipoController {
    public function index() {
        $page_title = 'Nuestro Equipo | Banderas Sin Fronteras';
        $equipo = [
            ['nombre' => 'Audry', 'cargo' => 'Presidenta'],
            ['nombre' => 'Maryory Aranda', 'cargo' => 'Vice-Presidenta'],
            ['nombre' => 'Carolay', 'cargo' => 'Secretaria'],
            ['nombre' => 'Juan Diego Gil', 'cargo' => 'Tesorero'],
            ['nombre' => 'Yuberki Gimenes', 'cargo' => 'Miembro del Equipo'],
            ['nombre' => 'Mercy Esaa', 'cargo' => 'Miembro del Equipo'],
            ['nombre' => 'Elineth Brito', 'cargo' => 'Miembro del Equipo'],
            ['nombre' => 'Henry Gil', 'cargo' => 'Miembro del Equipo'],
            ['nombre' => 'Mirllatn Guerra', 'cargo' => 'Miembro del Equipo'],
            ['nombre' => 'Josmar Pinto', 'cargo' => 'Miembro del Equipo'],
        ];
        include '../app/Views/public/equipo/index.php';
    }
}