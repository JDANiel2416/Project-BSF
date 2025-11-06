<?php include PROJECT_ROOT . '/app/Views/admin/layout/header.php'; ?>

<div class="project-summary-container">
    <header class="project-summary-header">
        <h1><?php echo htmlspecialchars($proyecto['title']); ?></h1>
        <div class="project-summary-actions">
            <button class="btn-secondary">Previsualizar</button>
            <button class="btn-primary">Guardar</button>
        </div>
    </header>

    <nav class="project-summary-tabs">
        <a href="#" class="active">Resumen</a>
        <a href="#">Formulario</a>
        <a href="#">Datos</a>
        <a href="#">Configuración</a>
    </nav>

    <div class="project-summary-body">
        <main class="project-summary-main">
            <div class="summary-card">
                <h2>Información sobre el proyecto</h2>
                <p class="description"><?php echo htmlspecialchars($proyecto['description'] ?: '-'); ?></p>
                <div class="info-grid">
                    <div><span>Estado</span><span class="status status-<?php echo strtolower($proyecto['status']); ?>"><?php echo ucfirst($proyecto['status']); ?></span></div>
                    <div><span>Preguntas</span><?php echo htmlspecialchars($proyecto['question_count'] ?? 0); ?></div>
                    <div><span>Propietario</span><?php echo htmlspecialchars($proyecto['propietario']); ?></div>
                    <div><span>Last edited</span>yo</div>
                    <div><span>Última modificación</span><?php echo date('d/m/Y \a \l\a\s H:i', strtotime($proyecto['created_at'])); ?></div>
                    <div><span>Última implementación</span>-</div>
                    <div><span>Sector</span><?php echo htmlspecialchars($proyecto['sector']); ?></div>
                    <div><span>País</span><?php echo htmlspecialchars($proyecto['country']); ?></div>
                </div>
            </div>

            <div class="summary-card">
                <h2>Envíos</h2>
                <div class="date-tabs">
                    <a href="#" class="active">Últimos 7 días</a>
                    <a href="#">Últimos 31 días</a>
                    <a href="#">Últimos 3 meses</a>
                    <a href="#">Últimos 12 meses</a>
                </div>
                <div class="no-data-placeholder">
                    <p>No hay datos de gráficos disponibles para el período actual.</p>
                </div>
                <div class="stats-footer">
                    <div><strong>0</strong><span>30 de oct. de 2025 – Hoy</span></div>
                    <div><strong>0</strong><span>Total</span></div>
                </div>
            </div>
        </main>

        <aside class="project-summary-sidebar">
            <div class="direct-links-card">
                <h3>Enlaces directos</h3>
                <ul>
                    <li><a href="#"><span><i class="fas fa-paste"></i> Recolectar datos</span> <i class="fas fa-chevron-right"></i></a></li>
                    <li><a href="#"><span><i class="fas fa-share-alt"></i> Compartir proyecto</span> <i class="fas fa-chevron-right"></i></a></li>
                    <li><a href="<?php echo ADMIN_URL . '/formularios/constructor/' . $proyecto['id']; ?>"><span><i class="fas fa-edit"></i> Editar formulario</span> <i class="fas fa-chevron-right"></i></a></li>
                    <li><a href="#"><span><i class="fas fa-eye"></i> Previsualizar el formulario</span> <i class="fas fa-chevron-right"></i></a></li>
                </ul>
            </div>
        </aside>
    </div>
</div>

<?php include PROJECT_ROOT . '/app/Views/admin/layout/footer.php'; ?>