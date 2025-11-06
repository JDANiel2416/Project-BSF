<?php include 'layout/header.php' ?>

<div class="dashboard-container sidebar-container"> 
    <!-- BARRA LATERAL -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <a href="<?php echo ADMIN_URL; ?>/dashboard">
                <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="Logo BSF Completo" class="logo-full">
                <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="Logo BSF Icono" class="logo-icon">
            </a>
        </div>
        <div class="sidebar-content">
            <div class="sidebar-header">
                <a href="#" class="btn-nuevo">
                    <i class="fas fa-plus"></i>
                    <span>NUEVO</span>
                </a>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="#"><i class="fas fa-tasks"></i><span>Activos</span></a>
                    </li>
                    <li>
                        <a href="#"><i class="fas fa-archive"></i><span>Archivados</span></a>
                    </li>
                </ul>
            </nav>
        </div>
        <div class="sidebar-footer">
            <button id="toggle-sidebar-btn" title="Retraer/Expandir barra lateral">
                <i class="fas fa-chevron-left icon-collapse"></i>
                <i class="fas fa-chevron-right icon-expand"></i>
            </button>
        </div>
    </aside>


    <!-- CONTENIDO PRINCIPAL -->
    <main class="main-content">
        <header class="main-header">
            <div class="header-left">
                <h1>Mis proyectos</h1>
            </div>
            <div class="header-center">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Buscar...">
                </div>
            </div>
            <div class="header-right">
                <a href="<?php echo ADMIN_URL; ?>/logout" class="btn-logout">Cerrar Sesión</a>
            </div>
        </header>

        <div class="table-container">
            <table class="data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox"></th>
                        <th>Nombre del proyecto</th>
                        <th>Estado</th>
                        <th>Propietario</th>
                        <th>Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($proyectos)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">No hay proyectos para mostrar.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($proyectos as $proyecto): ?>
                            <tr>
                                <td><input type="checkbox"></td>
                                <td><a href="#"><?php echo htmlspecialchars($proyecto['nombre']); ?></a></td>
                                <td>
                                    <span class="status status-<?php echo strtolower($proyecto['estado']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($proyecto['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($proyecto['propietario'] ?? 'N/A'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($proyecto['fecha_modificacion'])); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<div id="modal-nuevo-proyecto" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear proyecto: Seleccionar una fuente</h2>
            <button class="close-modal" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <p>Seleccionar una de las siguientes opciones para continuar.</p>
            <div class="modal-options-grid">
                <!-- Opción 1: Crear desde borrador -->
                <a href="#" id="crear-borrador-btn" class="option-card">
                    <div class="option-card-icon">
                        <i class="fas fa-pencil-alt"></i>
                    </div>
                    <div class="option-card-text">Crear Formulario</div>
                </a>

                <!-- Opción 3: Cargar XLSForm -->
                <a href="#" class="option-card">
                    <div class="option-card-icon">
                        <i class="fas fa-upload"></i>z
                    </div>
                    <div class="option-card-text">Cargar un XLSForm</div>
                </a>

                <!-- Opción 4: Importar desde URL -->
                <a href="#" class="option-card">
                    <div class="option-card-icon">
                        <i class="fas fa-link"></i>
                    </div>
                    <div class="option-card-text">Importar un XLSForm a través de URL</div>
                </a>
            </div>
        </div>
    </div>
</div>

<div id="modal-detalles-proyecto" class="modal-overlay hidden">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Crear proyecto: Detalles del proyecto</h2>
            <button class="close-modal" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <form action="#" method="POST">
                <div class="form-group-modal">
                    <label for="proyecto-nombre">Nombre del proyecto (obligatorio)</label>
                    <input type="text" id="proyecto-nombre" class="input-modal" placeholder="Ingresa el título del proyecto aquí">
                </div>
                <div class="form-group-modal">
                    <label for="proyecto-descripcion">Descripción</label>
                    <textarea id="proyecto-descripcion" class="textarea-modal" placeholder="Ingresa una breve descripción aquí"></textarea>
                </div>
                <div class="form-grid">
                    <div class="form-group-modal">
                        <label for="proyecto-sector">Sector (obligatorio)</label>
                        <select id="proyecto-sector" class="select-modal">
                            <option value="" disabled selected>Seleccionar...</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="form-group-modal">
                        <label for="proyecto-pais">País (obligatorio)</label>
                        <select id="proyecto-pais" class="select-modal">
                            <option value="" disabled selected>Seleccionar...</option>
                            <option value="peru">Perú</option>
                            <option value="venezuela">Venezuela</option>
                            <option value="colombia">Colombia</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button id="btn-regresar" type="button" class="btn-modal-secondary">Regresar</button>
            <button id="btn-crear-proyecto" type="submit" class="btn-modal-primary">Crear proyecto</button>
        </div>
    </div>
</div>

<?php include 'layout/footer.php';  ?>