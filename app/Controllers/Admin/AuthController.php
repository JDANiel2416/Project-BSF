<?php
namespace App\Controllers\Admin;

use App\Models\Admin\Usuario;

class AuthController {

    public function showLoginForm() {
        if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
            header('Location: ' . ADMIN_URL . '/dashboard');
            exit;
        }
        $page_title = 'Admin Login';
        include PROJECT_ROOT . '/app/Views/admin/login.php';
    }

    public function login() {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $usuarioModel = new Usuario();

        $usuario = $usuarioModel->findByEmail($email);

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user_id'] = $usuario['id'];
            $_SESSION['admin_email'] = $usuario['email'];
            $_SESSION['admin_full_name'] = $usuario['full_name'];

            header('Location: ' . ADMIN_URL . '/dashboard');
            exit;
        } else {
            $_SESSION['error_message'] = 'Correo o contraseña incorrectos.';
            header('Location: ' . ADMIN_URL . '/login');
            exit;
        }
    }

    public function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . ADMIN_URL . '/login');
        exit;
    }
}