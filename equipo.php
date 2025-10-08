<?php
$page_title = 'Nuestro Equipo | Banderas Sin Fronteras';
include 'includes/header.php';
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
?>
<div class="content-section">
    <div class="container">
        <div class="page-intro">
            <h1 class="scroll-animated fade-in-up">Conoce a Nuestro Equipo</h1>
            <p class="section-subtitle scroll-animated fade-in-up" style="transition-delay: 0.1s;">Las personas detrás
                de la misión: un equipo multidisciplinario y comprometido, unido por la pasión de servir y generar un
                impacto positivo en nuestra comunidad.</p>
        </div>
        <div class="equipo-grid">
            <?php foreach ($equipo as $index => $miembro): ?>
                <div class="miembro-card scroll-animated fade-in-up"
                    style="transition-delay: <?php echo ($index * 0.05) + 0.2; ?>s;">
                    <img src="img/user.png" alt="Foto de <?php echo htmlspecialchars($miembro['nombre']); ?>">
                    <h4><?php echo htmlspecialchars($miembro['nombre']); ?></h4>
                    <p><?php echo htmlspecialchars($miembro['cargo']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>