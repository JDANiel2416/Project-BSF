<?php
$p = $proyecto ?? [];
$id = $p['id'] ?? 0;
$qJson = $questionsJson ?? '[]';

$vCount = $versionCount ?? 0;
$pubUrl = $publicFormUrl ?? '#';
$isActive = ($p['status'] === 'active');
$hasPending = $hasPendingChanges ?? false;

// 1. Si hay versiones, usa esa fecha, si no, la creación del proyecto
$lastMod = $p['last_modified_at'] ? date('d M Y H:i', strtotime($p['last_modified_at'])) : date('d M Y H:i', strtotime($p['created_at']));

// 2. Si hay fecha de publicación
$lastDep = $p['last_deployed_at'] ? date('d M Y H:i', strtotime($p['last_deployed_at'])) : '-';

// 3. Si hay fecha de envío
$lastSub = $p['last_submission_at'] ? date('d M Y H:i', strtotime($p['last_submission_at'])) : '-';

$isArchived = ($p['status'] === 'archived');
$txtArchiveTitle = $isArchived ? 'Desarchivar Proyecto' : 'Archivar Proyecto';
$txtArchiveDesc = $isArchived ? 'El proyecto volverá a la lista de activos.' : 'El proyecto se ocultará de la lista principal.';
$txtBtnArchive = $isArchived ? 'Desarchivar' : 'Archivar';
$actionArchive = $isArchived ? 'restore' : 'archive';
?>
<div class="project-detail-wrapper fade-in" data-project-name="<?php echo htmlspecialchars($p['title']); ?>"
    data-questions='<?php echo $qJson; ?>'>

    <div class="detail-tabs">
        <button class="tab-link active" data-tab="summary">RESUMEN</button>
        <button class="tab-link" data-tab="formulario-view">FORMULARIO</button>
        <button class="tab-link" data-tab="data">DATOS</button>
        <button class="tab-link" data-tab="settings">CONFIGURACIÓN</button>
    </div>

    <div class="tab-content-container">

        <div id="tab-summary" class="tab-pane active">
            <div class="summary-layout">

                <div class="summary-main">
                    <div class="card-panel">
                        <div class="card-header-simple">Información sobre el proyecto</div>

                        <div class="project-info-box">
                            <div class="info-description">
                                <label>Descripción</label>
                                <p><?php echo htmlspecialchars($p['description'] ?: '-'); ?></p>
                            </div>

                            <div class="info-stats-grid">
                                <div class="stat-item">
                                    <label>Estado</label>
                                    <?php if ($isActive && $hasPending): ?>
                                        <span class="status-badge-blue" style="background:#fff3cd; color:#856404;"><i
                                                class="fas fa-exclamation-circle"></i> Cambios pendientes</span>
                                    <?php else: ?>
                                        <span class="status-badge-blue"><i class="fas fa-tag"></i>
                                            <?php echo ucfirst($p['status'] == 'draft' ? 'Borrador' : $p['status']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="stat-item">
                                    <label>Preguntas</label>
                                    <span
                                        class="value"><?php echo htmlspecialchars($p['question_count'] ?? 0); ?></span>
                                </div>
                                <div class="stat-item">
                                    <label>Propietario</label>
                                    <span class="value"><?php echo htmlspecialchars($p['propietario']); ?></span>
                                </div>
                                <div class="stat-item">
                                    <label>Last edited</label>
                                    <span class="value"><?php echo htmlspecialchars($p['propietario']); ?></span>
                                </div>
                            </div>

                            <div class="info-dates-grid">
                                <div class="stat-item">
                                    <label>Última modificación</label>
                                    <span class="value" style="font-size:13px;"><?php echo $lastMod; ?></span>
                                </div>
                                <div class="stat-item">
                                    <label>Última implementación</label>
                                    <span class="value" style="font-size:13px;"><?php echo $lastDep; ?></span>
                                </div>
                                <div class="stat-item">
                                    <label>Último envío</label>
                                    <span class="value"
                                        style="font-size:13px; color: <?php echo ($lastSub !== '-') ? '#28a745' : '#333'; ?>; font-weight:600;">
                                        <?php echo $lastSub; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="info-meta-grid">
                                <div class="stat-item">
                                    <label>Sector</label>
                                    <span class="value"><?php echo htmlspecialchars($p['sector']); ?></span>
                                </div>
                                <div class="stat-item">
                                    <label>País</label>
                                    <span class="value"><?php echo htmlspecialchars($p['country']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-panel">
                        <div class="card-header-simple">Envíos</div>
                        <div class="stats-chart-container">
                            <div class="chart-tabs">
                                <div class="chart-tab active">Últimos 7 días</div>
                                <div class="chart-tab">Últimos 31 días</div>
                                <div class="chart-tab">Últimos 3 meses</div>
                                <div class="chart-tab">Últimos 12 meses</div>
                            </div>

                            <div class="chart-placeholder">
                                <div class="bar-mock" style="height: 0%;"></div>
                                <div class="bar-mock" style="height: 0%;"></div>
                                <div class="bar-mock" style="height: 0%;"></div>
                                <div class="bar-mock" style="height: 20%;"></div>
                            </div>

                            <div class="chart-footer">
                                <div class="chart-stat-box">
                                    <strong class="big-num">1</strong>
                                    <span><?php echo date('d \d\e M. \d\e Y'); ?> – Hoy</span>
                                </div>
                                <div class="chart-stat-box">
                                    <strong class="big-num">1</strong>
                                    <span>Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="summary-sidebar">
                    <div class="card-panel">
                        <div class="card-header-simple">Enlaces directos</div>
                        <ul class="direct-links-list">
                            <li><a href="#"
                                    onclick="document.querySelector('.tab-link[data-tab=\'formulario-view\']').click(); return false;"><span
                                        style="display:flex;align-items:center;"><i
                                            class="fas fa-clipboard-list icon-left"></i> Recolectar datos</span><i
                                        class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#"><span style="display:flex;align-items:center;"><i
                                            class="fas fa-user-plus icon-left"></i> Compartir proyecto</span> <i
                                        class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#" class="action-link-builder" data-id="<?php echo $id; ?>"><span
                                        style="display:flex;align-items:center;"><i class="fas fa-pen icon-left"></i>
                                        Editar formulario</span> <i class="fas fa-chevron-right"></i></a></li>
                            <li><a href="#" class="btn-preview-action"><span style="display:flex;align-items:center;"><i
                                            class="fas fa-eye icon-left"></i> Previsualizar el formulario</span> <i
                                        class="fas fa-chevron-right"></i></a></li>
                        </ul>
                    </div>

                    <div class="card-panel">
                        <div class="card-header-simple">Datos</div>
                        <ul class="direct-links-list">
                            <li>
                                <a href="#" class="btn-jump-to-data" data-target="Tabla">
                                    <div><i class="fas fa-table icon-left"></i> Tabla de datos</div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="btn-jump-to-data" data-target="Informes">
                                    <div><i class="fas fa-chart-pie icon-left"></i> Informes</div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="btn-jump-to-data" data-target="Galería">
                                    <div><i class="fas fa-images icon-left"></i> Galería</div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="btn-jump-to-data" data-target="Descarga">
                                    <div><i class="fas fa-file-download icon-left"></i> Descargar datos</div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="btn-jump-to-data" data-target="Mapa">
                                    <div><i class="fas fa-map-marked-alt icon-left"></i> Mapa</div>
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>


        <div id="tab-formulario-view" class="tab-pane">
            <div class="form-view-container">

                <?php if (!$isActive): // CASO 1: PROYECTO EN BORRADOR (Nunca implementado) ?>
                    <div class="version-header">
                        <h3>Versión borrador</h3>
                        <div class="version-actions">
                            <i class="fas fa-pen"></i> <i class="fas fa-eye"></i> <i class="fas fa-history"></i> <i
                                class="fas fa-ellipsis-h"></i>
                        </div>
                    </div>

                    <div class="alert-warning-box">
                        <i class="fas fa-exclamation-circle"></i>
                        <span>Si deseas publicar estos cambios, debes implementar este formulario.</span>
                    </div>

                    <div class="version-status-bar">
                        <span class="version-info">
                            <strong>v<?php echo $vCount; ?></strong> (no implementado) Última modificación:
                            <?php echo $lastMod; ?> - <?php echo $p['question_count']; ?> preguntas
                        </span>
                        <button id="btn-deploy-action" class="btn-deploy" data-id="<?php echo $id; ?>"
                            data-mode="initial">IMPLEMENTAR</button>
                    </div>

                <?php elseif ($isActive && $hasPending): // CASO 2: IMPLEMENTADO PERO CON CAMBIOS PENDIENTES ?>
                    <div class="version-header">
                        <h3>Cambios sin publicar</h3>
                        <div class="version-actions">
                            <i class="fas fa-pen"></i> <i class="fas fa-eye"></i> <i class="fas fa-history"></i> <i
                                class="fas fa-ellipsis-h"></i>
                        </div>
                    </div>

                    <div class="alert-warning-box" style="border-color:#ffc107; background-color:#fff3cd;">
                        <i class="fas fa-exclamation-triangle" style="color:#ffc107;"></i>
                        <span>Tienes cambios guardados que aún no son visibles para el público.</span>
                    </div>

                    <div class="version-status-bar">
                        <span class="version-info">
                            <strong>v<?php echo $vCount; ?></strong> (Borrador pendiente) Última modificación:
                            <?php echo $lastMod; ?>
                        </span>
                        <button id="btn-deploy-action" class="btn-deploy" data-id="<?php echo $id; ?>" data-mode="redeploy"
                            style="background-color:#ff9800;">IMPLEMENTAR DE NUEVO</button>
                    </div>

                    <div class="data-collection-section" style="opacity:0.5; pointer-events:none; filter: grayscale(1);">
                        <h4>Recolectar datos (Versión anterior activa)</h4>
                        <div class="collection-link-box">
                            <div class="link-display">
                                <span class="link-type"><?php echo $pubUrl; ?></span>
                            </div>
                        </div>
                    </div>

                <?php else: // CASO 3: IMPLEMENTADO Y PUBLICADO (Sin cambios pendientes) ?>
                    <div class="version-header">
                        <h3>Versión actual (Pública)</h3>
                        <div class="version-actions">
                            <i class="fas fa-pen"></i> <i class="fas fa-eye"></i> <i class="fas fa-history"></i> <i
                                class="fas fa-ellipsis-h"></i>
                        </div>
                    </div>

                    <div class="version-status-bar">
                        <span class="version-info">
                            <strong>v<?php echo $vCount; ?></strong> Publicado - Última modificación:
                            <?php echo $lastMod; ?>
                        </span>
                        <button class="btn-deploy" disabled style="opacity:0.6; cursor:default;">IMPLEMENTADO</button>
                    </div>

                    <div class="data-collection-section">
                        <h4>Recolectar datos</h4>

                        <div class="collection-link-box">
                            <div class="link-display">
                                <span class="link-type" style="word-break: break-all;"><?php echo $pubUrl; ?></span>
                            </div>
                            <div class="link-actions">
                                <input type="text" id="hidden-public-link" value="<?php echo $pubUrl; ?>"
                                    style="position:absolute; left:-9999px;">
                                <button class="btn-link-action copy-btn">Copiar</button>
                                <a href="<?php echo $pubUrl; ?>" target="_blank" class="btn-link-action open-btn">Abrir</a>
                            </div>
                        </div>
                        <p class="link-help-text">Esto permite envíos en línea y sin conexión, y es la mejor opción para
                            recolectar datos en el terreno.</p>
                    </div>
                <?php endif; ?>

                <div class="languages-section" style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                    <div style="display:flex; justify-content:space-between;">
                        <strong>Idiomas:</strong> Este proyecto aún no tiene idiomas definidos
                        <i class="fas fa-globe" style="color:#6D4C7F;"></i>
                    </div>
                </div>
            </div>
        </div>


        <div id="tab-data" class="tab-pane">
            <div class="data-tab-layout">
                <div class="data-sidebar">
                    <a class="data-nav-item active"><span>Tabla</span> <i class="fas fa-chevron-right"></i></a>
                    <a class="data-nav-item"><span>Informes</span> <i class="fas fa-chevron-right"></i></a>
                    <a class="data-nav-item"><span>Galería</span> <i class="fas fa-chevron-right"></i></a>
                    <a class="data-nav-item"><span>Descarga</span> <i class="fas fa-chevron-right"></i></a>
                    <a class="data-nav-item"><span>Mapa</span> <i class="fas fa-chevron-right"></i></a>
                </div>
                <div class="data-content-area">
                    <div class="data-placeholder">
                        <i class="fas fa-database"></i>
                        <p>No hay respuestas registradas aún.</p>
                    </div>
                </div>
            </div>
        </div>

        <div id="tab-settings" class="tab-pane">
            <div class="card-panel">
                <div class="card-header-simple">Ajustes del Proyecto</div>
                <form id="form-update-project" data-id="<?php echo $id; ?>" style="padding:20px;">
                    <div class="form-group">
                        <label>Nombre del Proyecto</label>
                        <input type="text" name="nombre" class="form-control"
                            value="<?php echo htmlspecialchars($p['title']); ?>">
                    </div>
                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control"
                            rows="3"><?php echo htmlspecialchars($p['description']); ?></textarea>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label>Sector</label>
                                <select name="sector" class="form-control">
                                    <option value="">Seleccionar...</option>
                                    <option value="Salud" <?php echo $p['sector'] == 'Salud' ? 'selected' : ''; ?>>Salud
                                    </option>
                                    <option value="Educación" <?php echo $p['sector'] == 'Educación' ? 'selected' : ''; ?>>Educación</option>
                                    <option value="Protección" <?php echo $p['sector'] == 'Protección' ? 'selected' : ''; ?>>Protección</option>
                                    <option value="Medio Ambiente" <?php echo $p['sector'] == 'Medio Ambiente' ? 'selected' : ''; ?>>Medio Ambiente</option>
                                    <option value="Otro" <?php echo $p['sector'] == 'Otro' ? 'selected' : ''; ?>>Otro
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label>País</label>
                                <select name="pais" class="form-control">
                                    <option value="">Seleccionar...</option>
                                    <option value="Perú" <?php echo $p['country'] == 'Perú' ? 'selected' : ''; ?>>Perú
                                    </option>
                                    <option value="Venezuela" <?php echo $p['country'] == 'Venezuela' ? 'selected' : ''; ?>>Venezuela</option>
                                    <option value="Colombia" <?php echo $p['country'] == 'Colombia' ? 'selected' : ''; ?>>
                                        Colombia</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                </form>
            </div>

            <div class="card-panel" style="border: 1px solid #ffcdcd;">
                <div class="card-header-simple" style="background:#fff5f5; color:#dc3545;">Zona de Peligro</div>
                <div style="padding:20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
                        <div>
                            <strong style="color:#333;"><?php echo $txtArchiveTitle; ?></strong>
                            <p style="margin:0; font-size:12px; color:#666;"><?php echo $txtArchiveDesc; ?></p>
                        </div>
                        <button class="btn-secondary" id="btn-archive-project" data-id="<?php echo $id; ?>"
                            data-action="<?php echo $actionArchive; ?>"><?php echo $txtBtnArchive; ?></button>
                    </div>
                    <hr style="border:0; border-top:1px solid #eee; margin:15px 0;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <strong style="color:#333;">Eliminar Proyecto</strong>
                            <p style="margin:0; font-size:12px; color:#666;">Esta acción es irreversible.</p>
                        </div>
                        <button class="btn-danger" id="btn-delete-project" data-id="<?php echo $id; ?>">Eliminar
                            Proyecto</button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>