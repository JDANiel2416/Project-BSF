<?php include PROJECT_ROOT . '/app/Views/admin/layout/header.php';  ?>
<div class="login-wrapper">
    <div class="login-box">
        <h2>Acceso de Administrador</h2>
        <p>Inicia sesión para gestionar el contenido.</p>
        
        <?php
            if (isset($_SESSION['error_message'])) {
                echo '<p class="error-message">' . $_SESSION['error_message'] . '</p>';
                unset($_SESSION['error_message']);
            }
        ?>

        <form action="<?php echo ADMIN_URL; ?>/login-handler" method="POST">
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-login">Iniciar Sesión</button>
        </form>
    </div>
</div>
<?php include PROJECT_ROOT . '/app/Views/admin/layout/footer.php';  ?>