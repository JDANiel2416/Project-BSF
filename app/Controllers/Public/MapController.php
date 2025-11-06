<?php
namespace App\Controllers\Public;

class MapController {
    public function index() {
        $page_title = 'Nuestra Ubicación | Banderas Sin Fronteras';
        
        $data = [
            'lat' => -12.2196,
            'lng' => -76.9325,
            'zoom' => 15,
            'popup_message' => 'Sede de Banderas Sin Fronteras'
        ];

        include '../app/Views/public/mapa/index.php';
    }
}