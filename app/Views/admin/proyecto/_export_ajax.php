<div class="export-container fade-in">
    
    <form id="form-export-data">
        <input type="hidden" name="project_id" value="<?php echo $params[0]; ?>">
        
        <div class="export-layout-grid">
            
            <div class="export-options-col">
                <div class="form-group">
                    <label class="export-label">Formato de archivo</label>
                    <select name="format" class="form-control">
                        <option value="xlsx">Excel (.xlsx)</option>
                        <option value="csv">CSV (Separado por comas)</option>
                    </select>
                </div>

                <div class="form-group" style="margin-top:20px;">
                    <label class="export-section-title">Opciones avanzadas</label>
                    <div style="margin-top:10px;">
                        <label class="export-check-row">
                            <input type="checkbox" id="check-date-range"> 
                            <span>Filtrar por rango de fechas</span>
                        </label>
                        <div id="date-range-inputs" class="date-range-box hidden">
                            <input type="date" name="start_date" class="form-control compact">
                            <span style="color:#666;">hasta</span>
                            <input type="date" name="end_date" class="form-control compact">
                        </div>
                    </div>
                </div>
            </div>

            <div class="export-checklist-col">
                <div class="checklist-header">
                    <label class="export-label">Seleccionar columnas a exportar</label>
                    <div class="checklist-actions">
                        <button type="button" id="btn-sel-all">Todas</button>
                        <button type="button" id="btn-sel-none">Ninguna</button>
                    </div>
                </div>

                <div class="checklist-scroll-area">
                    <div class="checklist-group-title">Metadatos del Sistema</div>
                    
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="_id" checked> ID Interno
                    </label>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="_submission_time" checked> Fecha de Envío
                    </label>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="_submitted_by" checked> Usuario
                    </label>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="_validation_status" checked> Estado Validación
                    </label>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="start"> Start (Hora inicio)
                    </label>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="end"> End (Hora fin)
                    </label>

                    <div class="checklist-group-title">Preguntas del Formulario</div>
                    
                    <?php foreach($allQuestions as $q): ?>
                    <label class="export-check-row">
                        <input type="checkbox" name="columns[]" value="<?php echo $q['id']; ?>" checked>
                        <?php echo htmlspecialchars($q['text']); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="export-footer-bar">
            <span style="font-size:12px; color:#666;">
                <i class="fas fa-info-circle"></i> Se generará un archivo con las columnas seleccionadas en orden.
            </span>
            <button type="submit" class="btn-primary btn-lg">
                <i class="fas fa-download"></i> Generar Exportación
            </button>
        </div>
    </form>

    <div class="export-history-section">
        <h3>Historial de Descargas</h3>
        <table class="exports-history-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Filas</th>
                    <th style="text-align:right">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($history)): ?>
                    <tr><td colspan="4" class="empty-cell">No hay exportaciones recientes.</td></tr>
                <?php else: ?>
                    <?php foreach($history as $h): ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i', strtotime($h['created_at'])); ?></td>
                        <td><span class="badge-type"><?php echo strtoupper($h['file_type']); ?></span></td>
                        <td><?php echo $h['row_count']; ?></td>
                        <td style="text-align:right">
                            <a href="<?php echo PUBLIC_URL . '/' . $h['file_path']; ?>" class="btn-download-link" download>
                                <i class="fas fa-file-download"></i> Descargar
                            </a>
                            <button class="btn-icon-delete delete-export" data-id="<?php echo $h['id']; ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        (function() {
            // Checkboxes
            const checkboxes = document.querySelectorAll('.checklist-scroll-area input[type="checkbox"]');
            const btnAll = document.getElementById('btn-sel-all');
            const btnNone = document.getElementById('btn-sel-none');
            
            if(btnAll) btnAll.onclick = () => checkboxes.forEach(c => c.checked = true);
            if(btnNone) btnNone.onclick = () => checkboxes.forEach(c => c.checked = false);

            // Fechas
            const dateCheck = document.getElementById('check-date-range');
            const dateBox = document.getElementById('date-range-inputs');
            if(dateCheck) {
                dateCheck.onchange = (e) => {
                    dateBox.classList.toggle('hidden', !e.target.checked);
                };
            }

            // Submit
            const form = document.getElementById('form-export-data');
            if(form) {
                form.onsubmit = function(e) {
                    e.preventDefault();
                    if(document.querySelectorAll('input[name="columns[]"]:checked').length === 0) {
                        alert("Selecciona al menos una columna."); return;
                    }

                    const btn = form.querySelector('button[type="submit"]');
                    const oldText = btn.innerHTML;
                    btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

                    fetch(`${ADMIN_URL}/proyectos/processExport`, {
                        method: 'POST',
                        body: new FormData(form)
                    })
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) {
                            // Recargar vista para ver historial
                            const activeTab = document.querySelector('.data-nav-item.active');
                            if(activeTab && activeTab.textContent.trim() === 'Descarga') activeTab.click();
                        } else {
                            alert('Error: ' + d.message);
                        }
                    })
                    .catch(e => console.error(e))
                    .finally(() => { btn.disabled = false; btn.innerHTML = oldText; });
                };
            }

            // Eliminar
            document.querySelectorAll('.delete-export').forEach(b => {
                b.onclick = function() {
                    if(!confirm('¿Borrar archivo?')) return;
                    fetch(`${ADMIN_URL}/proyectos/deleteExport/${this.dataset.id}`)
                    .then(r=>r.json())
                    .then(d => { if(d.success) this.closest('tr').remove(); });
                }
            });
        })();
    </script>
</div>