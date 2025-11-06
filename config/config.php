<?php
// URL base para los archivos públicos (CSS, JS, imágenes)
define('PUBLIC_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/BSF/public');

// URL base para las rutas del sitio público
define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/BSF/public');
define('BASE_PATH', '/BSF/public');

// URL y Path base para la sección de administración
define('ADMIN_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/BSF/admin');
define('ADMIN_BASE_PATH', '/BSF/admin');

// Ruta raíz del proyecto para includes
define('PROJECT_ROOT', dirname(__DIR__));