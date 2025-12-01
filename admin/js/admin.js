document.addEventListener('DOMContentLoaded', function() {
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
    if(searchInput) {
        searchInput.addEventListener('keyup', function() {
            const term = this.value.toLowerCase();
            document.querySelectorAll('#projects-table tbody tr').forEach(row => {
                if(row.cells.length < 2) return; 
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
            if(searchInput) searchInput.value = '';
            if(tbody) Array.from(tbody.rows).forEach(r => r.style.display = '');
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
            if(e.target.closest('.btn-restore')) return;

            const btn = e.target.closest('.btn-icon-action');
            const content = btn.nextElementSibling;
            if(content) {
                document.querySelectorAll('.dropdown-content').forEach(d => {
                    if (d !== content) d.classList.remove('show');
                });
                content.classList.toggle('show');
            }
        }
    });

    // --- NAVEGACIÓN SIDEBAR ---
    const navActive = document.querySelector('a[data-action="load-active"]');
    if(navActive) {
        navActive.addEventListener('click', (e) => {
            e.preventDefault();
            window.location.href = `${ADMIN_URL}/dashboard`;
        });
    }

    const navArchived = document.querySelector('a[data-action="load-archived"]');
    if(navArchived) {
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
            } catch(err) {
                tbody.innerHTML = '<tr><td colspan="7" style="text-align:center; color:red;">Error al cargar archivados.</td></tr>';
            }
        });
    }

    // --- ACCIONES DE TABLA ---
    if(tbody) {
        tbody.addEventListener('click', (e) => {
            if(e.target.closest('.btn-restore')) {
                e.stopPropagation();
                const btn = e.target.closest('.btn-restore');
                const id = btn.dataset.id;
                if(confirm('¿Restaurar este proyecto a la lista de activos?')) {
                    fetch(`${ADMIN_URL}/proyectos/restaurar/${id}`)
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) navArchived.click();
                        else alert('Error: ' + d.message);
                    });
                }
                return;
            }
            const row = e.target.closest('tr');
            if(!row || e.target.closest('input') || e.target.closest('.dropdown') || e.target.closest('.btn-icon-action')) return;
            const id = row.dataset.id;
            if(id) loadView('detail', id);
        });
    }

    // --- MODALES ---
    const modalOpts = document.getElementById('modal-nuevo-proyecto');
    const modalDetails = document.getElementById('modal-detalles-proyecto');
    const btnNew = document.getElementById('btn-open-create-modal');
    const btnBack = document.getElementById('btn-back-options');
    const btnOptCreate = document.getElementById('opt-crear-cero');
    
    if(btnNew) btnNew.addEventListener('click', (e) => { e.preventDefault(); modalOpts.classList.remove('hidden'); });
    if(btnOptCreate) btnOptCreate.addEventListener('click', () => { modalOpts.classList.add('hidden'); modalDetails.classList.remove('hidden'); });
    if(btnBack) btnBack.addEventListener('click', () => { modalDetails.classList.add('hidden'); modalOpts.classList.remove('hidden'); });

    document.body.addEventListener('click', (e) => {
        if(e.target.classList.contains('close-modal') || e.target.classList.contains('modal-overlay')) {
            document.querySelectorAll('.modal-overlay').forEach(m => m.classList.add('hidden'));
        }
    });

    const formCreate = document.getElementById('form-create-project');
    if(formCreate) {
        formCreate.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-confirm-create');
            const loader = btn.querySelector('.loader');
            btn.disabled = true; loader.classList.remove('hidden');
            const formData = new FormData(formCreate);

            try {
                const res = await fetch(`${ADMIN_URL}/proyectos/crear`, { method: 'POST', body: formData });
                const data = await res.json();
                if(data.success) {
                    modalDetails.classList.add('hidden');
                    formCreate.reset();
                    loadView('builder', data.projectId, data.projectName); 
                } else {
                    alert('Error: ' + data.message);
                }
            } catch(err) { console.error(err); alert('Error de conexión.'); } 
            finally { btn.disabled = false; loader.classList.add('hidden'); }
        });
    }

    // --- ROUTER VISTAS ---
    async function loadView(type, id, extraData = null) {
        projectList.style.display = 'none';
        ajaxSlot.innerHTML = '<div style="display:flex;justify-content:center;padding:50px;"><div class="spinner" style="border:3px solid #eee;border-top:3px solid #6D4C7F;border-radius:50%;width:40px;height:40px;animation:spin 1s infinite"></div></div><style>@keyframes spin{to{transform:rotate(360deg)}}</style>';
        
        let url = '';
        if(type === 'detail') url = `${ADMIN_URL}/proyectos/getProjectDetailAjax/${id}`;
        if(type === 'builder') url = `${ADMIN_URL}/proyectos/getFormConstructorAjax/${id}`;

        try {
            const res = await fetch(url);
            const html = await res.text();
            ajaxSlot.innerHTML = html;
            
            let projectName = extraData;
            if (!projectName) {
                const wrapper = ajaxSlot.querySelector('[data-project-name]');
                if(wrapper) projectName = wrapper.dataset.projectName;
            }
            updateHeader('project', projectName || 'Proyecto');

            if(type === 'builder') initFormBuilderLogic(id);

        } catch(err) {
            ajaxSlot.innerHTML = '<p style="text-align:center; padding:20px;">Error al cargar vista.</p>';
        }
    }

    // --- INTERACCIONES DE VISTAS AJAX ---
    ajaxSlot.addEventListener('click', (e) => {
        if(e.target.closest('#btn-close-builder')) {
            const id = e.target.closest('#btn-close-builder').dataset.id;
            loadView('detail', id);
            return;
        }

        // --- LÓGICA IMPLEMENTAR ---
        if (e.target.id === 'btn-deploy-action') {
            const btn = e.target;
            const id = btn.dataset.id;
            
            if(confirm('¿Estás seguro de implementar este formulario? Pasará a ser público.')) {
                btn.disabled = true;
                btn.innerHTML = 'Implementando...';
                
                fetch(`${ADMIN_URL}/proyectos/implementar/${id}`)
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) {
                            // Recargar la vista detalle para ver el nuevo estado
                            loadView('detail', id); 
                        } else {
                            alert('Error: ' + d.message);
                            btn.disabled = false;
                            btn.innerHTML = 'IMPLEMENTAR';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        btn.disabled = false;
                    });
            }
        }
        // --- LÓGICA COPIAR ENLACE ---
        if (e.target.classList.contains('copy-btn')) {
            const input = document.getElementById('hidden-public-link');
            if(input) {
                navigator.clipboard.writeText(input.value).then(() => {
                    const originalText = e.target.innerText;
                    e.target.innerText = 'Copiado!';
                    setTimeout(() => e.target.innerText = originalText, 2000);
                });
            }
        }


        // --- LÓGICA MODIFICADA PARA ARCHIVAR/DESARCHIVAR DESDE CONFIG ---
        if(e.target.id === 'btn-archive-project') {
            const id = e.target.dataset.id;
            const action = e.target.dataset.action; // 'archive' o 'restore'
            
            const endpoint = action === 'restore' ? 'restaurar' : 'archivar';
            const successMsg = action === 'restore' ? 'Proyecto restaurado' : 'Proyecto archivado';

            fetch(`${ADMIN_URL}/proyectos/${endpoint}/${id}`)
                .then(r => r.json())
                .then(d => {
                    if(d.success) {
                        alert(successMsg);
                        window.location.href = `${ADMIN_URL}/dashboard`;
                    } else {
                        alert('Error: ' + d.message);
                    }
                });
        }
        // ----------------------------------------------------------------

        if(e.target.id === 'btn-delete-project') {
            if(confirm('¿Estás SEGURO de eliminar este proyecto permanentemente?')) {
                const id = e.target.dataset.id;
                fetch(`${ADMIN_URL}/proyectos/eliminar/${id}`)
                    .then(r => r.json())
                    .then(d => {
                        if(d.success) {
                            alert('Proyecto eliminado');
                            window.location.href = `${ADMIN_URL}/dashboard`;
                        }
                    });
            }
        }

        if(e.target.closest('.action-link-builder')) {
            e.preventDefault();
            const id = e.target.closest('.action-link-builder').dataset.id;
            loadView('builder', id);
            return;
        }
        
        if(e.target.closest('.btn-preview-action')) {
            e.preventDefault();
            let questions = [];
            const builderWrapper = document.querySelector('.form-builder-wrapper');
            if(builderWrapper) {
                questions = window.currentFormQuestions || [];
            } else {
                const detailWrapper = document.querySelector('.project-detail-wrapper');
                if(detailWrapper && detailWrapper.dataset.questions) {
                    try { questions = JSON.parse(detailWrapper.dataset.questions); } 
                    catch(e) { questions = []; }
                }
            }
            openPreviewModal(questions);
        }

        if(e.target.classList.contains('tab-link')) {
            const tabId = e.target.dataset.tab;
            const wrapper = e.target.closest('.project-detail-wrapper');
            wrapper.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            wrapper.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            e.target.classList.add('active');
            document.getElementById(`tab-${tabId}`).classList.add('active');
        }
        
        if(e.target.classList.contains('data-nav-item')) {
            const sidebar = e.target.closest('.data-sidebar');
            sidebar.querySelectorAll('.data-nav-item').forEach(i => i.classList.remove('active'));
            e.target.classList.add('active');
        }
    });
    
    document.addEventListener('submit', async (e) => {
        if(e.target && e.target.id === 'form-update-project') {
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
                if(data.success) {
                    alert('Proyecto actualizado');
                    const newName = fd.get('nombre');
                    if(newName) document.getElementById('header-title').textContent = newName;
                } else {
                    alert('Error: ' + data.message);
                }
            } catch(err) { alert('Error de conexión'); }
            finally { btn.disabled = false; btn.textContent = originalText; }
        }
    });

    function openPreviewModal(questions) {
        let modal = document.getElementById('preview-dynamic-modal');
        if(!modal) {
            modal = document.createElement('div');
            modal.id = 'preview-dynamic-modal';
            modal.className = 'modal-overlay';
            modal.innerHTML = `
                <div class="modal-content" style="max-width:500px;">
                    <div class="modal-header"><h2>Vista Previa</h2><button class="close-modal">&times;</button></div>
                    <div class="modal-body" style="max-height:70vh; overflow-y:auto;"><form id="preview-form-mock"></form></div>
                    <div class="modal-footer" style="padding:15px; border-top:1px solid #eee; text-align:right;"><button class="btn-secondary close-modal">Cerrar</button></div>
                </div>`;
            document.body.appendChild(modal);
            modal.addEventListener('click', (e) => {
                if(e.target.classList.contains('close-modal') || e.target.classList.contains('modal-overlay')) modal.classList.add('hidden');
            });
        }
        
        const formContainer = modal.querySelector('#preview-form-mock');
        formContainer.innerHTML = '';
        
        if(!questions || questions.length === 0) {
            formContainer.innerHTML = '<p style="text-align:center; color:#999;">Formulario vacío</p>';
        } else {
            questions.forEach(q => {
                const div = document.createElement('div');
                div.style.marginBottom = '15px';
                const label = document.createElement('label');
                label.style.display = 'block';
                label.style.fontWeight = '500';
                label.style.marginBottom = '5px';
                label.innerHTML = q.text + (q.required === 'yes' ? ' <span style="color:red">*</span>' : '');
                div.appendChild(label);
                let input = document.createElement('input');
                input.className = 'form-control';
                if(q.type === 'number') input.type = 'number';
                else if(q.type === 'date') input.type = 'date';
                else if(q.type === 'photo') input.type = 'file';
                else input.type = 'text';
                div.appendChild(input);
                formContainer.appendChild(div);
            });
        }
        modal.classList.remove('hidden');
    }

    function initFormBuilderLogic(projectId) {
        const wrapper = document.querySelector('.form-builder-wrapper');
        let questions = [];
        try { questions = JSON.parse(wrapper.dataset.questions || '[]'); } catch(e) { questions = []; }
        window.currentFormQuestions = questions;

        const container = document.getElementById('questions-container');
        const emptyState = document.getElementById('empty-state-container');
        
        let draggedItem = null;
        let draggedIndex = null;

        function render() {
            container.innerHTML = '';
            if(questions.length === 0) {
                emptyState.classList.remove('hidden');
            } else {
                emptyState.classList.add('hidden');
                questions.forEach((q, idx) => {
                    const el = document.createElement('div');
                    el.className = 'question-item fade-in';
                    el.setAttribute('draggable', 'true');
                    el.dataset.index = idx;

                    el.innerHTML = `
                        <div class="q-handle" title="Arrastrar"><i class="fas fa-grip-vertical"></i></div>
                        <div class="q-content">
                            <div class="q-header">
                                <span class="q-type">${q.type}</span>
                                <div class="q-options-check">
                                    <label style="cursor:pointer; font-size:12px;">
                                        <input type="checkbox" class="q-req" ${q.required === 'yes' ? 'checked' : ''}> Obligatorio
                                    </label>
                                </div>
                            </div>
                            <input type="text" class="q-text-input" value="${q.text}" placeholder="Escribe tu pregunta">
                        </div>
                        <div class="q-actions">
                            <button type="button" class="btn-q-action q-settings" title="Configuración"><i class="fas fa-cog"></i></button>
                            <button type="button" class="btn-q-action q-dup" title="Duplicar"><i class="fas fa-clone"></i></button>
                            <button type="button" class="btn-q-action q-del delete" title="Eliminar"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;

                    el.querySelector('.q-text-input').addEventListener('input', (ev) => { questions[idx].text = ev.target.value; window.currentFormQuestions = questions; });
                    el.querySelector('.q-req').addEventListener('change', (ev) => { questions[idx].required = ev.target.checked ? 'yes' : 'no'; window.currentFormQuestions = questions; });

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

        container.addEventListener('click', (e) => {
            if (e.target.closest('.q-del')) {
                const idx = parseInt(e.target.closest('.question-item').dataset.index);
                questions.splice(idx, 1); render();
            }
            if (e.target.closest('.q-dup')) {
                const idx = parseInt(e.target.closest('.question-item').dataset.index);
                const copy = JSON.parse(JSON.stringify(questions[idx]));
                copy.id = 'q_' + Date.now();
                copy.text = copy.text + ' (copia)';
                questions.splice(idx + 1, 0, copy);
                render();
            }
        });

        const addBox = document.getElementById('add-question-box');
        const btnAdd = document.getElementById('add-question-btn');
        const inputNew = document.getElementById('new-question-input');
        const btnConfirmAdd = document.getElementById('confirm-add-btn');
        
        if(btnAdd) {
            btnAdd.addEventListener('click', () => { addBox.classList.remove('hidden'); btnAdd.classList.add('hidden'); inputNew.focus(); });
        }

        const typeModal = document.getElementById('question-type-modal');
        if(btnConfirmAdd) {
            btnConfirmAdd.addEventListener('click', () => { if(!inputNew.value.trim()) return; typeModal.classList.remove('hidden'); });
        }
        
        typeModal.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('click', () => {
                questions.push({
                    id: 'q_' + Date.now(),
                    text: inputNew.value,
                    type: card.dataset.type,
                    required: 'no',
                    columnName: 'col_' + Date.now()
                });
                typeModal.classList.add('hidden');
                addBox.classList.add('hidden');
                if(btnAdd) btnAdd.classList.remove('hidden');
                inputNew.value = '';
                render();
            });
        });
        
        typeModal.querySelector('.close-modal').addEventListener('click', () => typeModal.classList.add('hidden'));

        const btnSave = document.getElementById('btn-save-builder');
        if(btnSave) {
            btnSave.addEventListener('click', async function() {
                const btn = this;
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                const fd = new FormData();
                fd.append('form_definition', JSON.stringify(questions));
                
                try {
                    await fetch(`${ADMIN_URL}/formularios/guardar/${projectId}`, { method: 'POST', body: fd });
                    btn.innerHTML = '<i class="fas fa-check"></i> Guardado';
                } catch(e) {
                    btn.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Error';
                }
                setTimeout(() => btn.innerHTML = originalHTML, 2000);
            });
        }
        
        const btnPreview = document.getElementById('btn-preview-builder');
        if(btnPreview) {
            const newBtn = btnPreview.cloneNode(true);
            btnPreview.parentNode.replaceChild(newBtn, btnPreview);
            newBtn.addEventListener('click', () => openPreviewModal(questions));
        }
    }
});