<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bsf_db');

/**
 * @return mysqli
 */
function conectarDB() {
    $conexion = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conexion) {
        echo "Error: No se pudo conectar a la base de datos.";
        echo "errno de depuración: " . mysqli_connect_errno();
        echo "error de depuración: " . mysqli_connect_error();
        exit;
    }
    mysqli_set_charset($conexion, 'utf8mb4');

    return $conexion;
}