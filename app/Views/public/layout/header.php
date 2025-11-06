<?php
    $current_path = trim(str_replace(BASE_PATH, '', $_SERVER['REQUEST_URI']), '/');
    if (empty($current_path)) $current_path = 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) : 'Banderas Sin Fronteras'; ?></title>
    <meta name="description" content="Asociación Civil Banderas Sin Fronteras, dedicada al apoyo de familias migrantes, refugiadas y locales en situación de vulnerabilidad.">
    <link rel="stylesheet" href="<?php echo PUBLIC_URL; ?>/css/style.css?v=1.3">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<header class="main-header">
    <div class="container">
        <a href="<?php echo BASE_URL; ?>/inicio" class="logo"><img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf.png" alt="Logo"></a>
        <nav class="main-nav">
            <ul>
                <li class="<?php echo ($current_path == 'inicio') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/inicio">Inicio</a></li>
                <li class="<?php echo ($current_path == 'quienes-somos') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/quienes-somos">Quiénes Somos</a></li>
                <li class="<?php echo ($current_path == 'proyectos') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/proyectos">Proyectos</a></li>
                <li class="<?php echo ($current_path == 'mapa') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/mapa">Ubicación</a></li>
                <li class="<?php echo ($current_path == 'voluntariado') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/voluntariado">Voluntariado</a></li>
                <li class="<?php echo ($current_path == 'equipo') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/equipo">Equipo</a></li>
                <li class="<?php echo ($current_path == 'contacto') ? 'active' : ''; ?>"><a href="<?php echo BASE_URL; ?>/contacto">Contacto</a></li>
            </ul>
        </nav>
        <button class="nav-toggle" aria-label="Abrir menú"><i class="fas fa-bars"></i></button>
    </div>
</header>
<main>