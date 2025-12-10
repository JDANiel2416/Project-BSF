<?php 
// Variables del controlador: $projectData, $currentReportName, etc.
$projTitle = $projectData['nombre'] ?? 'Proyecto';
$projDesc = $projectData['description'] ?? '';
?>
<div class="reports-container fade-in">
    
    <div class="report-tools-bar">
        <div class="tool-group">
            <select id="report-selector" class="form-control" style="width: 250px; display:inline-block;" onchange="loadReport(this.value)">
                <option value="">Informe predeterminado</option>
                <?php foreach ($customReports as $rep): ?>
                    <option value="<?php echo $rep['id']; ?>" <?php echo ($currentReportId == $rep['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($rep['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button class="btn-icon-secondary" title="Crear informe personalizado" onclick="openReportModal('create')">
                <i class="fas fa-plus"></i>
            </button>

            <?php if (!empty($currentReportId)): ?>
                <button class="btn-icon-secondary" title="Editar este informe" onclick="openReportModal('edit', <?php echo $currentReportId; ?>)">
                    <i class="fas fa-pen"></i>
                </button>
            <?php endif; ?>
        </div>

        <div class="tool-group">
            <button class="btn-secondary" title="Imprimir" onclick="printReport()"><i class="fas fa-print"></i> Imprimir</button>
        </div>
    </div>

    <div class="report-warning">
        <i class="fas fa-exclamation-circle" style="font-size: 18px;"></i>
        <span>Informe automatizado. Los gráficos se generan para preguntas de tipo Selección y Fecha.</span>
    </div>

    <div id="printable-report-area">
        
        <div class="report-print-header">
            <h1 style="margin:0 0 10px 0; color:#000; font-size:26px; text-transform:uppercase;">
                <?php echo htmlspecialchars($projTitle); ?>
            </h1>
            
            <?php if (!empty($projDesc)): ?>
                <p style="margin:0 0 15px 0; color:#444; font-size:14px; font-style:italic;">
                    <?php echo htmlspecialchars($projDesc); ?>
                </p>
            <?php endif; ?>

            <?php if ($currentReportName !== 'Informe predeterminado'): ?>
                <h3 style="margin:10px 0; color:#6D4C7F; font-size:18px; border-top:1px solid #ccc; display:inline-block; padding-top:5px;">
                    <?php echo htmlspecialchars($currentReportName); ?>
                </h3>
            <?php endif; ?>

            <p style="margin:15px 0 0; color:#666; font-size:12px;">Generado el <?php echo date('d/m/Y H:i'); ?></p>
        </div>

        <?php if (empty($stats)): ?>
            <div style="text-align:center; padding:40px; color:#999;">
                <p>No hay datos para mostrar en este informe.</p>
            </div>
        <?php else: ?>
            <?php foreach ($stats as $q): ?>
                <div class="report-card">
                    <div class="report-card-header">
                        <h3><?php echo htmlspecialchars($q['text']); ?></h3>
                        <p>
                            TIPO: <?php echo strtoupper($q['type']); ?>. 
                            <?php echo $q['responded']; ?> respuestas.
                            (<?php echo $q['empty']; ?> vacíos).
                        </p>
                    </div>
                    <div class="report-card-body">
                        
                        <?php if ($q['type'] === 'number'): ?>
                            <?php if(isset($q['stats'])): ?>
                            <table class="stats-table" style="max-width: 800px; margin: 0 auto;">
                                <thead>
                                    <tr><th>Media</th><th>Mediana</th><th>Moda</th><th>Desv. Estándar</th></tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="text-align:right;"><?php echo $q['stats']['media']; ?></td>
                                        <td style="text-align:right;"><?php echo $q['stats']['mediana']; ?></td>
                                        <td style="text-align:right;"><?php echo $q['stats']['moda']; ?></td>
                                        <td style="text-align:right;"><?php echo $q['stats']['desviacion']; ?></td>
                                    </tr>
                                </tbody>
                            </table>
                            <?php else: ?>
                                <p style="text-align:center; color:#999;">Sin datos suficientes.</p>
                            <?php endif; ?>

                        <?php elseif ($q['type'] === 'select' || $q['type'] === 'date'): ?>
                            <?php if (!empty($q['data'])): ?>
                                <div class="chart-wrapper">
                                    <canvas id="chart-<?php echo $q['id']; ?>"></canvas>
                                </div>
                                <table class="stats-table">
                                    <thead><tr><th>Opción / Valor</th><th>Frecuencia</th><th>Porcentaje</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($q['data'] as $row): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['valor']); ?></td>
                                            <td><?php echo $row['frecuencia']; ?></td>
                                            <td><?php echo $row['porcentaje']; ?>%</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            <?php else: ?>
                                <p style="text-align:center; color:#999;">Sin datos.</p>
                            <?php endif; ?>

                        <?php elseif ($q['type'] === 'photo'): ?>
                            <table class="stats-table">
                                <thead><tr><th>Nombre del archivo</th><th>Frecuencia</th><th>Porcentaje</th></tr></thead>
                                <tbody>
                                    <?php foreach ($q['data'] as $row): ?>
                                    <tr>
                                        <td><i class="fas fa-image" style="color:#6D4C7F; margin-right:5px;"></i> <?php echo htmlspecialchars($row['valor']); ?></td>
                                        <td><?php echo $row['frecuencia']; ?></td>
                                        <td><?php echo $row['porcentaje']; ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>

                        <?php else: ?>
                            <table class="stats-table">
                                <thead><tr><th>Respuesta</th><th>Frecuencia</th><th>Porcentaje</th></tr></thead>
                                <tbody>
                                    <?php foreach ($q['data'] as $row): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['valor']); ?></td>
                                        <td><?php echo $row['frecuencia']; ?></td>
                                        <td><?php echo $row['porcentaje']; ?>%</td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div id="modal-custom-report" class="modal-overlay hidden">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 id="modal-rep-title">Nuevo Informe Personalizado</h2>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="form-custom-report">
                    <input type="hidden" name="project_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="report_id" id="rep-id">
                    
                    <div class="form-group">
                        <label>Nombre del Informe</label>
                        <input type="text" name="report_name" id="rep-name" class="form-control" required placeholder="Ej: Datos Demográficos">
                    </div>

                    <div class="form-group">
                        <label>Selecciona las preguntas a incluir:</label>
                        <div class="checkbox-list-container">
                            <?php foreach ($allQuestions as $q): ?>
                                <label class="checkbox-item">
                                    <input type="checkbox" name="questions[]" value="<?php echo $q['id']; ?>" class="rep-q-check">
                                    <span><?php echo htmlspecialchars($q['text']); ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="btn-secondary close-modal">Cancelar</button>
                        <button type="submit" class="btn-primary">Guardar Informe</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            // --- GRÁFICOS (Chart.js) ---
            if (typeof Chart !== 'undefined') {
                const statsData = <?php echo json_encode($stats); ?>;
                statsData.forEach(q => {
                    if ((q.type === 'select' || q.type === 'date') && q.data && q.data.length > 0) {
                        const ctx = document.getElementById('chart-' + q.id);
                        if (ctx) {
                            const labels = q.data.map(d => d.valor);
                            const data = q.data.map(d => d.frecuencia);
                            const bgColors = labels.map((_, i) => `hsl(${260 + (i * 20)}, 60%, 70%)`);
                            new Chart(ctx, {
                                type: q.type === 'date' ? 'line' : 'bar',
                                data: {
                                    labels: labels,
                                    datasets: [{ label: 'Frecuencia', data: data, backgroundColor: bgColors, borderColor: '#6D4C7F', borderWidth: 1, fill: (q.type === 'date') }]
                                },
                                options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
                            });
                        }
                    }
                });
            }

            // --- LÓGICA IMPRESIÓN ---
            window.printReport = function() {
                // 1. Guardar título original
                const originalTitle = document.title;
                
                // 2. Obtener datos PHP limpios
                // Usamos addslashes para evitar errores de JS si el nombre tiene comillas
                const projName = "<?php echo addslashes($projTitle ?? 'Proyecto'); ?>";
                const repName = "<?php echo addslashes($currentReportName ?? 'Informe predeterminado'); ?>";
                
                // 3. Formatear fecha (AAAA-MM-DD)
                const d = new Date();
                const dateStr = d.getFullYear() + "-" + 
                                ("0" + (d.getMonth() + 1)).slice(-2) + "-" + 
                                ("0" + d.getDate()).slice(-2);

                // 4. Construir nombre del archivo según tu regla
                let fileName = "";
                
                if (repName === 'Informe predeterminado') {
                    // Formato: nombre del proyecto_fecha.pdf
                    fileName = `${projName}_${dateStr}`;
                } else {
                    fileName = `${projName}_${repName}_${dateStr}`;
                }

                // 5. Asignar título y ejecutar impresión
                document.title = fileName;
                window.print();

                // 6. Restaurar título original después de un momento
                setTimeout(() => {
                    document.title = originalTitle;
                }, 1000);
            };

            // --- LÓGICA DEL MODAL DE INFORMES ---
            const modal = document.getElementById('modal-custom-report');
            const form = document.getElementById('form-custom-report');
            const title = document.getElementById('modal-rep-title');
            
            const currentReportId = "<?php echo $currentReportId; ?>";
            const currentSelectedQuestions = <?php 
                if(!empty($currentReportId)) {
                    $found = null;
                    foreach($customReports as $r) { if($r['id'] == $currentReportId) $found = $r; }
                    echo $found ? $found['questions_json'] : '[]';
                } else { echo '[]'; } 
            ?>;
            const currentReportName = "<?php echo htmlspecialchars($currentReportName); ?>";

            window.openReportModal = function(mode, reportId = null) {
                form.reset();
                document.getElementById('rep-id').value = '';
                
                if (mode === 'create') {
                    title.textContent = 'Nuevo Informe Personalizado';
                    document.querySelectorAll('.rep-q-check').forEach(c => c.checked = false);
                } else if (mode === 'edit') {
                    title.textContent = 'Editar Informe';
                    document.getElementById('rep-id').value = reportId;
                    const select = document.getElementById('report-selector');
                    document.getElementById('rep-name').value = select.options[select.selectedIndex].text.trim();
                    
                    document.querySelectorAll('.rep-q-check').forEach(c => {
                        if (currentSelectedQuestions.includes(c.value)) {
                            c.checked = true;
                        } else {
                            c.checked = false;
                        }
                    });
                }
                modal.classList.remove('hidden');
            };

            modal.querySelectorAll('.close-modal').forEach(btn => {
                btn.onclick = () => modal.classList.add('hidden');
            });

            form.onsubmit = function(e) {
                e.preventDefault();
                const fd = new FormData(form);
                const btn = form.querySelector('button[type="submit"]');
                const originalText = btn.textContent;
                btn.disabled = true; btn.textContent = 'Guardando...';

                fetch(`${ADMIN_URL}/proyectos/saveReport`, {
                    method: 'POST',
                    body: fd
                })
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        modal.classList.add('hidden');
                        loadReport(d.report_id);
                    } else {
                        alert('Error: ' + d.message);
                    }
                })
                .finally(() => {
                    btn.disabled = false; btn.textContent = originalText;
                });
            };

            window.loadReport = function(reportId) {
                const dataArea = document.querySelector('.data-content-area');
                const projectId = "<?php echo $id; ?>";
                
                dataArea.style.opacity = '0.6';

                let url = `${ADMIN_URL}/proyectos/getReportsAjax/${projectId}`;
                if(reportId) url += `?report_id=${reportId}`;

                fetch(url)
                    .then(res => res.text())
                    .then(html => {
                        dataArea.innerHTML = html;
                        dataArea.style.opacity = '1';
                        const scripts = dataArea.querySelectorAll('script');
                        scripts.forEach(s => {
                            const ns = document.createElement('script');
                            ns.textContent = s.textContent;
                            document.body.appendChild(ns); 
                            document.body.removeChild(ns);
                        });
                    });
            };
        })();
    </script>
</div>