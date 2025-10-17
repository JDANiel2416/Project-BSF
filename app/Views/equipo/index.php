<?php include '../app/Views/layout/header.php'; ?>
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
                    <img src="<?php echo BASE_URL; ?>/img/user.png" alt="Foto de <?php echo htmlspecialchars($miembro['nombre']); ?>">
                    <h4><?php echo htmlspecialchars($miembro['nombre']); ?></h4>
                    <p><?php echo htmlspecialchars($miembro['cargo']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php include '../app/Views/layout/footer.php'; ?>