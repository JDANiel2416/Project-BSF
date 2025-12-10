document.addEventListener('DOMContentLoaded', function () {
    const ADMIN_URL = window.ADMIN_URL || '/BSF/admin';
    const mainContainer = document.querySelector('.dashboard-container');
    const projectList = document.getElementById('view-projects-list');
    const ajaxSlot = document.getElementById('ajax-content-slot');
    const headerTitle = document.getElementById('header-title');
    const headerSearch = document.getElementById('header-search-container');
    const tbody = document.querySelector('#projects-table tbody');

    // --- SIDEBAR TOGGLE ---
    const toggleBtn = document.getElementById('toggle-sidebar-btn');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            mainContainer.classList.toggle('sidebar-collapsed');
        });
    }

    // --- BUSCADOR ---
    const searchInput = document.getElementById('global-search');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#projects-table tbody tr').forEach(row => {
                if (row.cells.length < 2) return;
                const nameLink = row.querySelector('.project-name-link');
                const text = nameLink ? nameLink.textContent.toLowerCase() : row.cells[1].textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // --- HEADER LOGIC ---
    function updateHeader(mode, title = 'Mis proyectos') {
        if (mode === 'list') {
            headerTitle.textContent = title;
            headerSearch.classList.remove('hidden-search');
            if (searchInput) searchInput.value = '';
            if (tbody) Array.from(tbody.rows).forEach(r => r.style.display = '');
        } else {
            headerTitle.textContent = title;
            headerSearch.classList.add('hidden-search');
        }
    }

    // --- DROPDOWN 3 PUNTOS ---
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.dropdown')) {
            document.querySelectorAll('.dropdown-content').forEach(d => d.classList.remove('show'));
        } else if (e.target.closest('.btn-icon-action')) {
            e.preventDefault();
            e.stopPropagation();
            if (e.target.closest('.btn-restore')) return;

            const btn = e.target.closest('.btn-icon-action');
            const content = btn.nextElementSibling;
            if (content) {
                document.querySelectorAll('.dropdown-content').forEach(d => {
                    if (d !== content) d.classList.remove('show');
                });
                content.classList.toggle('show');
            }
        }
    });

    // --- NAVEGACIÓN SIDEBAR ---
    const navActive = document.querySelector('a[data-action="load-active"]');
    if (navActive) {
        navActive.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = `${ADMIN_URL}/dashboard`;
        });
    }

    const navArchived = document.querySelector('a[data-action="load-archived"]');
    if (navArchived) {
        navArchived.addEventListener('click', async (e) => {
            e.preventDefault();
            document.getElementById('nav-item-active').classList.remove('active');
            document.getElementById('nav-item-archived').classList.add('active');

            projectList.style.display = 'block';
            ajaxSlot.innerHTML = '';
            updateHeader('list', 'Proyectos Archivados');

            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; padding:20px;"><div class="spinner" style="border:3px solid #eee;border-top:3px solid #6D4C7F;border-radius:50%;width:30px;height:30px;animation:spin 1s infinite;margin:0 auto;"></div></td></tr>';

            try {
                const res = await fetch(`${ADMIN_URL}/proyectos/listarArchivados`);
                const html = await res.text();
                tbody.innerHTML = html;
            } catch (err) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error al cargar archivados.</td></tr>';
            }
        });
    }

    // --- ACCIONES DE TABLA ---
    if (tbody) {
        tbody.addEventListener('click', (e) => {
            if (e.target.closest('.btn-restore')) {
                e.stopPropagation();
                const btn = e.target.closest('.btn-restore');
                const id = btn.dataset.id;
                if (confirm('¿Restaurar este proyecto a la lista de activos?')) {
                    fetch(`${ADMIN_URL}/proyectos/restaurar/${id}`)
                        .then(r => r.json())
                        .then(d => {
                            if (d.success) navArchived.click();
                            else alert('Error: ' + d.message);
                        });
                }
                return;
            }
            const row = e.target.closest('tr');
            if (!row || e.target.closest('input') || e.target.closest('.dropdown') || e.target.closest('.btn-icon-action')) return;
            const id = row.dataset.id;
            if (id) loadView('detail', id);
        });
    }

    // --- MODALES ---
    const modalOpts = document.getElementById('modal-nuevo-proyecto');
    const modalDetails = document.getElementById('modal-detalles-proyecto');
    const btnNew = document.getElementById('btn-open-create-modal');
    const btnBack = document.getElementById('btn-back-options');
    const btnOptCreate = document.getElementById('opt-crear-cero');

    if (btnNew) btnNew.addEventListener('click', (e) => { e.preventDefault(); modalOpts.classList.remove('hidden'); });
    if (btnOptCreate) btnOptCreate.addEventListener('click', () => { modalOpts.classList.add('hidden'); modalDetails.classList.remove('hidden'); });
    if (btnBack) btnBack.addEventListener('click', () => { modalDetails.classList.add('hidden'); modalOpts.classList.remove('hidden'); });

    document.body.addEventListener('click', (e) => {
        if (e.target.classList.contains('close-modal') || e.target.classList.contains('modal-overlay')) {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden'));
        }
    });

    const formCreate = document.getElementById('form-create-project');
    if (formCreate) {
        formCreate.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-create');
            const loader = btn.querySelector('.loader');
            btn.disabled = true; loader.classList.remove('hidden');
            const formData = new FormData(formCreate);

            try {
                const res = await fetch(`${ADMIN_URL}/proyectos/crear`, { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    modalDetails.classList.add('hidden');
                    formCreate.reset();
                    loadView('builder', data.projectId, data.projectName);
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) { console.error(err); alert('Error de conexión.'); }
            finally { btn.disabled = false; loader.classList.add('hidden'); }
        });
    }

    // --- ROUTER VISTAS ---
    async function loadView(type, id, extraData = null) {
        projectList.style.display = 'none';
        ajaxSlot.innerHTML = '<div style="display:flex;justify-content:center;padding:50px;"><div class="spinner" style="border:3px solid #eee;border-top:3px solid #6D4C7F;border-radius:50%;width:40px;height:40px;animation:spin 1s infinite"></div></div><style>@keyframes spin{to{transform:rotate(360deg)}}</style>';

        let url = '';
        if (type === 'detail') url = `${ADMIN_URL}/proyectos/getProjectDetailAjax/${id}`;
        if (type === 'builder') url = `${ADMIN_URL}/proyectos/getFormConstructorAjax/${id}`;

        try {
            const res = await fetch(url);
            const html = await res.text();
            ajaxSlot.innerHTML = html;

            let projectName = extraData;
            if (!projectName) {
                const wrapper = ajaxSlot.querySelector('[data-project-name]');
                if (wrapper) projectName = wrapper.dataset.projectName;
            }
            updateHeader('project', projectName || 'Proyecto');

            if (type === 'builder') initFormBuilderLogic(id);

        } catch (err) {
            ajaxSlot.innerHTML = '<p style="text-align:center; padding:20px;">Error al cargar vista.</p>';
        }
    }
    // --- INTERACCIONES DE VISTAS AJAX ---
    ajaxSlot.addEventListener('click', (e) => {
        // Cerrar Constructor
        if (e.target.closest('#btn-close-builder')) {
            loadView('detail', e.target.closest('#btn-close-builder').dataset.id);
            return;
        }

        // Click en Tabs Principales (RESUMEN, FORMULARIO, DATOS, CONFIGURACIÓN)
        if (e.target.classList.contains('tab-link')) {
            const tabId = e.target.dataset.tab;
            const wrapper = e.target.closest('.project-detail-wrapper');

            // UI Toggle Active
            wrapper.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            wrapper.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            e.target.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');

            // Cargar datos si es el tab DATOS
            if (tabId === 'data') {
                const dataArea = document.querySelector('.data-content-area');
                const formSettings = document.getElementById('form-update-project');

                const sidebarItems = document.querySelectorAll('.data-nav-item');
                sidebarItems.forEach(item => item.classList.remove('active'));

                // Buscar el item que dice "Tabla" y activarlo
                sidebarItems.forEach(item => {
                    if (item.textContent.trim().includes('Tabla')) {
                        item.classList.add('active');
                    }
                });

                if (formSettings && dataArea) {
                    const pid = formSettings.dataset.id;
                    dataArea.innerHTML = '<div style="text-align:center;padding:50px;"><div class="spinner" style="border:3px solid #eee;border-top:3px solid #6D4C7F;border-radius:50%;width:30px;height:30px;animation:spin 1s infinite;margin:0 auto 10px;"></div><span style="color:#666;">Cargando tabla de datos...</span></div>';

                    fetch(`${ADMIN_URL}/proyectos/getDataTableAjax/${pid}`)
                        .then(res => res.text())
                        .then(html => {
                            dataArea.innerHTML = html;

                            // --- CRÍTICO: EJECUTAR SCRIPTS INYECTADOS ---
                            const scripts = dataArea.querySelectorAll('script');
                            scripts.forEach(script => {
                                const newScript = document.createElement('script');
                                if (script.src) newScript.src = script.src;
                                else newScript.textContent = script.textContent;
                                document.body.appendChild(newScript);
                                document.body.removeChild(newScript);
                            });

                            initDataTable();
                        })
                        .catch(err => {
                            console.error(err);
                            dataArea.innerHTML = '<p style="color:red;text-align:center;padding:20px;">Error al cargar los datos.</p>';
                        });
                }
            }
        }

        // --- LÓGICA DE NAV SIDEBAR EN DATOS ---
        if (e.target.closest('.data-nav-item')) {
            const item = e.target.closest('.data-nav-item');
            const sidebar = item.closest('.data-sidebar');
            const labelSpan = item.querySelector('span');
            const sectionName = labelSpan ? labelSpan.textContent.trim() : '';

            // Actualizar UI (Clase active)
            sidebar.querySelectorAll('.data-nav-item').forEach(i => i.classList.remove('active'));
            item.classList.add('active');

            const dataArea = document.querySelector('.data-content-area');
            const formProject = document.getElementById('form-update-project');
            if (!formProject || !dataArea) return;

            const projectId = formProject.dataset.id;

            // Feedback de carga visual
            dataArea.innerHTML = '<div style="text-align:center;padding:50px;"><div class="spinner" style="border:3px solid #eee;border-top:3px solid #6D4C7F;border-radius:50%;width:30px;height:30px;animation:spin 1s infinite;margin:0 auto 10px;"></div><span style="color:#666;">Cargando...</span></div>';

            // 1. CARGAR TABLA
            if (sectionName === 'Tabla') {
                fetch(`${ADMIN_URL}/proyectos/getDataTableAjax/${projectId}`)
                    .then(res => res.text())
                    .then(html => {
                        dataArea.innerHTML = html;
                        // Ejecutar scripts (necesario para la tabla interactiva)
                        const scripts = dataArea.querySelectorAll('script');
                        scripts.forEach(s => {
                            const ns = document.createElement('script');
                            if (s.src) ns.src = s.src; else ns.textContent = s.textContent;
                            document.body.appendChild(ns); document.body.removeChild(ns);
                        });
                        initDataTable();
                    });
            }
            // 2. CARGAR INFORMES
            else if (sectionName === 'Informes') {
                fetch(`${ADMIN_URL}/proyectos/getReportsAjax/${projectId}`)
                    .then(res => res.text())
                    .then(html => {
                        dataArea.innerHTML = html;
                        // Ejecutar scripts (necesario para los gráficos Chart.js)
                        const scripts = dataArea.querySelectorAll('script');
                        scripts.forEach(s => {
                            const ns = document.createElement('script');
                            ns.textContent = s.textContent;
                            document.body.appendChild(ns); document.body.removeChild(ns);
                        });
                    });
            }
            // 3. CARGAR DESCARGA 
            else if (sectionName === 'Descarga') {
                fetch(`${ADMIN_URL}/proyectos/getExportAjax/${projectId}`)
                    .then(res => res.text())
                    .then(html => {
                        dataArea.innerHTML = html;
                        const scripts = dataArea.querySelectorAll('script');
                        scripts.forEach(s => {
                            const ns = document.createElement('script');
                            ns.textContent = s.textContent;
                            document.body.appendChild(ns); document.body.removeChild(ns);
                        });
                    })
                    .catch(err => {
                        console.error("Error cargando descarga:", err);
                        dataArea.innerHTML = '<p style="color:red; text-align:center;">Error al cargar el módulo de descargas.</p>';
                    });
            }
            // 4. CARGAR MAPA (NUEVO)
            else if (sectionName === 'Mapa') {
                fetch(`${ADMIN_URL}/proyectos/getMapAjax/${projectId}`)
                    .then(res => res.text())
                    .then(html => {
                        dataArea.innerHTML = html;
                        const scripts = dataArea.querySelectorAll('script');
                        scripts.forEach(s => {
                            const ns = document.createElement('script');
                            if (s.src) {
                                ns.src = s.src;
                                ns.onload = () => { /* Script externo cargado */ };
                            } else {
                                ns.textContent = s.textContent;
                            }
                            document.body.appendChild(ns);
                            if (!s.src) document.body.removeChild(ns);
                        });
                    });
            }
            // 4. Otros
            else {
                dataArea.innerHTML = '<div class="data-placeholder"><i class="fas fa-tools"></i><p>Sección en construcción.</p></div>';
            }
        }

        // --- LÓGICA IMPLEMENTAR ---
        if (e.target.id === 'btn-deploy-action') {
            const btn = e.target;
            const id = btn.dataset.id;
            const mode = btn.dataset.mode;

            const performDeploy = () => {
                const originalText = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

                fetch(`${ADMIN_URL}/proyectos/implementar/${id}`)
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            alert("¡Implementación exitosa! El formulario ha sido actualizado públicamente.");
                            loadView('detail', id);
                        } else {
                            alert('Error: ' + d.message);
                            btn.disabled = false;
                            btn.innerHTML = originalText;
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        alert('Error de conexión');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    });
            };

            if (mode === 'redeploy') {
                const modalId = 'confirm-redeploy-modal';
                let existingModal = document.getElementById(modalId);
                if (existingModal) existingModal.remove();

                const modalHtml = `
                <div id="${modalId}" class="modal-overlay fade-in">
                    <div class="modal-content" style="max-width: 450px; text-align: left;">
                        <div class="modal-header" style="background: #fff; border-bottom: none; padding-bottom: 0;">
                            <h2 style="color: #333; font-size: 18px;">Sobrescribir la implementación existente</h2>
                        </div>
                        <div class="modal-body" style="padding-top: 10px;">
                            <p style="color: #555; font-size: 14px;">Este formulario ya ha sido implementado. ¿Estás seguro que deseas sobrescribir la implementación existente?</p>
                            <p style="color: #d9534f; font-weight: 500; font-size: 13px; margin-top: 10px;">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer" style="padding: 15px 20px; display: flex; justify-content: flex-end; gap: 10px; border-top: 1px solid #eee;">
                            <button class="btn-secondary close-conf-modal">Cancelar</button>
                            <button class="btn-primary" id="btn-conf-ok" style="background-color: #d9534f; border-color: #d9534f;">OK</button>
                        </div>
                    </div>
                </div>`;

                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const modalEl = document.getElementById(modalId);

                modalEl.querySelector('.close-conf-modal').onclick = () => modalEl.remove();
                modalEl.querySelector('#btn-conf-ok').onclick = () => {
                    modalEl.remove();
                    performDeploy();
                };
            } else {
                if (confirm('¿Estás seguro de implementar este formulario? Pasará a ser público.')) {
                    performDeploy();
                }
            }
        }

        // --- LÓGICA COPIAR ENLACE ---
        if (e.target.classList.contains('copy-btn')) {
            const input = document.getElementById('hidden-public-link');
            if (input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    const originalText = e.target.innerText;
                    e.target.innerText = 'Copiado!';
                    setTimeout(() => e.target.innerText = originalText, 2000);
                });
            }
        }

        // --- LÓGICA ARCHIVAR/RESTAURAR ---
        if (e.target.id === 'btn-archive-project') {
            const id = e.target.dataset.id;
            const action = e.target.dataset.action;
            const endpoint = action === 'restore' ? 'restaurar' : 'archivar';
            const successMsg = action === 'restore' ? 'Proyecto restaurado' : 'Proyecto archivado';

            fetch(`${ADMIN_URL}/proyectos/${endpoint}/${id}`)
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert(successMsg);
                        window.location.href = `${ADMIN_URL}/dashboard`;
                    } else {
                        alert('Error: ' + d.message);
                    }
                });
        }

        // --- LÓGICA ELIMINAR ---
        if (e.target.id === 'btn-delete-project') {
            if (confirm('¿Estás SEGURO de eliminar este proyecto permanentemente?')) {
                const id = e.target.dataset.id;
                fetch(`${ADMIN_URL}/proyectos/eliminar/${id}`)
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            alert('Proyecto eliminado');
                            window.location.href = `${ADMIN_URL}/dashboard`;
                        }
                    });
            }
        }

        // --- REDIRECCIONES ---
        if (e.target.closest('.action-link-builder')) {
            e.preventDefault();
            const id = e.target.closest('.action-link-builder').dataset.id;
            loadView('builder', id);
            return;
        }

        if (e.target.closest('.btn-preview-action')) {
            e.preventDefault();
            let questions = [];
            const builderWrapper = document.querySelector('.form-builder-wrapper');
            if (builderWrapper) {
                questions = window.currentFormQuestions || [];
            } else {
                const detailWrapper = document.querySelector('.project-detail-wrapper');
                if (detailWrapper && detailWrapper.dataset.questions) {
                    try { questions = JSON.parse(detailWrapper.dataset.questions); }
                    catch (e) { questions = []; }
                }
            }
            openPreviewModal(questions);
        }
    });

    document.addEventListener('submit', async (e) => {
        if (e.target && e.target.id === 'form-update-project') {
            e.preventDefault();
            const form = e.target;
            const id = form.dataset.id;
            const btn = form.querySelector('button[type="submit"]');
            const originalText = btn.textContent;
            btn.disabled = true; btn.textContent = 'Guardando...';

            const fd = new FormData(form);
            try {
                const res = await fetch(`${ADMIN_URL}/proyectos/actualizar/${id}`, { method: 'POST', body: fd });
                const data = await res.json();
                if (data.success) {
                    alert('Proyecto actualizado');
                    const newName = fd.get('nombre');
                    if (newName) document.getElementById('header-title').textContent = newName;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (err) { alert('Error de conexión'); }
            finally { btn.disabled = false; btn.textContent = originalText; }
        }
    });

    function openPreviewModal(questions) {
        let modal = document.getElementById('preview-dynamic-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'preview-dynamic-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width:600px;">
                    <div class="modal-header">
                        <h2>Vista Previa Interactiva</h2>
                        <button class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body" style="max-height:70vh; overflow-y:auto; padding:30px;">
                        <form id="preview-form-mock" novalidate></form>
                    </div>
                    <div class="modal-footer" style="padding:15px; border-top:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                        <span id="preview-validation-msg" style="font-size:12px; color:red;"></span>
                        <div>
                            <button type="button" class="btn-secondary close-modal">Cerrar</button>
                            <button type="button" id="btn-test-submit" class="btn-primary">Probar Envío</button>
                        </div>
                    </div>
                </div>`;
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if (e.target.classList.contains('close-modal') || e.target.classList.contains('modal-overlay')) modal.classList.add('hidden');
            });
        }

        const formContainer = modal.querySelector('#preview-form-mock');
        const validationMsg = modal.querySelector('#preview-validation-msg');
        const btnTest = modal.querySelector('#btn-test-submit');

        formContainer.innerHTML = '';
        validationMsg.textContent = '';

        if (!questions || questions.length === 0) {
            formContainer.innerHTML = '<p style="text-align:center; color:#999;">Formulario vacío</p>';
        } else {
            // 1. RENDERIZADO
            questions.forEach(q => {
                const div = document.createElement('div');
                div.id = `preview-q-${q.id}`;
                div.className = 'preview-q-item';
                div.style.marginBottom = '20px';

                const label = document.createElement('label');
                label.style.display = 'block';
                label.style.fontWeight = '600';
                label.style.marginBottom = '8px';
                label.style.fontSize = '14px';
                label.innerHTML = q.text + (q.required === 'yes' ? ' <span style="color:red">*</span>' : '');
                div.appendChild(label);

                // Render Input según tipo
                let inputHtml = '';
                if (q.type === 'select') {
                    const opts = Array.isArray(q.options) ? q.options : [];
                    if (opts.length === 0) inputHtml = '<em style="color:#999">Sin opciones</em>';
                    else {
                        inputHtml += `<div class="radio-group-preview" style="display:flex; flex-direction:column; gap:5px;">`;
                        opts.forEach(opt => {
                            inputHtml += `<label style="font-weight:400; font-size:13px; cursor:pointer;"><input type="radio" name="${q.id}" value="${opt}"> ${opt}</label>`;
                        });
                        inputHtml += `</div>`;
                    }
                } else if (q.type === 'number') {
                    inputHtml = `<input type="number" name="${q.id}" class="form-control" placeholder="Número...">`;
                } else if (q.type === 'date') {
                    inputHtml = `<input type="date" name="${q.id}" class="form-control">`;
                } else if (q.type === 'photo') {
                    inputHtml = `<input type="file" name="${q.id}" class="form-control" disabled><small style="color:#999">(Subida deshabilitada en vista previa)</small>`;
                } else if (q.type === 'gps') {
                    inputHtml = `<input type="text" name="${q.id}" class="form-control" placeholder="Coordenadas GPS (Simulado)">`;
                } else { // Texto
                    inputHtml = `<input type="text" name="${q.id}" class="form-control" placeholder="Texto corto...">`;
                }

                const inputWrapper = document.createElement('div');
                inputWrapper.innerHTML = inputHtml;
                div.appendChild(inputWrapper);

                if (q.hint) {
                    const hint = document.createElement('small');
                    hint.style.color = '#777';
                    hint.style.fontStyle = 'italic';
                    hint.textContent = q.hint;
                    div.appendChild(hint);
                }

                // Contenedor de error
                const errorDiv = document.createElement('div');
                errorDiv.className = 'error-text';
                errorDiv.style.color = '#dc3545';
                errorDiv.style.fontSize = '12px';
                errorDiv.style.marginTop = '5px';
                errorDiv.style.display = 'none';
                div.appendChild(errorDiv);

                formContainer.appendChild(div);
            });

            // 2. MOTOR DE LÓGICA
            const checkLogic = () => {
                questions.forEach(q => {
                    const qDiv = document.getElementById(`preview-q-${q.id}`);
                    if (!qDiv) return;

                    // Si NO hay condiciones, mostrar siempre
                    if (!q.logic || !q.logic.conditions || q.logic.conditions.length === 0) {
                        qDiv.style.display = 'block';
                        return;
                    }

                    // Evaluar condiciones (AND - todas deben cumplirse)
                    let shouldShow = true;
                    for (let cond of q.logic.conditions) {
                        const sourceId = cond.source;
                        // Obtener valor de la pregunta fuente
                        let sourceVal = '';
                        const sourceInput = formContainer.querySelector(`[name="${sourceId}"]`);

                        if (sourceInput) {
                            if (sourceInput.type === 'radio') {
                                const checked = formContainer.querySelector(`[name="${sourceId}"]:checked`);
                                sourceVal = checked ? checked.value : '';
                            } else {
                                sourceVal = sourceInput.value;
                            }
                        }

                        // Evaluar
                        const op = cond.operator;
                        const targetVal = cond.value;

                        if (op === 'filled') {
                            if (sourceVal === '' || sourceVal === null || sourceVal === undefined) shouldShow = false;
                        } else if (op === '=') {
                            if (sourceVal != targetVal) shouldShow = false; // != permite comparar "5" con 5
                        } else if (op === '!=') {
                            if (sourceVal == targetVal) shouldShow = false;
                        }
                    }

                    qDiv.style.display = shouldShow ? 'block' : 'none';

                    // Si se oculta, opcionalmente limpiar valor para que no afecte validaciones posteriores
                    if (!shouldShow) {
                        const myInputs = qDiv.querySelectorAll('input');
                        myInputs.forEach(i => {
                            if (i.type === 'radio' || i.type === 'checkbox') i.checked = false;
                            else i.value = '';
                        });
                    }
                });
            };

            formContainer.addEventListener('input', checkLogic);
            formContainer.addEventListener('change', checkLogic);

            checkLogic();

            // 3. MOTOR DE VALIDACIÓN
            btnTest.onclick = () => {
                let hasErrors = false;
                validationMsg.textContent = '';

                // Limpiar errores previos
                formContainer.querySelectorAll('.error-text').forEach(e => { e.style.display = 'none'; e.textContent = ''; });
                formContainer.querySelectorAll('.form-control').forEach(i => i.style.borderColor = '#ccc');

                questions.forEach(q => {
                    const qDiv = document.getElementById(`preview-q-${q.id}`);
                    // Solo validar si es visible
                    if (qDiv.style.display === 'none') return;

                    let val = '';
                    const input = formContainer.querySelector(`[name="${q.id}"]`);
                    if (input) {
                        if (input.type === 'radio') {
                            const checked = formContainer.querySelector(`[name="${q.id}"]:checked`);
                            val = checked ? checked.value : '';
                        } else {
                            val = input.value.trim();
                        }
                    }

                    // A) Validación 'Requerido'
                    if (q.required === 'yes' && val === '') {
                        showError(q.id, 'Esta respuesta es obligatoria.');
                        hasErrors = true;
                    }

                    // B) Criterios de Validación Personalizados
                    if (val !== '' && q.validation && q.validation.criteria && q.validation.criteria.length > 0) {
                        let criteriaMet = true; // Asumimos AND para múltiples criterios
                        for (let crit of q.validation.criteria) {
                            const op = crit.operator;
                            const limit = crit.value;

                            // Conversión numérica si aplica
                            const numVal = parseFloat(val);
                            const numLimit = parseFloat(limit);
                            const isNum = !isNaN(numVal) && !isNaN(numLimit);

                            if (op === '=') { if (val != limit) criteriaMet = false; }
                            else if (op === '!=') { if (val == limit) criteriaMet = false; }
                            else if (isNum) {
                                if (op === '>') { if (!(numVal > numLimit)) criteriaMet = false; }
                                else if (op === '<') { if (!(numVal < numLimit)) criteriaMet = false; }
                                else if (op === '>=') { if (!(numVal >= numLimit)) criteriaMet = false; }
                                else if (op === '<=') { if (!(numVal <= numLimit)) criteriaMet = false; }
                            }
                        }

                        if (!criteriaMet) {
                            showError(q.id, q.validation.errorMessage || 'El valor no cumple con los criterios.');
                            hasErrors = true;
                        }
                    }
                });

                if (hasErrors) {
                    validationMsg.textContent = 'Hay errores en el formulario.';
                } else {
                    alert('¡Formulario válido! En un entorno real se enviaría.');
                }
            };

            function showError(qId, msg) {
                const qDiv = document.getElementById(`preview-q-${qId}`);
                const errDiv = qDiv.querySelector('.error-text');
                const inp = qDiv.querySelector('.form-control');
                if (inp) inp.style.borderColor = 'red';
                errDiv.textContent = msg;
                errDiv.style.display = 'block';
            }
        }
        modal.classList.remove('hidden');
    }

    function initFormBuilderLogic(projectId) {
        const wrapper = document.querySelector('.form-builder-wrapper');
        let questions = [];
        try {
            questions = JSON.parse(wrapper.dataset.questions || '[]');
        } catch (e) {
            questions = [];
        }

        // Normalizar datos y asegurar estructura
        questions.forEach(q => {
            if (q.type === 'select' && !Array.isArray(q.options)) {
                q.options = ['Opción 1', 'Opción 2'];
            }
            if (!q.columnName) q.columnName = 'col_' + Date.now() + Math.floor(Math.random() * 1000);
            if (!q.hint) q.hint = '';
            if (!q.required) q.required = 'no';

            if (!q.logic) q.logic = { conditions: [] };
            // Validación ahora tiene criteria (lista) y errorMessage (string global)
            if (!q.validation) q.validation = { criteria: [], errorMessage: '' };
        });

        window.currentFormQuestions = questions;

        const container = document.getElementById('questions-container');
        const emptyState = document.getElementById('empty-state-container');

        // Elementos del Modal
        let currentEditingIndex = null;
        const modalSettings = document.getElementById('settings-question-modal');
        const inputSetCol = document.getElementById('set-col-name');
        const inputSetHint = document.getElementById('set-hint');
        const btnSaveSettings = document.getElementById('btn-save-settings');

        // Contenedores
        const logicContainer = document.getElementById('logic-conditions-container');
        const btnAddCondition = document.getElementById('btn-add-condition');

        const validationContainer = document.getElementById('validation-container');
        const btnAddValidation = document.getElementById('btn-add-validation');
        const inputValGlobalError = document.getElementById('set-val-error-msg');

        let draggedItem = null;
        let draggedIndex = null;

        // --- RENDERIZADO PRINCIPAL ---
        function render() {
            container.innerHTML = '';
            if (questions.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                questions.forEach((q, idx) => {
                    const el = document.createElement('div');
                    el.className = 'question-item fade-in';
                    el.setAttribute('draggable', 'true');
                    el.dataset.index = idx;

                    let optionsHtml = '';
                    if (q.type === 'select') {
                        optionsHtml = `<div class="q-options-area">
                        <label class="options-label">Opciones de respuesta:</label>
                        <div class="options-list">`;
                        const opts = Array.isArray(q.options) ? q.options : [];
                        opts.forEach((opt, optIdx) => {
                            optionsHtml += `
                            <div class="option-row">
                                <i class="far fa-circle"></i>
                                <input type="text" class="option-input" data-opt-index="${optIdx}" value="${opt}" placeholder="Opción ${optIdx + 1}">
                                <button type="button" class="btn-del-option" data-opt-index="${optIdx}" title="Eliminar opción">&times;</button>
                            </div>
                        `;
                        });
                        optionsHtml += `</div><button type="button" class="btn-add-option">+ Añadir Opción</button></div>`;
                    }

                    const reqMark = q.required === 'yes' ? '<span style="color:red; margin-left:5px;">*</span>' : '';

                    let iconsHtml = '';
                    if (q.logic && q.logic.conditions && q.logic.conditions.length > 0) {
                        iconsHtml += '<i class="fas fa-code-branch" title="Lógica de omisión" style="color:#6D4C7F; font-size:12px; margin-left:8px;"></i>';
                    }
                    if (q.validation && q.validation.criteria && q.validation.criteria.length > 0) {
                        iconsHtml += '<i class="fas fa-check-circle" title="Validación" style="color:#28a745; font-size:12px; margin-left:5px;"></i>';
                    }

                    el.innerHTML = `
                    <div class="q-handle" title="Arrastrar"><i class="fas fa-grip-vertical"></i></div>
                    <div class="q-content">
                        <div class="q-header">
                            <span class="q-type">${q.type.toUpperCase()}</span>
                            <span>${iconsHtml}</span>
                        </div>
                        <div style="display:flex; align-items:center;">
                            <input type="text" class="q-text-input" value="${q.text}" placeholder="Escribe tu pregunta">
                            ${reqMark}
                        </div>
                        ${q.hint ? `<small style="color:#888; display:block; margin-top:5px; font-style:italic;">${q.hint}</small>` : ''}
                        ${optionsHtml}
                    </div>
                    <div class="q-actions">
                        <button type="button" class="btn-q-action q-settings" title="Configuración"><i class="fas fa-cog"></i></button>
                        <button type="button" class="btn-q-action q-dup" title="Duplicar"><i class="fas fa-clone"></i></button>
                        <button type="button" class="btn-q-action q-del delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                    </div>
                `;

                    el.querySelector('.q-text-input').addEventListener('input', (ev) => {
                        questions[idx].text = ev.target.value;
                        window.currentFormQuestions = questions;
                    });

                    if (q.type === 'select') {
                        el.querySelectorAll('.option-input').forEach(inp => {
                            inp.addEventListener('input', (ev) => {
                                const optIdx = parseInt(ev.target.dataset.optIndex);
                                questions[idx].options[optIdx] = ev.target.value;
                                window.currentFormQuestions = questions;
                            });
                        });
                    }

                    const handle = el.querySelector('.q-handle');
                    handle.addEventListener('mousedown', () => el.setAttribute('draggable', 'true'));
                    el.addEventListener('dragstart', (e) => {
                        draggedItem = el; draggedIndex = idx; el.style.opacity = '0.5'; e.dataTransfer.effectAllowed = 'move';
                    });
                    el.addEventListener('dragend', () => {
                        el.style.opacity = '1'; draggedItem = null; draggedIndex = null;
                    });
                    el.addEventListener('dragover', (e) => { e.preventDefault(); return false; });
                    el.addEventListener('drop', (e) => {
                        e.stopPropagation();
                        if (draggedIndex !== null && draggedIndex !== idx) {
                            const itemMoved = questions.splice(draggedIndex, 1)[0];
                            questions.splice(idx, 0, itemMoved);
                            render();
                        }
                        return false;
                    });

                    container.appendChild(el);
                });
            }
            window.currentFormQuestions = questions;
        }

        render();

        // --- [1] LOGICA DE OMISIÓN ---
        function renderLogicRows(logicData) {
            logicContainer.innerHTML = '';
            const conditions = logicData.conditions || [];
            conditions.forEach((cond) => { addLogicRow(cond); });
        }

        function addLogicRow(data = {}) {
            const row = document.createElement('div');
            row.className = 'logic-row fade-in';
            // Estilos controlados por CSS para anchos

            const selectQ = document.createElement('select');
            selectQ.className = 'form-control logic-source-q';
            selectQ.innerHTML = '<option value="">Seleccione una pregunta...</option>';
            questions.forEach(q => {
                if (currentEditingIndex !== null && questions[currentEditingIndex].id === q.id) return;
                const option = document.createElement('option');
                option.value = q.id;
                option.textContent = q.text.substring(0, 40) + (q.text.length > 40 ? '...' : '');
                if (data.source === q.id) option.selected = true;
                selectQ.appendChild(option);
            });

            const selectOp = document.createElement('select');
            selectOp.className = 'form-control logic-op';
            selectOp.innerHTML = `
            <option value="=" ${data.operator === '=' ? 'selected' : ''}>(=) Igual a</option>
            <option value="!=" ${data.operator === '!=' ? 'selected' : ''}>(!=) No es</option>
            <option value="filled" ${data.operator === 'filled' ? 'selected' : ''}>Fue contestado</option>
        `;

            const inputVal = document.createElement('input');
            inputVal.type = 'text';
            inputVal.className = 'form-control logic-val';
            inputVal.placeholder = 'Valor de respuesta';
            inputVal.value = data.value || '';

            function toggleInput() {
                if (selectOp.value === 'filled') {
                    inputVal.style.display = 'none';
                    inputVal.value = '';
                } else {
                    inputVal.style.display = 'block';
                }
            }
            selectOp.addEventListener('change', toggleInput);
            toggleInput();

            const btnDel = document.createElement('button');
            btnDel.type = 'button';
            btnDel.className = 'btn-icon-danger';
            btnDel.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnDel.style.marginLeft = '10px';
            btnDel.style.border = 'none';
            btnDel.style.background = 'transparent';
            btnDel.style.color = '#dc3545';
            btnDel.style.cursor = 'pointer';
            btnDel.addEventListener('click', () => row.remove());

            row.appendChild(selectQ);
            row.appendChild(selectOp);
            row.appendChild(inputVal);
            row.appendChild(btnDel);
            logicContainer.appendChild(row);
        }

        // --- [2] CRITERIOS DE VALIDACIÓN ---
        function renderValidationRows(valData) {
            validationContainer.innerHTML = '';
            const criteria = valData.criteria || [];
            criteria.forEach(crit => addValidationRow(crit));
        }

        function addValidationRow(data = {}) {
            const wrapper = document.createElement('div');
            wrapper.className = 'validation-item fade-in';

            const row = document.createElement('div');
            row.className = 'validation-row';

            const label = document.createElement('span');
            label.textContent = "La respuesta de esta pregunta tiene que ser >";
            label.className = 'val-label';

            const selectOp = document.createElement('select');
            selectOp.className = 'form-control val-op';
            selectOp.innerHTML = `
            <option value="=" ${data.operator === '=' ? 'selected' : ''}>(=)</option>
            <option value="!=" ${data.operator === '!=' ? 'selected' : ''}>No (!=)</option>
            <option value=">" ${data.operator === '>' ? 'selected' : ''}>Mayor que (>)</option>
            <option value="<" ${data.operator === '<' ? 'selected' : ''}>Menos de (<)</option>
            <option value=">=" ${data.operator === '>=' ? 'selected' : ''}>Mayor igual que (>=)</option>
            <option value="<=" ${data.operator === '<=' ? 'selected' : ''}>Menos que lo mismo (<=)</option>
        `;

            const inputVal = document.createElement('input');
            inputVal.type = 'text';
            inputVal.className = 'form-control val-value';
            inputVal.placeholder = 'Valor de la respuesta';
            inputVal.value = data.value || '';

            const btnDel = document.createElement('button');
            btnDel.type = 'button';
            btnDel.innerHTML = '<i class="fas fa-trash-alt"></i>';
            btnDel.style.border = 'none';
            btnDel.style.background = 'transparent';
            btnDel.style.color = '#dc3545';
            btnDel.style.cursor = 'pointer';
            btnDel.style.fontSize = '16px';
            btnDel.style.marginLeft = '10px';
            btnDel.addEventListener('click', () => wrapper.remove());

            row.appendChild(label);
            row.appendChild(selectOp);
            row.appendChild(inputVal);
            row.appendChild(btnDel);

            wrapper.appendChild(row);
            validationContainer.appendChild(wrapper);
        }

        // --- EVENTOS MODAL ---
        modalSettings.querySelector('.settings-sidebar').addEventListener('click', (e) => {
            const li = e.target.closest('li');
            if (!li || li.classList.contains('disabled')) return;
            modalSettings.querySelectorAll('.settings-sidebar li').forEach(l => l.classList.remove('active'));
            li.classList.add('active');
            const panelId = li.dataset.panel;
            modalSettings.querySelectorAll('.settings-panel').forEach(p => p.classList.remove('active'));
            document.getElementById('panel-' + panelId).classList.add('active');
        });

        btnAddCondition.addEventListener('click', () => addLogicRow());
        btnAddValidation.addEventListener('click', () => addValidationRow());

        // --- GUARDAR CONFIGURACIÓN (CON VALIDACIÓN ESTRICTA) ---
        if (btnSaveSettings) {
            btnSaveSettings.addEventListener('click', () => {
                if (currentEditingIndex !== null) {

                    // 1. VALIDACIÓN PREVIA: Verificar campos vacíos
                    let hasErrors = false;

                    // Verificar Lógica de Omisión
                    const logicRowsCheck = logicContainer.querySelectorAll('.logic-row');
                    logicRowsCheck.forEach(row => {
                        const op = row.querySelector('.logic-op').value;
                        const val = row.querySelector('.logic-val').value.trim();
                        const src = row.querySelector('.logic-source-q').value;

                        if (src && op !== 'filled' && val === '') {
                            hasErrors = true;
                            row.querySelector('.logic-val').style.borderColor = 'red';
                        } else {
                            row.querySelector('.logic-val').style.borderColor = '#ccc';
                        }
                    });

                    // Verificar Criterios de Validación (Aquí siempre se requiere valor)
                    const valRowsCheck = validationContainer.querySelectorAll('.validation-item');
                    valRowsCheck.forEach(item => {
                        const val = item.querySelector('.val-value').value.trim();
                        if (val === '') {
                            hasErrors = true;
                            item.querySelector('.val-value').style.borderColor = 'red';
                        } else {
                            item.querySelector('.val-value').style.borderColor = '#ccc';
                        }
                    });

                    if (hasErrors) {
                        alert('Por favor, complete todos los "Valores de respuesta" requeridos antes de guardar.');
                        return; // DETENER EL GUARDADO
                    }

                    // 2. PROCESO DE GUARDADO
                    const q = questions[currentEditingIndex];

                    // General
                    q.hint = inputSetHint.value;
                    const checkedRadio = document.querySelector('input[name="set_required"]:checked');
                    if (checkedRadio) q.required = checkedRadio.value;

                    // Lógica
                    const logicRows = logicContainer.querySelectorAll('.logic-row');
                    const conditions = [];
                    logicRows.forEach(row => {
                        const source = row.querySelector('.logic-source-q').value;
                        const op = row.querySelector('.logic-op').value;
                        const val = row.querySelector('.logic-val').value;
                        // Guardar solo si tiene pregunta origen seleccionada
                        if (source) conditions.push({ source: source, operator: op, value: val });
                    });
                    if (!q.logic) q.logic = {};
                    q.logic.conditions = conditions;

                    // Validación
                    const valItems = validationContainer.querySelectorAll('.validation-item');
                    const criteria = [];
                    valItems.forEach(item => {
                        const op = item.querySelector('.val-op').value;
                        const val = item.querySelector('.val-value').value;
                        criteria.push({ operator: op, value: val });
                    });
                    if (!q.validation) q.validation = {};
                    q.validation.criteria = criteria;
                    q.validation.errorMessage = inputValGlobalError.value;
                    render();
                    modalSettings.classList.add('hidden');
                }
            });
        }

        // --- ABRIR CONFIGURACIÓN ---
        container.addEventListener('click', (e) => {
            const qItem = e.target.closest('.question-item');
            if (!qItem) return;
            const idx = parseInt(qItem.dataset.index);

            if (e.target.closest('.q-settings')) {
                currentEditingIndex = idx;
                const q = questions[idx];

                const sidebar = modalSettings.querySelector('.settings-sidebar');
                const firstLi = sidebar.querySelector('li[data-panel="options"]');
                firstLi.click();

                // Cargar General
                inputSetCol.value = q.columnName;
                inputSetHint.value = q.hint || '';
                const radios = document.getElementsByName('set_required');
                radios.forEach(r => { r.checked = (r.value === q.required); });

                // Cargar Lógica
                const logicData = q.logic || { conditions: [] };
                renderLogicRows(logicData);

                // Cargar Validación
                const valData = q.validation || { criteria: [], errorMessage: '' };
                renderValidationRows(valData);
                inputValGlobalError.value = valData.errorMessage || '';

                modalSettings.classList.remove('hidden');
            }

            // Otros eventos
            if (e.target.closest('.q-del')) {
                if (confirm('¿Eliminar esta pregunta?')) { questions.splice(idx, 1); render(); }
            }
            if (e.target.closest('.q-dup')) {
                const copy = JSON.parse(JSON.stringify(questions[idx]));
                copy.id = 'q_' + Date.now();
                copy.columnName = 'col_' + Date.now();
                copy.text = copy.text + ' (copia)';
                copy.logic = { conditions: [] };
                copy.validation = { criteria: [], errorMessage: '' };
                questions.splice(idx + 1, 0, copy);
                render();
            }
            if (e.target.closest('.btn-add-option')) {
                if (!questions[idx].options) questions[idx].options = [];
                questions[idx].options.push('Nueva Opción');
                render();
            }
            if (e.target.closest('.btn-del-option')) {
                const optIdx = parseInt(e.target.closest('.btn-del-option').dataset.optIndex);
                if (questions[idx].options.length <= 1) { alert("Debe haber al menos una opción."); return; }
                questions[idx].options.splice(optIdx, 1);
                render();
            }
        });

        // --- AGREGAR NUEVA PREGUNTA ---
        const addBox = document.getElementById('add-question-box');
        const btnAdd = document.getElementById('add-question-btn');
        const inputNew = document.getElementById('new-question-input');
        const btnConfirmAdd = document.getElementById('confirm-add-btn');

        if (btnAdd) {
            btnAdd.addEventListener('click', () => {
                addBox.classList.remove('hidden');
                btnAdd.classList.add('hidden');
                inputNew.focus();
            });
        }

        const typeModal = document.getElementById('question-type-modal');
        if (btnConfirmAdd) {
            btnConfirmAdd.addEventListener('click', () => {
                if (!inputNew.value.trim()) return;
                typeModal.classList.remove('hidden');
            });
        }

        typeModal.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', () => {
                const type = card.dataset.type;
                const newQ = {
                    id: 'q_' + Date.now(),
                    columnName: 'col_' + Date.now(),
                    text: inputNew.value,
                    type: type,
                    required: 'no',
                    hint: '',
                    logic: { conditions: [] },
                    validation: { criteria: [], errorMessage: '' }
                };
                if (type === 'select') newQ.options = ['Opción 1', 'Opción 2', 'Opción 3'];
                questions.push(newQ);
                typeModal.classList.add('hidden');
                addBox.classList.add('hidden');
                if (btnAdd) btnAdd.classList.remove('hidden');
                inputNew.value = '';
                render();
            });
        });

        document.querySelectorAll('.close-modal').forEach(btn => {
            btn.addEventListener('click', () => document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden')));
        });

        // Guardar Server
        const btnSave = document.getElementById('btn-save-builder');
        if (btnSave) {
            btnSave.addEventListener('click', async function () {
                const btn = this;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                const fd = new FormData();
                fd.append('form_definition', JSON.stringify(questions));
                try {
                    await fetch(`${ADMIN_URL}/formularios/guardar/${projectId}`, { method: 'POST', body: fd });
                    btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
                } catch (e) { btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error'; }
                setTimeout(() => btn.innerHTML = originalHTML, 2000);
            });
        }

        const btnPreview = document.getElementById('btn-preview-builder');
        if (btnPreview) {
            const newBtn = btnPreview.cloneNode(true);
            btnPreview.parentNode.replaceChild(newBtn, btnPreview);
            newBtn.addEventListener('click', () => openPreviewModal(questions));
        }
    }
    // ===============================================================
    let tableState = {
        data: [],
        filteredData: [],
        currentPage: 1,
        rowsPerPage: 30,
        activeSort: { key: '_id', dir: 'desc' },
        activeMenuColumn: null
    };

    let activeRowIdForStatus = null;

    // --- 1. INICIALIZACIÓN ---
    function initDataTable() {
        if (!document.getElementById('submissions-table')) return;

        // 1. RECUPERAR PREFERENCIA DE FILAS
        const savedRows = localStorage.getItem('admin_rows_per_page');
        if (savedRows) {
            tableState.rowsPerPage = parseInt(savedRows);
            // Sincronizar el select visualmente
            const selectRows = document.getElementById('rows-per-page');
            if (selectRows) selectRows.value = savedRows;
        }

        // Leer datos inyectados por PHP
        const rawData = window.currentTableData || [];

        // Aplanar datos para facilitar acceso
        tableState.data = rawData.map(row => {
            let answers = {};
            try { answers = JSON.parse(row.submission_data || '{}'); } catch (e) { }

            for (let qId in answers) {
                row['q_' + qId] = answers[qId];
            }
            return row;
        });

        // Estado inicial
        tableState.filteredData = [...tableState.data];

        // Orden por defecto: ID descendente
        tableState.filteredData.sort((a, b) => b._id - a._id);

        renderTableRows();
        updatePaginationUI();
    }

    if (document.getElementById('submissions-table')) {
        initDataTable();
    }

    // --- 2. RENDERIZADO DE FILAS ---
    function renderTableRows() {
        const tbody = document.getElementById('table-body');
        if (!tbody) return;
        tbody.innerHTML = '';

        // Paginación
        const start = (tableState.currentPage - 1) * tableState.rowsPerPage;
        const end = start + tableState.rowsPerPage;
        const pageData = tableState.filteredData.slice(start, end);

        const questions = window.currentQuestions || [];
        const colCount = document.querySelectorAll('#submissions-table thead th').length || (questions.length + 5);

        if (pageData.length === 0) {
            tbody.innerHTML = `<tr><td colspan="${colCount}" style="text-align:center; padding:40px; color:#999;">No se encontraron resultados</td></tr>`;
            updateBulkBar();
            return;
        }

        pageData.forEach(row => {
            const tr = document.createElement('tr');
            tr.className = 'data-row';
            tr.dataset.id = row._id;

            // Columna 1: Acciones
            let actionsHtml = `
                <td style="left:0; position:sticky; background:#fff; z-index:10; border-right:1px solid #eee; text-align:center;">
                    <div class="actions-cell-content">
                        <input type="checkbox" class="row-select" style="cursor:pointer;">
                        <button class="btn-icon-action btn-view-detail" title="Ver detalle"><i class="fas fa-eye"></i></button>
                        <button class="btn-icon-action btn-edit-row" title="Editar"><i class="fas fa-pen"></i></button>
                    </div>
                </td>`;

            // Columna 2: ID
            let idHtml = `<td>#${row._id}</td>`;

            // Columna 3: Estado de Validación
            let statusClass = 'status-pending';
            let statusText = 'En espera';
            if (row._validation_status === 'approved') { statusClass = 'status-approved'; statusText = 'Aprobado'; }
            if (row._validation_status === 'rejected') { statusClass = 'status-rejected'; statusText = 'No aprobado'; }

            let statusHtml = `
                <td>
                    <div class="status-cell-wrapper">
                        <span class="status-badge status-trigger ${statusClass}" data-id="${row._id}" style="cursor:pointer;">
                            ${statusText} <i class="fas fa-caret-down" style="font-size:10px; margin-left:3px;"></i>
                        </span>
                    </div>
                </td>`;

            // 4. Columnas de Sistema
            let datesHtml = `
                <td>${formatDate(row._submission_time)}</td>
                <td>${formatDate(row.start)}</td>
                <td>${formatDate(row.end)}</td>`;

            // 5. Columnas Dinámicas
            let questionsHtml = '';
            questions.forEach(q => {
                let val = row['q_' + q.id] || '-';
                let displayVal = val;
                if (Array.isArray(val)) displayVal = val.join(', ');
                if (typeof displayVal === 'string') displayVal = displayVal.replace('C:\\fakepath\\', '');

                let content = htmlspecialchars(displayVal);

                if (q.type === 'photo' && typeof val === 'string' && val.startsWith('uploads/')) {
                    const url = window.PUBLIC_URL + '/' + val;
                    content = `<a href="${url}" target="_blank"><img src="${url}" style="height:30px; border-radius:3px; border:1px solid #ddd;"></a>`;
                } else if (content.length > 50) {
                    content = `<span title="${htmlspecialchars(displayVal)}">${content.substring(0, 50)}...</span>`;
                }
                questionsHtml += `<td style="border-left:2px solid #f9f9f9;">${content}</td>`;
            });

            tr.innerHTML = actionsHtml + idHtml + statusHtml + datesHtml + questionsHtml;
            tbody.appendChild(tr);
        });

        // Restaurar checks
        const checkAll = document.getElementById('check-all-rows');
        if (checkAll && checkAll.checked) {
            document.querySelectorAll('.row-select').forEach(c => c.checked = true);
        }
        updateBulkBar();
    }

    // --- 3. ORDENAMIENTO ---
    document.addEventListener('click', (e) => {
        const sortMenu = document.getElementById('column-sort-menu');
        if (!sortMenu) return;

        // Abrir menú ordenar
        const btnMenu = e.target.closest('.btn-sort-menu');
        if (btnMenu) {
            e.stopPropagation(); e.preventDefault();
            sortMenu.classList.remove('visible');
            const th = btnMenu.closest('th');
            const rect = btnMenu.getBoundingClientRect();

            tableState.activeMenuColumn = {
                key: th.dataset.key,
                type: th.dataset.type || 'string'
            };

            const menuWidth = 180;
            let topPos = rect.bottom;
            let leftPos = rect.left;

            if (topPos + 100 > window.innerHeight) topPos = rect.top - 100;
            if (leftPos + menuWidth > window.innerWidth) leftPos = window.innerWidth - menuWidth - 10;

            sortMenu.style.top = topPos + 'px';
            sortMenu.style.left = leftPos + 'px';
            sortMenu.classList.add('visible');
            return;
        }

        // Ejecutar ordenar
        const sortItem = e.target.closest('.sort-item');
        if (sortItem && sortMenu.classList.contains('visible') && !sortItem.classList.contains('global-status-opt')) { // Excluir items de estado
            e.stopPropagation();
            const action = sortItem.dataset.action;
            if (action === 'asc' || action === 'desc') applySort(action);
            sortMenu.classList.remove('visible');
            return;
        }

        if (!e.target.closest('#column-sort-menu')) sortMenu.classList.remove('visible');
    });

    // Scroll listener para cerrar menús flotantes
    const tableContainer = document.querySelector('.table-responsive');
    if (tableContainer) {
        tableContainer.addEventListener('scroll', () => {
            document.getElementById('column-sort-menu')?.classList.remove('visible');
            document.getElementById('global-status-menu')?.classList.remove('visible'); // Cerrar también el de estado
        }, { passive: true });
    }

    function applySort(dir) {
        if (!tableState.activeMenuColumn) return;
        const { key, type } = tableState.activeMenuColumn;
        tableState.filteredData.sort((a, b) => {
            let valA = a[key], valB = b[key];
            if (valA == null || valA === '-') valA = '';
            if (valB == null || valB === '-') valB = '';

            if (type === 'number') return dir === 'asc' ? (parseFloat(valA) || 0) - (parseFloat(valB) || 0) : (parseFloat(valB) || 0) - (parseFloat(valA) || 0);
            if (type === 'date') return dir === 'asc' ? (new Date(valA).getTime() || 0) - (new Date(valB).getTime() || 0) : (new Date(valB).getTime() || 0) - (new Date(valA).getTime() || 0);

            valA = valA.toString().toLowerCase(); valB = valB.toString().toLowerCase();
            if (valA < valB) return dir === 'asc' ? -1 : 1;
            if (valA > valB) return dir === 'asc' ? 1 : -1;
            return 0;
        });
        renderTableRows();
    }

    // --- 4. FILTRADO ---
    document.addEventListener('keyup', (e) => { if (e.target.classList.contains('col-filter')) applyFilters(); });
    document.addEventListener('change', (e) => { if (e.target.classList.contains('col-filter')) applyFilters(); });

    function applyFilters() {
        const filters = document.querySelectorAll('.col-filter');
        tableState.filteredData = tableState.data.filter(row => {
            let pass = true;
            filters.forEach(input => {
                const key = input.closest('th').dataset.key;
                const filterVal = input.value.toLowerCase();
                if (filterVal) {
                    const rowVal = (row[key] || '').toString().toLowerCase();
                    if (input.tagName === 'SELECT') { if (rowVal !== filterVal) pass = false; }
                    else { if (!rowVal.includes(filterVal)) pass = false; }
                }
            });
            return pass;
        });
        tableState.currentPage = 1;
        updatePaginationUI();
        renderTableRows();
    }

    // --- 5. PAGINACIÓN ---
    function updatePaginationUI() {
        const total = tableState.filteredData.length;
        const totalPages = Math.ceil(total / tableState.rowsPerPage) || 1;

        // Buscar elementos en el DOM actual
        const elCurrent = document.getElementById('page-current');
        const elTotal = document.getElementById('page-total');
        const btnPrev = document.getElementById('btn-page-prev');
        const btnNext = document.getElementById('btn-page-next');

        // Actualizar textos
        if (elCurrent) elCurrent.textContent = tableState.currentPage;
        if (elTotal) elTotal.textContent = totalPages;

        // Actualizar estado de botones (Habilitar/Deshabilitar)
        if (btnPrev) {
            btnPrev.disabled = (tableState.currentPage <= 1);
            // Asegurar cambio visual si el CSS usa :disabled
            btnPrev.style.opacity = (tableState.currentPage <= 1) ? '0.5' : '1';
            btnPrev.style.cursor = (tableState.currentPage <= 1) ? 'not-allowed' : 'pointer';
        }

        if (btnNext) {
            btnNext.disabled = (tableState.currentPage >= totalPages);
            btnNext.style.opacity = (tableState.currentPage >= totalPages) ? '0.5' : '1';
            btnNext.style.cursor = (tableState.currentPage >= totalPages) ? 'not-allowed' : 'pointer';
        }
    }

    // Delegación de eventos para botones (funciona con contenido AJAX)
    document.addEventListener('click', (e) => {
        // Botón Anterior
        const prevBtn = e.target.closest('#btn-page-prev');
        if (prevBtn && !prevBtn.disabled) {
            e.preventDefault();
            if (tableState.currentPage > 1) {
                tableState.currentPage--;
                renderTableRows();
                updatePaginationUI();
            }
        }

        // Botón Siguiente
        const nextBtn = e.target.closest('#btn-page-next');
        if (nextBtn && !nextBtn.disabled) {
            e.preventDefault();
            const totalPages = Math.ceil(tableState.filteredData.length / tableState.rowsPerPage) || 1;
            if (tableState.currentPage < totalPages) {
                tableState.currentPage++;
                renderTableRows();
                updatePaginationUI();
            }
        }
    });

    // Delegación para el selector de filas por página
    document.addEventListener('change', (e) => {
        if (e.target.id === 'rows-per-page') {
            const val = parseInt(e.target.value);

            // Actualizar estado
            tableState.rowsPerPage = val;

            // Guardar en el navegador para el futuro
            localStorage.setItem('admin_rows_per_page', val);

            tableState.currentPage = 1;
            renderTableRows();
            updatePaginationUI();
        }
    });

    // Función global para las flechas pequeñas
    window.changePage = function (delta) {
        const totalPages = Math.ceil(tableState.filteredData.length / tableState.rowsPerPage) || 1;
        const newPage = tableState.currentPage + delta;
        if (newPage >= 1 && newPage <= totalPages) {
            tableState.currentPage = newPage;
            renderTableRows();
            updatePaginationUI();
        }
    };

    // --- 6. SELECCIÓN ---
    document.addEventListener('change', (e) => {
        if (e.target.id === 'check-all-rows') {
            document.querySelectorAll('.row-select').forEach(c => c.checked = e.target.checked);
            updateBulkBar();
        }
        if (e.target.classList.contains('row-select')) updateBulkBar();
    });

    document.addEventListener('click', (e) => {
        if (e.target.id === 'btn-bulk-deselect') {
            e.preventDefault();
            document.querySelectorAll('.row-select').forEach(c => c.checked = false);
            const checkAll = document.getElementById('check-all-rows');
            if (checkAll) checkAll.checked = false;
            updateBulkBar();
        }
    });

    function updateBulkBar() {
        const count = document.querySelectorAll('.row-select:checked').length;
        const bar = document.getElementById('bulk-actions-bar');
        if (bar) {
            if (count > 0) {
                bar.classList.add('visible');
                bar.querySelector('#selected-count').textContent = count;
            } else {
                bar.classList.remove('visible');
            }
        }
    }

    // --- 7. ACCIONES ---
    document.addEventListener('click', (e) => {
        const globalMenu = document.getElementById('global-status-menu');

        // A) ABRIR MENÚ DE ESTADO (Desde fila)
        if (e.target.closest('.status-trigger')) {
            e.preventDefault(); e.stopPropagation();
            const trigger = e.target.closest('.status-trigger');
            activeRowIdForStatus = trigger.dataset.id;

            if (globalMenu) {
                // Posicionar menú
                const rect = trigger.getBoundingClientRect();
                const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
                const scrollLeft = window.pageXOffset || document.documentElement.scrollLeft;

                // Ajustar posición 
                globalMenu.style.top = (rect.bottom + scrollTop + 5) + 'px';
                globalMenu.style.left = (rect.left + scrollLeft - 10) + 'px';
                globalMenu.classList.add('visible');
            }
            return;
        }

        // B) TOGGLE DROPDOWN MASIVO 
        if (e.target.closest('.dropdown-toggle')) {
            e.preventDefault(); e.stopPropagation();
            const menu = e.target.closest('.dropdown').querySelector('.dropdown-menu');
            if (menu) menu.classList.toggle('hidden');
        } else if (!e.target.closest('.dropdown-menu')) {
            document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
        }

        // C) EJECUTAR CAMBIO DE ESTADO
        const statusItem = e.target.closest('.global-status-opt') || e.target.closest('.bulk-status-opt');

        if (statusItem) {
            e.preventDefault();
            const newStatus = statusItem.dataset.status;
            let ids = [];

            if (statusItem.classList.contains('global-status-opt')) {
                if (activeRowIdForStatus) ids.push(activeRowIdForStatus);
            } else {
                ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.closest('tr').dataset.id);
            }

            if (ids.length > 0) {
                // 1. Guardamos el contenido original para restaurarlo después
                const originalContent = statusItem.innerHTML;
                const isBulk = !statusItem.classList.contains('global-status-opt');

                // 2. Feedback visual
                if (isBulk) {
                    statusItem.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ...';
                    statusItem.style.pointerEvents = 'none';
                }

                fetch(`${ADMIN_URL}/proyectos/updateSubmissionStatus`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids, status: newStatus })
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        ids.forEach(id => {
                            const row = tableState.data.find(r => r._id == id);
                            if (row) row._validation_status = newStatus;
                        });

                        // Limpiar selección masiva si aplica
                        if (statusItem.classList.contains('bulk-status-opt')) {
                            document.querySelectorAll('.row-select').forEach(c => c.checked = false);
                            document.getElementById('check-all-rows').checked = false;
                        }

                        renderTableRows(); // Re-renderizar tabla
                    } else {
                        alert('Error: ' + d.message);
                    }
                }).catch(err => {
                    console.error(err);
                    alert('Error de conexión.');
                }).finally(() => {
                    // 3. RESTAURAR EL TEXTO ORIGINAL Y CERRAR MENÚS
                    if (isBulk) {
                        statusItem.innerHTML = originalContent;
                        statusItem.style.pointerEvents = 'auto';
                    }

                    if (globalMenu) globalMenu.classList.remove('visible');
                    document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.add('hidden'));
                });
            }
        }
        // Cerrar menú global si clic fuera
        if (globalMenu && !e.target.closest('#global-status-menu') && !e.target.closest('.status-trigger')) {
            globalMenu.classList.remove('visible');
        }

        // D) ELIMINAR MASIVO
        if (e.target.id === 'btn-bulk-delete') {
            const ids = Array.from(document.querySelectorAll('.row-select:checked')).map(cb => cb.closest('tr').dataset.id);
            if (ids.length > 0 && confirm(`¿ELIMINAR ${ids.length} registros permanentemente?`)) {
                fetch(`${ADMIN_URL}/proyectos/deleteSubmissions`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ ids: ids })
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        tableState.data = tableState.data.filter(r => !ids.includes(r._id.toString()));
                        tableState.filteredData = tableState.filteredData.filter(r => !ids.includes(r._id.toString()));
                        renderTableRows();
                        updatePaginationUI();
                        updateBulkBar();
                    } else { alert('Error: ' + d.message); }
                });
            }
        }
    });

    // --- 8. MODALES ---
    let currentDetailIndex = 0; let detailRows = [];

    document.addEventListener('click', (e) => {
        if (e.target.closest('.btn-view-detail')) {
            const tr = e.target.closest('tr');
            detailRows = Array.from(document.querySelectorAll('#submissions-table tbody tr.data-row')).map(r => r.dataset.id);
            currentDetailIndex = detailRows.indexOf(tr.dataset.id);
            openDetailModal(tr.dataset.id);
        }
        if (e.target.id === 'btn-prev-sub' && currentDetailIndex > 0) openDetailModal(detailRows[--currentDetailIndex]);
        if (e.target.id === 'btn-next-sub' && currentDetailIndex < detailRows.length - 1) openDetailModal(detailRows[++currentDetailIndex]);

        if (e.target.closest('.btn-edit-row')) openEditModal(e.target.closest('tr').dataset.id);
    });

    function openDetailModal(subId) {
        const modal = document.getElementById('modal-submission-detail');
        const metaContainer = document.getElementById('meta-container');
        const detailContainer = document.getElementById('detail-container');
        const counter = document.getElementById('modal-nav-counter');
        const titleId = document.getElementById('modal-detail-subtitle');

        const row = tableState.data.find(r => r._id == subId);
        if (!row) return;

        // 1. Renderizar Metadatos
        titleId.textContent = `ID: #${row._id} | Enviado: ${formatDate(row._submission_time)}`;

        const statusLabel = row._validation_status === 'approved'
            ? '<span style="color:#2dce89; font-weight:600;"><i class="fas fa-check-circle"></i> Aprobado</span>'
            : (row._validation_status === 'rejected'
                ? '<span style="color:#f5365c; font-weight:600;"><i class="fas fa-times-circle"></i> Rechazado</span>'
                : '<span style="color:#fb6340; font-weight:600;"><i class="fas fa-clock"></i> En revisión</span>');

        metaContainer.innerHTML = `
            <div>
                <span class="info-label">Estado Validación</span>
                <div class="info-value no-border">${statusLabel}</div>
            </div>
            <div>
                <span class="info-label">Usuario</span>
                <div class="info-value no-border">${row._submitted_by || 'Anónimo'}</div>
            </div>
            <div>
                <span class="info-label">Fecha Inicio</span>
                <div class="info-value no-border">${formatDate(row.start)}</div>
            </div>
        `;

        // 2. Renderizar Preguntas
        detailContainer.innerHTML = '';
        const questions = window.currentQuestions || [];

        questions.forEach(q => {
            let val = row['q_' + q.id];
            let isEmpty = (val === undefined || val === null || val === '');
            let isPhoto = (q.type === 'photo');

            // Decidir si ocupa ancho completo
            let isFull = isPhoto || (typeof val === 'string' && val.length > 50);

            let contentHtml = '';
            if (isEmpty) {
                contentHtml = '<span style="color:#ccc; font-style:italic;">Sin respuesta</span>';
            } else if (isPhoto && typeof val === 'string' && val.startsWith('uploads/')) {
                const url = window.PUBLIC_URL + '/' + val;
                contentHtml = `<a href="${url}" target="_blank"><img src="${url}" class="evidence-img" alt="Evidencia"></a>`;
            } else if (typeof val === 'string') {
                contentHtml = val.replace('C:\\fakepath\\', '');
            } else {
                contentHtml = val;
            }

            const item = document.createElement('div');
            item.className = `info-item ${isFull ? 'full-width' : ''}`;
            item.innerHTML = `
                <span class="info-label">${q.text}</span>
                <div class="info-value ${isPhoto ? 'no-border' : ''}">${contentHtml}</div>
            `;
            detailContainer.appendChild(item);
        });

        // Actualizar contador
        if (counter) counter.textContent = `Registro ${currentDetailIndex + 1} de ${detailRows.length}`;

        modal.classList.remove('hidden');
    }

    // --- FUNCIÓN MODIFICADA: Permite input de archivo ---
    function openEditModal(subId) {
        const modal = document.getElementById('modal-edit-submission');
        const container = document.getElementById('edit-form-container');
        document.getElementById('edit-sub-id').value = subId;

        const row = tableState.data.find(r => r._id == subId);
        if (!row) return;

        const questions = window.currentQuestions || [];
        container.innerHTML = '';

        questions.forEach(q => {
            const val = row['q_' + q.id] || '';

            let isFullWidth = (q.type === 'photo' || q.type === 'gps' || (typeof val === 'string' && val.length > 50));

            const group = document.createElement('div');
            group.className = `edit-field-group ${isFullWidth ? 'full-width' : ''}`;

            const label = document.createElement('label');
            label.className = 'edit-label';
            label.textContent = q.text;
            group.appendChild(label);

            let inputHtml = '';

            if (q.type === 'text' || q.type === 'gps') {
                inputHtml = `<input type="text" class="edit-input" name="q_${q.id}" value="${htmlspecialchars(val)}">`;
            }
            else if (q.type === 'number') {
                inputHtml = `<input type="number" class="edit-input" name="q_${q.id}" value="${val}">`;
            }
            else if (q.type === 'date') {
                inputHtml = `<input type="date" class="edit-input" name="q_${q.id}" value="${val}">`;
            }
            else if (q.type === 'select') {
                let optionsHtml = '';
                (q.options || []).forEach(opt => {
                    const checked = (val === opt) ? 'checked' : '';
                    optionsHtml += `
                        <label class="edit-radio-option">
                            <input type="radio" name="q_${q.id}" value="${opt}" ${checked}>
                            ${opt}
                        </label>`;
                });
                inputHtml = `<div class="edit-radio-group">${optionsHtml}</div>`;
            }
            else if (q.type === 'photo') {
                // MODIFICADO: Mostrar previsualización y permitir cambio
                let imgPreview = '<span style="font-size:12px; color:#999;">Sin imagen actual</span>';
                if (typeof val === 'string' && val.startsWith('uploads/')) {
                    const url = window.PUBLIC_URL + '/' + val;
                    imgPreview = `<img src="${url}" title="Imagen actual"> <a href="${url}" target="_blank" style="font-size:12px; margin-left:5px;">Ver actual</a>`;
                }

                inputHtml = `
                    <div class="edit-photo-preview" style="justify-content: space-between;">
                        <div style="display:flex; align-items:center; gap:10px;">${imgPreview}</div>
                    </div>
                    <div style="margin-top:8px;">
                        <label style="font-size:11px; color:#666;">Cambiar imagen (opcional):</label>
                        <input type="file" class="edit-input" name="q_${q.id}" accept="image/*">
                    </div>
                `;
            }

            const inputWrapper = document.createElement('div');
            inputWrapper.innerHTML = inputHtml;

            // Lógica para desempaquetar o mantener wrapper
            if (inputWrapper.firstElementChild && (q.type !== 'select' && q.type !== 'photo')) {
                group.appendChild(inputWrapper.firstElementChild);
            } else {
                group.appendChild(inputWrapper);
            }

            container.appendChild(group);
        });

        modal.classList.remove('hidden');
    }

    // --- NUEVA LÓGICA DE GUARDADO ---
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('#btn-save-edit');

        if (btn) {
            e.preventDefault();

            const form = document.getElementById('form-edit-submission');
            if (!form) return;

            const formData = new FormData(form);
            const originalText = btn.innerHTML;

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            fetch(`${ADMIN_URL}/proyectos/updateSubmissionData`, {
                method: 'POST',
                body: formData
            })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        alert('Registro actualizado correctamente.');
                        document.getElementById('modal-edit-submission').classList.add('hidden');

                        // Recargar la tabla para ver los cambios
                        const formProject = document.getElementById('form-update-project');
                        if (formProject) {
                            const projectId = formProject.dataset.id;
                            const container = document.querySelector('.data-content-area');

                            // Feedback visual suave (opacidad) mientras carga
                            if (container) container.style.opacity = '0.6';

                            fetch(`${ADMIN_URL}/proyectos/getDataTableAjax/${projectId}`)
                                .then(res => res.text())
                                .then(html => {
                                    if (container) {
                                        container.innerHTML = html;
                                        container.style.opacity = '1';
                                        const scripts = container.querySelectorAll('script');
                                        scripts.forEach(oldScript => {
                                            const newScript = document.createElement('script');
                                            if (oldScript.src) {
                                                newScript.src = oldScript.src;
                                            } else {
                                                newScript.textContent = oldScript.textContent;
                                            }
                                            document.body.appendChild(newScript);
                                            document.body.removeChild(newScript);
                                        });

                                        initDataTable(); // Ahora sí usará los datos nuevos
                                    }
                                });
                        }
                    } else {
                        alert('Error: ' + d.message);
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión al guardar.');
                })
                .finally(() => {
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
        }
    });

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        if (isNaN(d.getTime())) return dateStr;
        return d.toLocaleDateString('es-ES', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + d.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }

    function htmlspecialchars(str) {
        if (typeof str !== "string") return str;
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
});