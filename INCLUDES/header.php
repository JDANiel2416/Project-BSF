<?php 
    $current_page = basename($_SERVER['SCRIPT_NAME']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Banderas Sin Fronteras'; ?></title>
    <meta name="description" content="Asociación Civil Banderas Sin Fronteras, dedicada al apoyo de familias migrantes, refugiadas y locales en situación de vulnerabilidad.">
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<header class="main-header">
    <div class="container">
        <a href="index.php" class="logo"><img src="img/logo-bsf.png" alt="Logo Banderas Sin Fronteras"></a>
        <nav class="main-nav">
            <ul>
                <li class="<?php echo ($current_page == 'index.php') ? 'active' : ''; ?>"><a href="index.php">Inicio</a></li>
                <li class="<?php echo ($current_page == 'quienes-somos.php') ? 'active' : ''; ?>"><a href="quienes-somos.php">Quiénes Somos</a></li>
                <li class="<?php echo ($current_page == 'proyectos.php') ? 'active' : ''; ?>"><a href="proyectos.php">Proyectos</a></li>
                <li class="<?php echo ($current_page == 'voluntariado.php') ? 'active' : ''; ?>"><a href="voluntariado.php">Voluntariado</a></li>
                <li class="<?php echo ($current_page == 'equipo.php') ? 'active' : ''; ?>"><a href="equipo.php">Equipo</a></li>
                <li class="<?php echo ($current_page == 'contacto.php') ? 'active' : ''; ?>"><a href="contacto.php">Contacto</a></li>
            </ul>
        </nav>
        <button class="nav-toggle" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
    </div>
</header>
<main>