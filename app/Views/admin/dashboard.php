<?php include PROJECT_ROOT . '/app/Views/admin/layout/header.php'; ?>

<div class="dashboard-container sidebar-container" id="main-wrapper">
    
    <aside class="sidebar" id="sidebar">
        <button id="toggle-sidebar-btn" title="Alternar menú">
            <i class="fas fa-chevron-left"></i>
        </button>

        <div class="sidebar-brand">
            <a href="<?php echo ADMIN_URL; ?>/dashboard">
                <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="BSF" class="logo-full">
                <img src="<?php echo PUBLIC_URL; ?>/img/logo-bsf-nigth.png" alt="BSF" class="logo-icon">
            </a>
        </div>

        <div class="sidebar-content">
            <button id="btn-open-create-modal" class="btn-nuevo">
                <i class="fas fa-plus"></i>
                <span>NUEVO</span>
            </button>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active" id="nav-item-active">
                        <a href="#" data-action="load-active"><i class="fas fa-list-ul"></i><span>Activos</span></a>
                    </li>
                    <li id="nav-item-archived">
                        <a href="#" data-action="load-archived"><i class="fas fa-archive"></i><span>Archivados</span></a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    <main class="main-content" id="main-content-area">
        
        <header class="main-header">
            <div class="header-left">
                <h1 id="header-title">Mis proyectos</h1>
            </div>
            
            <div class="header-center">
                <div class="search-bar" id="header-search-container">
                    <i class="fas fa-search"></i>
                    <input type="text" id="global-search" placeholder="Buscar proyecto por nombre..." aria-label="Buscar proyectos">
                </div>
            </div>
            
            <div class="header-right">
                <div class="user-profile-link">
                    <span>Admin</span>
                </div>
                <a href="<?php echo ADMIN_URL; ?>/logout" class="btn-logout"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div id="dynamic-view-container" class="view-container">
            
            <div id="view-projects-list" class="active-view">
                <div class="table-container">
                    <table class="data-table" id="projects-table">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input type="checkbox" id="check-all"></th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Sector</th>
                                <th>País</th>
                                <th>Fecha</th>
                                <th style="width: 50px;"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($proyectos)): ?>
                                <tr><td colspan="7" style="text-align: center; padding: 2rem; color:#999;">No hay proyectos.</td></tr>
                            <?php else: ?>
                                <?php foreach ($proyectos as $p): ?>
                                <tr data-id="<?php echo $p['id']; ?>" class="project-row">
                                    <td><input type="checkbox" class="row-check"></td>
                                    <td>
                                        <span class="project-name-link"><?php echo htmlspecialchars($p['nombre']); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                            $statusClass = strtolower($p['estado']);
                                            $statusLabel = ucfirst($p['estado'] == 'draft' ? 'borrador' : $p['estado']);
                                        ?>
                                        <span class="status status-<?php echo $statusClass == 'draft' ? 'draft' : 'active'; ?>">
                                            <?php echo $statusLabel; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['sector'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($p['pais'] ?? '-'); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($p['fecha_modificacion'])); ?></td>
                                    <td class="actions-cell">
                                        <div class="dropdown">
                                            <button class="btn-icon-action"><i class="fas fa-ellipsis-v"></i></button>
                                            <div class="dropdown-content">
                                                <button><i class="fas fa-pen"></i> Editar</button>
                                                <button><i class="fas fa-copy"></i> Duplicar</button>
                                                <button class="btn-delete-action"><i class="fas fa-trash"></i> Eliminar</button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="ajax-content-slot"></div>
        </div>
    </main>
</div>

<?php include PROJECT_ROOT . '/app/Views/admin/proyecto/_create_modal.php'; ?>
<?php include PROJECT_ROOT . '/app/Views/admin/layout/footer.php'; ?>