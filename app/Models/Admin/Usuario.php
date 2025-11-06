<?php
namespace App\Models\Admin;

class Usuario {
    
    /**
     * @param string $email
     * @return array|null
     */
    public function findByEmail(string $email) {
        $db = conectarDB();

        $query = "SELECT id, username, password_hash, email, full_name, role FROM users WHERE email = ? AND is_active = 1 LIMIT 1";

        $stmt = mysqli_prepare($db, $query);

        mysqli_stmt_bind_param($stmt, 's', $email);

        mysqli_stmt_execute($stmt);

        $resultado = mysqli_stmt_get_result($stmt);

        $usuario = mysqli_fetch_assoc($resultado);
        
        mysqli_stmt_close($stmt);
        mysqli_close($db);

        return $usuario;
    }
}