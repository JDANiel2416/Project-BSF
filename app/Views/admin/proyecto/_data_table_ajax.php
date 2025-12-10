<div class="data-table-wrapper">

    <div id="bulk-actions-bar" class="bulk-actions">
        <div style="display:flex; align-items:center; gap:15px;">
            <span style="font-weight:600; color:#1976d2;"><span id="selected-count">0</span> seleccionados</span>
            <button id="btn-bulk-deselect"
                style="background:none; border:none; cursor:pointer; color:#1976d2; text-decoration:underline; font-size:12px;">Deseleccionar
                todo</button>
        </div>

        <div style="display:flex; gap:10px;">
            <div class="dropdown" style="display:inline-block;">
                <button class="btn-secondary dropdown-toggle"
                    style="background:#fff; border-color:#90caf9; color:#1565c0;">
                    Cambiar estado <i class="fas fa-chevron-down"></i>
                </button>
                <div class="dropdown-menu hidden"
                    style="position:absolute; background:#fff; border:1px solid #ccc; border-radius:4px; box-shadow:0 2px 5px rgba(0,0,0,0.1); z-index:100; min-width:150px;">
                    <a href="#" class="dropdown-item bulk-status-opt" data-status="approved"
                        style="display:block; padding:8px 15px; text-decoration:none; color:#333;">Aprobado</a>
                    <a href="#" class="dropdown-item bulk-status-opt" data-status="pending_review"
                        style="display:block; padding:8px 15px; text-decoration:none; color:#333;">En espera</a>
                    <a href="#" class="dropdown-item bulk-status-opt" data-status="rejected"
                        style="display:block; padding:8px 15px; text-decoration:none; color:#333;">No aprobado</a>
                </div>
            </div>

            <button id="btn-bulk-delete" class="btn-danger"
                style="background:#ffebee; color:#d32f2f; border:1px solid #ffcdd2;">
                <i class="fas fa-trash"></i> Eliminar
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table" id="submissions-table"
            style="width:100%; white-space: nowrap; font-size: 13px; border-collapse: separate; border-spacing: 0;">
            <thead>
                <tr style="background: #f8f9fa; position: sticky; top: 0; z-index: 20;">

                    <th
                        style="width: 90px; left:0; position:sticky; z-index:21; border-right:1px solid #ddd; background:#f4f6f8;">
                        <div class="th-header-flex" style="justify-content:center;">
                            <input type="checkbox" id="check-all-rows" style="cursor:pointer;">
                            <span style="margin-left:5px;">Acciones</span>
                        </div>
                    </th>

                    <?php
                    $sysCols = [
                        ['key' => '_id', 'label' => 'ID', 'type' => 'number'],
                        ['key' => '_validation_status', 'label' => 'Validación', 'type' => 'string'],
                        ['key' => '_submission_time', 'label' => 'Fecha Envío', 'type' => 'date'],
                        ['key' => 'start', 'label' => 'Start', 'type' => 'date'],
                        ['key' => 'end', 'label' => 'End', 'type' => 'date']
                    ];

                    foreach ($sysCols as $col): ?>
                        <th data-key="<?php echo $col['key']; ?>" data-type="<?php echo $col['type']; ?>">
                            <div class="th-header-flex">
                                <span class="th-label"><?php echo $col['label']; ?></span>
                                <button class="btn-sort-menu"><i class="fas fa-caret-down"></i></button>
                            </div>
                            <div class="th-filter-box">
                                <?php if ($col['key'] === '_validation_status'): ?>
                                    <select class="col-filter">
                                        <option value="">Todos</option>
                                        <option value="approved">Aprobado</option>
                                        <option value="pending_review">En espera</option>
                                        <option value="rejected">No aprobado</option>
                                    </select>
                                <?php else: ?>
                                    <input type="text" class="col-filter" placeholder="Buscar">
                                <?php endif; ?>
                            </div>
                        </th>
                    <?php endforeach; ?>

                    <?php foreach ($questions as $q): ?>
                        <?php
                        $sortType = ($q['type'] === 'number') ? 'number' : (($q['type'] === 'date') ? 'date' : 'string');
                        $icon = 'fa-font';
                        if ($q['type'] == 'number')
                            $icon = 'fa-hashtag';
                        if ($q['type'] == 'date')
                            $icon = 'fa-calendar';
                        if ($q['type'] == 'select')
                            $icon = 'fa-list-ul';
                        if ($q['type'] == 'photo')
                            $icon = 'fa-image';
                        ?>
                        <th style="border-left: 2px solid #eee;" data-key="q_<?php echo $q['id']; ?>"
                            data-type="<?php echo $sortType; ?>">
                            <div class="th-header-flex">
                                <span class="th-label" style="color:#6D4C7F;">
                                    <i class="fas <?php echo $icon; ?>" style="font-size:10px; opacity:0.6;"></i>
                                    <?php echo htmlspecialchars($q['text']); ?>
                                </span>
                                <?php if ($q['type'] !== 'photo'): ?>
                                    <button class="btn-sort-menu"><i class="fas fa-caret-down"></i></button>
                                <?php endif; ?>
                            </div>

                            <?php if ($q['type'] === 'photo'): ?>
                                <div class="th-filter-box no-filter" style="height:28px;"></div>
                            <?php elseif ($q['type'] === 'select'): ?>
                                <div class="th-filter-box">
                                    <select class="col-filter">
                                        <option value="">Todos</option>
                                        <?php if (!empty($q['options'])): ?>
                                            <?php foreach ($q['options'] as $opt): ?>
                                                <option value="<?php echo htmlspecialchars($opt); ?>">
                                                    <?php echo htmlspecialchars($opt); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            <?php else: ?>
                                <div class="th-filter-box">
                                    <input type="text" class="col-filter" placeholder="Buscar...">
                                </div>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody id="table-body">
            </tbody>
        </table>
    </div>

    <div class="table-footer">
        <div style="width: 30%;"></div>
        <div class="pagination-controls">
            <button id="btn-page-prev" class="btn-page"><i class="fas fa-caret-left"></i> PREVIO</button>
            <div class="page-info">
                <span>Página</span>
                <div class="page-input-box">
                    <span id="page-current">1</span>
                    <div style="display:flex; flex-direction:column; margin-left:5px;">
                        <i class="fas fa-caret-up" style="font-size:10px; cursor:pointer;" onclick="changePage(1)"></i>
                        <i class="fas fa-caret-down" style="font-size:10px; cursor:pointer;"
                            onclick="changePage(-1)"></i>
                    </div>
                </div>
                <span>De <span id="page-total">1</span></span>
            </div>
            <button id="btn-page-next" class="btn-page">SIGUIENTE <i class="fas fa-caret-right"></i></button>
        </div>

        <div style="width: 30%; text-align: right;">
            <select id="rows-per-page" class="rows-per-page-select">
                <option value="10">10 filas</option>
                <option value="30" selected>30 filas</option>
                <option value="50">50 filas</option>
                <option value="100">100 filas</option>
                <option value="200">200 filas</option>
                <option value="500">500 filas</option>
            </select>
        </div>
    </div>

    <div id="column-sort-menu" class="sort-dropdown">
        <div class="sort-item" data-action="asc">
            <i class="fas fa-sort-amount-down-alt"></i> Ordenar A→Z (Asc)
        </div>
        <div class="sort-item" data-action="desc">
            <i class="fas fa-sort-amount-down"></i> Ordenar Z→A (Desc)
        </div>
    </div>

    <div id="modal-submission-detail" class="modal-overlay hidden">
        <div class="modal-content modal-detail-view" style="max-width: 850px !important; height: 95vh;">

            <div class="modal-header-clean">
                <div>
                    <h2 id="modal-detail-title" style="margin:0; font-size:18px; color:#333;">Detalles del Registro</h2>
                    <span id="modal-detail-subtitle" style="font-size:13px; color:#888;">ID: #---</span>
                </div>
                <button class="close-modal" style="font-size:24px; color:#aaa; transition:color 0.2s;">&times;</button>
            </div>

            <div class="modal-body-scrollable">
                <div id="meta-container"
                    style="background:#f8f9fa; padding:20px; border-radius:8px; margin-bottom:30px; display:flex; gap:30px; border:1px solid #e9ecef;">
                </div>

                <h3
                    style="font-size:14px; color:#6D4C7F; margin-bottom:20px; border-bottom:2px solid #f0f0f0; padding-bottom:10px;">
                    DATOS DEL FORMULARIO</h3>
                <div id="detail-container" class="info-grid">
                </div>
            </div>

            <div class="modal-footer-integrated">
                <button id="btn-prev-sub" class="nav-btn">
                    <i class="fas fa-arrow-left"></i> Anterior
                </button>

                <span id="modal-nav-counter" class="nav-counter-badge">1 de X</span>

                <button id="btn-next-sub" class="nav-btn">
                    Siguiente <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <div id="modal-edit-submission" class="modal-overlay hidden">
        <div class="modal-content modal-detail-view"
            style="max-width: 800px !important; height: auto; max-height: 90vh;">

            <div class="modal-header-clean">
                <div>
                    <h2 style="margin:0; font-size:18px; color:#333;">Editar Datos del Registro</h2>
                    <span style="font-size:12px; color:#888;">Modifique los valores con precaución.</span>
                </div>
                <button class="close-modal" style="font-size:24px; color:#aaa;">&times;</button>
            </div>

            <div class="modal-body-scrollable">
                <form id="form-edit-submission">
                    <input type="hidden" id="edit-sub-id" name="submission_id">
                    <div id="edit-form-container" class="edit-form-grid">
                    </div>
                </form>
            </div>

            <div class="modal-footer-integrated" style="justify-content: flex-end; gap: 10px;">
                <button class="btn-secondary close-modal">Cancelar</button>
                <button id="btn-save-edit" class="btn-primary">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </div>
    </div>

    <div id="global-status-menu" class="sort-dropdown" style="width: 150px;">
        <div class="sort-item global-status-opt" data-status="approved">
            <i class="fas fa-check-circle" style="color:#2e7d32;"></i> Aprobado
        </div>
        <div class="sort-item global-status-opt" data-status="pending_review">
            <i class="fas fa-clock" style="color:#f57f17;"></i> En espera
        </div>
        <div class="sort-item global-status-opt" data-status="rejected">
            <i class="fas fa-times-circle" style="color:#c62828;"></i> No aprobado
        </div>
    </div>

</div>

<script>
    window.currentTableData = <?php echo json_encode($submissions); ?>;
    window.currentQuestions = <?php echo json_encode($questions); ?>;
    window.PUBLIC_URL = '<?php echo PUBLIC_URL; ?>';
</script>